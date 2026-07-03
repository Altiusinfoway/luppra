<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\User;
use App\Support\Tenancy\TenancyManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancy
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('tenancy.enabled', false) || $this->shouldSkipTenancy($request)) {
            return $next($request);
        }

        $tenant = $this->resolveTenant($request);

        if ($tenant && $tenant->is_active) {
            app(TenancyManager::class)->initialize($tenant);
            app()->instance('currentTenant', $tenant);
            $this->configureTenantPermissionScope((int) $tenant->id);
        }
        else {
            $this->configureTenantPermissionScope(null);
        }

        return $next($request);
    }

    private function configureTenantPermissionScope(?int $tenantId): void
    {
        $cacheKey = $tenantId > 0
            ? "spatie.permission.cache.tenant.{$tenantId}"
            : 'spatie.permission.cache.landlord';

        app('config')->set('permission.cache.key', $cacheKey);
    }

    private function shouldSkipTenancy(Request $request): bool
    {
        foreach ((array) config('tenancy.exempt_paths', []) as $pattern) {
            if ($pattern !== '' && $request->is($pattern)) {
                return true;
            }
        }

        $routeName = $request->route()?->getName();
        if ($routeName) {
            foreach ((array) config('tenancy.exempt_route_names', []) as $pattern) {
                if ($pattern !== '' && Str::is($pattern, $routeName)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function resolveTenant(Request $request): ?Tenant
    {
        if ($this->shouldUseSessionTenantResolution($request)) {
            $sessionAuthUserId = (int) $request->session()->get(Auth::guard('web')->getName(), 0);
            if ($sessionAuthUserId > 0) {
                $landlordSessionUser = User::query()
                    ->select('id', 'type')
                    ->find($sessionAuthUserId);

                if ($landlordSessionUser && $landlordSessionUser->type === 'super admin') {
                    $impersonateTenantId = (int) $request->session()->get('impersonate_tenant_id', 0);
                    if ($impersonateTenantId > 0) {
                        $tenant = Tenant::query()->where('id', $impersonateTenantId)->where('is_active', true)->first();
                        if ($tenant) {
                            return $tenant;
                        }
                    }

                    return null;
                }
            }

            $sessionTenantId = (int) $request->session()->get('tenant_id', 0);
            if ($sessionTenantId > 0) {
                $tenant = Tenant::query()->where('id', $sessionTenantId)->where('is_active', true)->first();
                if ($tenant) {
                    return $tenant;
                }
            }

            $loginTenantId = (int) $request->session()->get('login_tenant_id', 0);
            if ($loginTenantId > 0) {
                $tenant = Tenant::query()->where('id', $loginTenantId)->where('is_active', true)->first();
                if ($tenant) {
                    return $tenant;
                }
            }
        }

        $user = $request->user();
        if ($user && $user->type === 'super admin') {
            $impersonateTenantId = (int) $request->session()->get('impersonate_tenant_id', 0);
            if ($impersonateTenantId > 0) {
                $tenant = Tenant::query()->where('id', $impersonateTenantId)->where('is_active', true)->first();
                if ($tenant) {
                    return $tenant;
                }
            }
        }

        $tenantIdHeader = (string) config('tenancy.header_tenant_id', 'X-Tenant-Id');
        $tenantSlugHeader = (string) config('tenancy.header_tenant_slug', 'X-Tenant-Slug');

        $tenantId = $request->header($tenantIdHeader) ?? $request->query('tenant_id');
        if (!empty($tenantId)) {
            return Tenant::query()->where('id', $tenantId)->first();
        }

        $tenantSlug = $request->header($tenantSlugHeader) ?? $request->query('tenant');
        if (!empty($tenantSlug)) {
            return Tenant::query()->where('slug', $tenantSlug)->first();
        }

        $host = strtolower((string) $request->getHost());
        if (!empty($host) && !in_array($host, ['localhost', '127.0.0.1'], true)) {
            $domain = TenantDomain::query()->where('domain', $host)->first();
            if ($domain) {
                return Tenant::query()->find($domain->tenant_id);
            }
        }

        return null;
    }

    private function shouldUseSessionTenantResolution(Request $request): bool
    {
        if ($request->is('api/*')) {
            return false;
        }

        if (!method_exists($request, 'hasSession') || !$request->hasSession()) {
            return false;
        }

        return true;
    }
}
