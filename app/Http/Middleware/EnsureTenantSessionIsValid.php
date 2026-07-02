<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantAuditLogger;
use App\Support\Tenancy\TenancyManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantSessionIsValid
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('tenancy.enabled', false) || !config('tenancy.enforce_tenant_session_isolation', true)) {
            return $next($request);
        }

        if ($this->shouldBypassForApi($request)) {
            return $next($request);
        }

        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        if (!$user) {
            return $next($request);
        }

        if ($user->type === 'super admin') {
            $this->clearTenantSessionState($request);
            app(TenancyManager::class)->end();
            app()->forgetInstance('currentTenant');
            return $next($request);
        }

        $requestedTenantId = $this->resolveTenantIdForSession($request);
        $requestTenantUserId = (int) $request->session()->get('tenant_user_id', 0);
        $sessionEmail = (string) $request->input('email', '');
        if ($sessionEmail === '' && isset($user->email)) {
            $sessionEmail = (string) $user->email;
        }

        if ($requestedTenantId > 0) {
            $rehydrated = $this->rehydrateTenantUserFromSession(
                $request,
                $requestedTenantId,
                $requestTenantUserId,
                $sessionEmail
            );

            if ($rehydrated instanceof User) {
                $user = $rehydrated;
            }
        }

        $userTenantId = (int) ($user->tenant_id ?? 0);
        if ($userTenantId <= 0) {
            return $this->deny($request, 'Tenant is not assigned to this user.');
        }

        $sessionTenantId = (int) $request->session()->get('tenant_id', 0);
        if ($sessionTenantId > 0 && $sessionTenantId !== $userTenantId) {
            $rehydrated = $this->rehydrateTenantUserFromSession(
                $request,
                $sessionTenantId,
                (int) $request->session()->get('tenant_user_id', 0),
                $sessionEmail
            );
            if ($rehydrated instanceof User) {
                $user = $rehydrated;
                $userTenantId = (int) ($user->tenant_id ?? 0);
            }

            if ($sessionTenantId !== $userTenantId) {
                return $this->deny($request, 'Tenant session mismatch detected.');
            }
        }

        if ($sessionTenantId <= 0) {
            $request->session()->put('tenant_id', $userTenantId);
        }

        $currentTenantId = app()->bound('currentTenant') ? (int) data_get(app('currentTenant'), 'id', 0) : 0;
        if ($currentTenantId > 0 && $currentTenantId !== $userTenantId) {
            return $this->deny($request, 'Cross-tenant access detected.');
        }

        if ($currentTenantId === 0) {
            $tenant = Tenant::query()->where('id', $userTenantId)->where('is_active', true)->first();
            if (!$tenant) {
                return $this->deny($request, 'Assigned tenant is not active.');
            }

            app(TenancyManager::class)->initialize($tenant);
            app()->instance('currentTenant', $tenant);
            $this->configureTenantPermissionScope($tenant->id);
        } else {
            $this->configureTenantPermissionScope($currentTenantId);
        }

        return $next($request);
    }

    private function resolveTenantIdForSession(Request $request): int
    {
        $tenantId = (int) $request->session()->get('tenant_id', 0);
        if ($tenantId > 0) {
            return $tenantId;
        }

        return (int) $request->session()->get('login_tenant_id', 0);
    }

    private function rehydrateTenantUserFromSession(Request $request, int $tenantId, int $sessionTenantUserId, string $email): ?User
    {
        if ($tenantId <= 0) {
            return null;
        }

        $tenant = Tenant::query()->where('id', $tenantId)->where('is_active', true)->first();
        if (!$tenant) {
            return null;
        }

        try {
            app(TenancyManager::class)->initialize($tenant);
            app()->instance('currentTenant', $tenant);

            $tenantUser = User::query()->where('id', $sessionTenantUserId)->first();
            if (!$tenantUser) {
                if ($email !== '') {
                    $tenantUser = User::query()->where('email', $email)->first();
                }

                if (!$tenantUser) {
                    return null;
                }
            }

            if ((int) $tenantUser->tenant_id !== $tenantId) {
                return null;
            }

            $request->session()->put('tenant_id', $tenantId);
            $request->session()->put('login_tenant_id', $tenantId);
            $request->session()->put('tenant_user_id', (int) $tenantUser->id);
            $this->configureTenantPermissionScope($tenantId);
            Auth::guard('web')->setUser($tenantUser);
            return $tenantUser;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function clearTenantSessionState(Request $request): void
    {
        $request->session()->forget('tenant_id');
        $request->session()->forget('tenant_user_id');
        $request->session()->forget('login_tenant_id');
        $this->configureTenantPermissionScope(null);
    }

    private function configureTenantPermissionScope(?int $tenantId): void
    {
        $cacheKey = $tenantId > 0
            ? "spatie.permission.cache.tenant.{$tenantId}"
            : 'spatie.permission.cache.landlord';

        app('config')->set('permission.cache.key', $cacheKey);
    }

    private function deny(Request $request, string $message): Response
    {
        TenantAuditLogger::log(
            event: 'tenant_access_blocked',
            tenantId: app()->bound('currentTenant') ? (int) data_get(app('currentTenant'), 'id') : null,
            userId: auth()->id(),
            message: $message,
            meta: [
                'path' => $request->path(),
                'session_tenant_id' => (int) $request->session()->get('tenant_id', 0),
                'user_tenant_id' => (int) (auth()->user()->tenant_id ?? 0),
            ]
        );

        Auth::guard('web')->logout();

        if (method_exists($request, 'session')) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $message,
                'code' => 'tenant_session_invalid',
            ], 401);
        }

        return response()->view('errors.tenant-access', [
            'title' => 'Tenant Access Blocked',
            'message' => $message,
        ], 403);
    }

    private function shouldBypassForApi(Request $request): bool
    {
        if (!$request->is('api/*')) {
            return false;
        }

        if (!method_exists($request, 'hasSession')) {
            return true;
        }

        return !$request->hasSession();
    }
}
