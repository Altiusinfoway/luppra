<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantModuleAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('tenancy.enabled', false) || !config('tenancy.enforce_plan_modules', false)) {
            return $next($request);
        }

        if ($this->shouldSkip($request) || !app()->bound('currentTenant')) {
            return $next($request);
        }

        $requiredModule = $this->resolveRequiredModule($request);
        if ($requiredModule === null) {
            return $next($request);
        }

        $tenant = app('currentTenant');
        $subscription = Subscription::query()
            ->with('plan')
            ->where('tenant_id', $tenant->id)
            ->latest('id')
            ->first();

        if (!$subscription || !$subscription->plan) {
            return $next($request);
        }

        $modules = collect($subscription->plan->modules ?? [])
            ->map(fn ($m) => strtolower(trim((string) $m)))
            ->filter()
            ->values();

        if ($modules->isEmpty() && (bool) config('tenancy.allow_all_when_plan_modules_empty', true)) {
            return $next($request);
        }

        if ($modules->contains('*') || $modules->contains(strtolower($requiredModule))) {
            return $next($request);
        }

        return $this->deny($request, "Your current plan does not include '{$requiredModule}' module.");
    }

    private function shouldSkip(Request $request): bool
    {
        foreach ((array) config('tenancy.module_exempt_paths', []) as $pattern) {
            if ($pattern !== '' && $request->is($pattern)) {
                return true;
            }
        }

        $routeName = $request->route()?->getName();
        if ($routeName) {
            foreach ((array) config('tenancy.module_exempt_route_names', []) as $pattern) {
                if ($pattern !== '' && Str::is($pattern, $routeName)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function resolveRequiredModule(Request $request): ?string
    {
        $routeName = $request->route()?->getName();
        $map = (array) config('tenancy.module_route_map', []);

        foreach ($map as $module => $rules) {
            foreach ((array) ($rules['route_names'] ?? []) as $pattern) {
                if ($routeName && Str::is($pattern, $routeName)) {
                    return (string) $module;
                }
            }

            foreach ((array) ($rules['paths'] ?? []) as $pathPattern) {
                if ($request->is($pathPattern)) {
                    return (string) $module;
                }
            }
        }

        return null;
    }

    private function deny(Request $request, string $message): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $message,
                'code' => 'plan_module_forbidden',
            ], 403);
        }

        return redirect()->route('dashboard')->with('error', $message);
    }
}
