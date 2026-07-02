<?php

namespace App\Http\Middleware;

use App\Services\TenantDefaultInitializationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanySettingsCompleted
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
        if (!$user || $user->type !== 'company') {
            return $next($request);
        }

        if ($request->routeIs('settings.edit', 'settings.update', 'get.states', 'get.cities', 'logout', 'subscription.plans', 'website.*')) {
            return $next($request);
        }

        if ($this->tenantDefaultInitializationService->hasCompletedCompanySettings($user)) {
            return $next($request);
        }

        $redirectUrl = route('settings.edit', $user->id);
        $message = 'Please complete your company settings before using the CRM.';

        if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
            return response()->json([
                'message' => $message,
                'redirect_url' => $redirectUrl,
                'code' => 'company_settings_required',
            ], 409);
        }

        return redirect()
            ->to($redirectUrl)
            ->with('status', $message);
    }
}
