<?php

namespace App\Http\Middleware;

use App\Services\TenantDefaultInitializationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantDefaultsInitialized
{
    public function __construct(private TenantDefaultInitializationService $tenantDefaultInitializationService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        if (!$user || $user->type !== 'company' || !app()->bound('currentTenant')) {
            return $next($request);
        }

        if ($request->routeIs('logout')) {
            return $next($request);
        }

        try {
            $this->tenantDefaultInitializationService->initializeTenantDefaults($user);
        } catch (\Throwable $e) {
            Log::error('Tenant default initialization failed.', [
                'tenant_id' => (int) data_get(app('currentTenant'), 'id', 0),
                'user_id' => (int) $user->id,
                'message' => $e->getMessage(),
            ]);
        }

        return $next($request);
    }
}
