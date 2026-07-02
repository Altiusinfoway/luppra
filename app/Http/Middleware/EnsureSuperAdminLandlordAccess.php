<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantAuditLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdminLandlordAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->isLandlordOnlyRoute($request)) {
            return $next($request);
        }

        $user = $request->user();
        if ($user && $user->type === 'super admin') {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Only super admin can access this section.',
                'code' => 'super_admin_only',
            ], 403);
        }

        TenantAuditLogger::log(
            event: 'landlord_route_blocked',
            tenantId: app()->bound('currentTenant') ? (int) data_get(app('currentTenant'), 'id') : null,
            userId: $request->user()?->id,
            message: 'Only super admin can access this section.',
            meta: [
                'path' => $request->path(),
                'route_name' => $request->route()?->getName(),
            ]
        );

        return response()->view('errors.tenant-access', [
            'title' => 'Access Restricted',
            'message' => 'Only super admin can access this section.',
        ], 403);
    }

    private function isLandlordOnlyRoute(Request $request): bool
    {
        foreach ((array) config('tenancy.landlord_only_paths', []) as $pattern) {
            if ($pattern !== '' && $request->is($pattern)) {
                return true;
            }
        }

        $routeName = $request->route()?->getName();
        if ($routeName) {
            foreach ((array) config('tenancy.landlord_only_route_names', []) as $pattern) {
                if ($pattern !== '' && Str::is($pattern, $routeName)) {
                    return true;
                }
            }
        }

        return false;
    }
}
