<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantSubscriptionIsValid
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('tenancy.enabled', false) || !config('tenancy.enforce_subscription', false)) {
            return $next($request);
        }

        if ($this->shouldSkip($request) || !app()->bound('currentTenant')) {
            return $next($request);
        }

        $tenant = app('currentTenant');
        $subscription = Subscription::query()
            ->where('tenant_id', $tenant->id)
            ->latest('id')
            ->first();

        if (!$subscription) {
            if ((bool) config('tenancy.allow_without_subscription', true)) {
                return $next($request);
            }

            return $this->deny($request, 'No active subscription is configured for this tenant.');
        }

        $today = Carbon::today()->toDateString();
        $isStatusValid = in_array($subscription->status, ['active', 'trial', 'trialing'], true);
        $isDateValid = empty($subscription->ends_at) || $subscription->ends_at->toDateString() >= $today;

        if ($isStatusValid && $isDateValid) {
            return $next($request);
        }

        return $this->deny($request, 'Subscription is expired or inactive. Please choose a plan to continue.');
    }

    private function deny(Request $request, string $message): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $message,
                'code' => 'subscription_inactive',
            ], 402);
        }

        return redirect()->to(route('subscription.plans') . '#pricing')->with('error', $message);
    }

    private function shouldSkip(Request $request): bool
    {
        foreach ((array) config('tenancy.subscription_exempt_paths', []) as $pattern) {
            if ($pattern !== '' && $request->is($pattern)) {
                return true;
            }
        }

        $routeName = $request->route()?->getName();
        if ($routeName) {
            if (Str::is('website.*', $routeName) || $routeName === 'subscription.plans') {
                return true;
            }

            foreach ((array) config('tenancy.subscription_exempt_route_names', []) as $pattern) {
                if ($pattern !== '' && Str::is($pattern, $routeName)) {
                    return true;
                }
            }
        }

        return false;
    }
}
