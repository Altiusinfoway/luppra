<?php

namespace App\Http\Controllers\Auth;

use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserLogin;
use App\Http\Controllers\Controller;
use App\Support\Tenancy\TenantAuditLogger;
use App\Support\Tenancy\TenancyManager;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers {
        login as protected traitLogin;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function login(Request $request)
    {
        if (config('tenancy.enabled', false) && $this->shouldRedirectToCentralLogin($request)) {
            return redirect()
                ->to($this->centralLoginUrl())
                ->withErrors([$this->username() => 'Please login from the main domain.']);
        }

        return $this->traitLogin($request);
    }

    protected function credentials(Request $request)
    {
        $this->resetTenantLoginContext($request);

        $credentials = $request->only($this->username(), 'password');
        if (!config('tenancy.enabled', false)) {
            return $credentials;
        }

        $email = (string) $request->input($this->username());
        if ($this->isSuperAdminLogin($email)) {
            return $credentials;
        }

        if ($this->isCentralHost((string) $request->getHost())) {
            return $credentials;
        }

        $tenantId = $this->resolveTenantIdForLogin($request);
        if (
            $tenantId === null
            && $this->isCentralHost((string) $request->getHost())
            && $this->resolveTenantIdFromLandlordContext($email) !== null
        ) {
            return $credentials;
        }

        if ($tenantId === null) {
            $tenantIds = $this->resolveTenantIdsForEmailAcrossActiveTenants($email);
            if (count($tenantIds) === 1) {
                $tenantId = $tenantIds[0];
            } else {
                $tenantId = $this->resolveTenantIdFromLandlordContext($email);
            }
        }

        if ($tenantId !== null) {
            $tenant = Tenant::query()->where('id', $tenantId)->where('is_active', true)->first();
            if ($tenant) {
                $tenancy = app(TenancyManager::class);
                $tenancy->initialize($tenant);
                app()->instance('currentTenant', $tenant);
                $this->configurePermissionScope((int) $tenant->id);

                if (User::query()->where('email', $email)->exists()) {
                    $request->session()->put('login_tenant_id', (int) $tenant->id);
                    $credentials['tenant_id'] = (int) $tenant->id;
                    return $credentials;
                }

                $tenancy->end();
                app()->forgetInstance('currentTenant');
                $this->configurePermissionScope(null);
            }
        }

        return $credentials;
    }

    protected function attemptLogin(Request $request): bool
    {
        if (!config('tenancy.enabled', false)) {
            return $this->guard()->attempt($this->credentials($request), $request->boolean('remember'));
        }

        $email = (string) $request->input($this->username());
        $plainPassword = (string) $request->input('password');
        if ($email === '' || $plainPassword === '') {
            return false;
        }

        $credentials = $this->credentials($request);
        if ($this->isSuperAdminLogin($email)) {
            return $this->guard()->attempt($credentials, $request->boolean('remember'));
        }

        $tenantHint = $this->resolveTenantIdForLogin($request);
        if ($this->isCentralHost((string) $request->getHost())) {
            $centralLoginAttempt = $this->attemptLandlordBrokeredTenantLogin($request, $email, $plainPassword);
            return $centralLoginAttempt ?? false;
        }

        $tenantIds = $tenantHint !== null
            ? [(int) $tenantHint]
            : $this->resolveTenantIdsForEmailAcrossActiveTenants($email);

        if ($tenantHint === null && count($tenantIds) === 0) {
            $fallbackTenantId = $this->resolveTenantIdFromLandlordContext($email);
            if ($fallbackTenantId !== null) {
                $tenantIds = [$fallbackTenantId];
            }
        }

        if (empty($tenantIds)) {
            return false;
        }

        $tenancy = app(TenancyManager::class);

        foreach ($tenantIds as $tenantId) {
            $tenantId = (int) $tenantId;
            $tenant = Tenant::query()->where('id', $tenantId)->where('is_active', true)->first();
            if (!$tenant) {
                continue;
            }

            try {
                $tenancy->initialize($tenant);
                app()->instance('currentTenant', $tenant);
                $this->configurePermissionScope((int) $tenant->id);

                $user = User::query()->where('email', $email)->first();
                if (!$user || empty($user->password)) {
                    continue;
                }

                if (!Hash::check($plainPassword, (string) $user->password)) {
                    continue;
                }

                $this->guard()->login($user, $request->boolean('remember'));
                $request->session()->put('tenant_user_id', (int) $user->id);
                $request->session()->put('login_tenant_id', (int) $tenant->id);
                $request->session()->put('tenant_id', (int) $tenant->id);

                return true;
            } catch (\Throwable $e) {
                // Skip broken/unreachable tenant and continue.
            } finally {
                if (!$this->guard()->check()) {
                    $tenancy->end();
                    app()->forgetInstance('currentTenant');
                    $this->configurePermissionScope(null);
                }
            }
        }

        return false;
    }

    private function attemptLandlordBrokeredTenantLogin(Request $request, string $email, string $plainPassword): ?bool
    {
        $landlordUser = User::query()
            ->where('email', $email)
            ->where('type', '!=', 'super admin')
            ->first();

        if (!$landlordUser) {
            return null;
        }

        if (empty($landlordUser->password) || !Hash::check($plainPassword, (string) $landlordUser->password)) {
            return false;
        }

        $tenantId = $this->resolveTenantIdFromLandlordContext($email);
        if ($tenantId === null) {
            $request->session()->put('tenant_login_error', 'Tenant is not assigned to this user.');
            return false;
        }

        $tenant = Tenant::query()->where('id', $tenantId)->where('is_active', true)->first();
        if (!$tenant) {
            $request->session()->put('tenant_login_error', 'Assigned tenant is inactive or unavailable.');
            return false;
        }

        $tenancy = app(TenancyManager::class);

        try {
            $tenancy->initialize($tenant);
            app()->instance('currentTenant', $tenant);
            $this->configurePermissionScope((int) $tenant->id);

            $tenantUser = User::query()
                ->where(function ($query) use ($landlordUser, $email) {
                    $query->where('id', (int) $landlordUser->id)
                        ->orWhere('email', $email);
                })
                ->where('tenant_id', (int) $tenant->id)
                ->first();

            if (!$tenantUser) {
                $request->session()->put('tenant_login_error', 'Tenant user account is missing.');
                return false;
            }

            if ((string) $tenantUser->password !== (string) $landlordUser->password) {
                $tenantUser->password = $landlordUser->password;
                $tenantUser->save();
            }

            $this->guard()->login($tenantUser, $request->boolean('remember'));
            $request->session()->put('tenant_user_id', (int) $tenantUser->id);
            $request->session()->put('login_tenant_id', (int) $tenant->id);
            $request->session()->put('tenant_id', (int) $tenant->id);

            return true;
        } catch (\Throwable $e) {
            $request->session()->put('tenant_login_error', 'Tenant login could not be completed.');
            return false;
        } finally {
            if (!$this->guard()->check()) {
                $tenancy->end();
                app()->forgetInstance('currentTenant');
                $this->configurePermissionScope(null);
            }
        }
    }

    private function resolveTenantIdsForEmailAcrossActiveTenants(string $email): array
    {
        if ($email === '') {
            return [];
        }

        $tenantIds = [];
        $tenancy = app(TenancyManager::class);
        $tenants = Tenant::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id']);

        foreach ($tenants as $tenant) {
            try {
                $tenancy->initialize($tenant);
                app()->instance('currentTenant', $tenant);

                if (User::query()->where('email', $email)->exists()) {
                    $tenantIds[] = (int) $tenant->id;
                }
            } catch (\Throwable $e) {
                // Skip unreachable/broken tenant DB and continue searching.
            } finally {
                $tenancy->end();
                app()->forgetInstance('currentTenant');
            }
        }

        return array_values(array_unique($tenantIds));
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $tenantLoginError = (string) $request->session()->pull('tenant_login_error', '');
        $this->incrementLoginAttempts($request);

        return redirect()->back()->withInput($request->only($this->username(), 'remember'))->withErrors([
            $this->username() => $tenantLoginError !== '' ? $tenantLoginError : trans('auth.failed'),
        ]);
    }

    private function resolveTenantIdFromLandlordContext(string $email): ?int
    {
        if ($email === '') {
            return null;
        }

        $landlordUser = User::query()
            ->where('email', $email)
            ->select('tenant_id', 'created_by')
            ->first();

        if (!$landlordUser) {
            return null;
        }

        $tenantId = (int) ($landlordUser->tenant_id ?? 0);
        if ($tenantId > 0 && Tenant::query()->where('id', $tenantId)->where('is_active', true)->exists()) {
            return $tenantId;
        }

        if ((int) ($landlordUser->created_by ?? 0) > 0) {
            $creatorTenantId = (int) (User::query()
                ->where('id', (int) $landlordUser->created_by)
                ->value('tenant_id') ?? 0);

            if ($creatorTenantId > 0 && Tenant::query()->where('id', $creatorTenantId)->where('is_active', true)->exists()) {
                return $creatorTenantId;
            }
        }

        return null;
    }

    protected function authenticated(Request $request, $user)
    {
        if (!config('tenancy.enabled', false)) {
            $this->clearTenantSessionState($request);
            $this->configurePermissionScope(null);
            $this->recordSingleCompanyUserLogin($request, $user);
            return;
        }

        if ($user->type === 'super admin') {
            $this->clearTenantSessionState($request);
            $this->configurePermissionScope(null);
            return;
        }

        $tenantIdFromContext = $this->resolveTenantIdForLogin($request);

        $userTenantId = (int) ($user->tenant_id ?? 0);
        if ($userTenantId <= 0) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $this->configurePermissionScope(null);
            return redirect()->route('login')->with('error', 'Tenant is not assigned to this user.');
        }

        if ($tenantIdFromContext !== null && $tenantIdFromContext !== $userTenantId) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $this->configurePermissionScope(null);

            return redirect()->route('login')->with('error', 'This login URL does not belong to your tenant.');
        }

        $request->session()->put('tenant_id', $userTenantId);
        $request->session()->put('login_tenant_id', $userTenantId);
        $request->session()->put('tenant_user_id', (int) $user->id);

        $subscriptionExpiredMessage = $this->subscriptionExpiredMessage($userTenantId);
        if ($subscriptionExpiredMessage !== null) {
            $this->configurePermissionScope($userTenantId);
            return redirect()
                ->to(route('subscription.plans') . '#pricing')
                ->with('error', $subscriptionExpiredMessage);
        }

        TenantAuditLogger::log(
            event: 'tenant_login_success',
            tenantId: $userTenantId,
            userId: (int) $user->id,
            message: 'Tenant user logged in.',
            meta: ['host' => (string) $request->getHost()]
        );
        $this->recordTenantUserLogin($request, $user);

        if ($this->isCentralHost((string) $request->getHost()) && $tenantIdFromContext === null) {
            $this->configurePermissionScope($userTenantId);
            return redirect()->to(route('dashboard'));
        }

        $this->configurePermissionScope($userTenantId);
    }

    private function subscriptionExpiredMessage(int $tenantId): ?string
    {
        if (!config('tenancy.enabled', false) || !config('tenancy.enforce_subscription', false)) {
            return null;
        }

        $subscription = Subscription::query()
            ->where('tenant_id', $tenantId)
            ->latest('id')
            ->first();

        if (!$subscription) {
            return (bool) config('tenancy.allow_without_subscription', true)
                ? null
                : 'No active subscription is configured for this tenant. Please choose a plan to continue.';
        }

        $status = (string) $subscription->status;
        $endsAt = $subscription->ends_at;
        $isExpiredByDate = $endsAt && $endsAt->toDateString() < now()->toDateString();

        if ($status === 'trialing' && $isExpiredByDate) {
            return 'Your free trial has expired. Please choose a plan to continue.';
        }

        if ($status === 'active' && $isExpiredByDate) {
            return 'Your plan has expired. Please choose a plan to continue.';
        }

        if (in_array($status, ['expired', 'canceled'], true)) {
            return $status === 'expired'
                ? 'Your plan has expired. Please choose a plan to continue.'
                : 'Your subscription has been canceled. Please choose a plan to continue.';
        }

        return null;
    }

    public function showLoginForm()
    {
        if (!config('tenancy.enabled', false)) {
            $this->clearTenantSessionState(request());
            return view('auth.login');
        }

        if ($this->shouldRedirectToCentralLogin(request())) {
            return redirect()->to($this->centralLoginUrl());
        }

        $tenantId = $this->resolveTenantIdForLogin(request());
        if ($tenantId !== null) {
            request()->session()->put('login_tenant_id', $tenantId);
        } else {
            $this->clearTenantSessionState(request());
        }

        return view('auth.login');
    }

    private function resolveTenantIdForLogin(Request $request): ?int
    {
        if (!config('tenancy.enabled', false)) {
            return null;
        }

        $tenantIdHeader = (string) config('tenancy.header_tenant_id', 'X-Tenant-Id');
        $tenantSlugHeader = (string) config('tenancy.header_tenant_slug', 'X-Tenant-Slug');

        $tenantId = $request->header($tenantIdHeader) ?? $request->query('tenant_id');
        if (!empty($tenantId)) {
            return (int) $tenantId;
        }

        $tenantSlug = $request->header($tenantSlugHeader) ?? $request->query('tenant');
        if (!empty($tenantSlug)) {
            return (int) (Tenant::query()->where('slug', $tenantSlug)->value('id') ?? 0) ?: null;
        }

        $sessionTenantId = (int) $request->session()->get('login_tenant_id', 0);
        if ($sessionTenantId > 0) {
            return $sessionTenantId;
        }

        $host = strtolower((string) $request->getHost());
        if (!empty($host) && !$this->isCentralHost($host)) {
            return (int) (TenantDomain::query()->where('domain', $host)->value('tenant_id') ?? 0) ?: null;
        }

        return null;
    }

    private function isCentralHost(string $host): bool
    {
        $host = strtolower(trim($host));
        $centralHosts = (array) config('tenancy.central_hosts', ['localhost', '127.0.0.1']);
        $appHost = strtolower((string) parse_url((string) config('app.url', ''), PHP_URL_HOST));
        if ($appHost !== '') {
            $centralHosts[] = $appHost;
        }

        $centralHosts = array_values(array_unique(array_filter(array_map('strtolower', array_map('trim', $centralHosts)))));

        return in_array($host, $centralHosts, true);
    }

    private function centralLoginUrl(): string
    {
        $baseUrl = rtrim((string) config('app.url', url('/')), '/');
        return $baseUrl.route('login', [], false);
    }

    private function shouldRedirectToCentralLogin(Request $request): bool
    {
        $currentHost = strtolower(trim((string) $request->getHost()));
        if ($this->isCentralHost($currentHost)) {
            return false;
        }

        $targetHost = strtolower((string) parse_url($this->centralLoginUrl(), PHP_URL_HOST));
        if ($targetHost === '' || $targetHost === $currentHost) {
            return false;
        }

        return true;
    }

    public function logout(Request $request)
    {
        $user = Auth::guard('web')->user();
        if ($user) {
            $this->recordTenantUserLogout($user);
        }

        $this->clearTenantSessionState($request);
        $this->configurePermissionScope(null);
        app(TenancyManager::class)->end();
        app()->forgetInstance('currentTenant');

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function clearTenantSessionState(Request $request): void
    {
        $request->session()->forget('tenant_id');
        $request->session()->forget('tenant_user_id');
        $request->session()->forget('login_tenant_id');
    }

    private function resetTenantLoginContext(Request $request): void
    {
        $this->clearTenantSessionState($request);
        $request->session()->forget('tenant_login_error');
        app(TenancyManager::class)->end();
        app()->forgetInstance('currentTenant');
        $this->configurePermissionScope(null);
    }

    private function isSuperAdminLogin(string $email): bool
    {
        if ($email === '') {
            return false;
        }

        return User::query()
            ->where('email', $email)
            ->where('type', 'super admin')
            ->exists();
    }

    private function configurePermissionScope(?int $tenantId): void
    {
        $cacheKey = $tenantId > 0
            ? "spatie.permission.cache.tenant.{$tenantId}"
            : 'spatie.permission.cache.landlord';

        app('config')->set('permission.cache.key', $cacheKey);

        $registrar = app(PermissionRegistrar::class);
        if (method_exists($registrar, 'forgetCachedPermissions')) {
            $registrar->forgetCachedPermissions();
        }
    }

    private function recordTenantUserLogin(Request $request, User $user): void
    {
        if (!app()->bound('currentTenant')) {
            return;
        }

        try {
            if (!Schema::hasTable('user_logins')) {
                return;
            }

            UserLogin::create([
                'user_id' => (int) $user->id,
                'login_date_time' => now(),
                'is_web_app_login' => 1,
                'browser_detail' => (string) ($request->userAgent() ?? ''),
                'ip_number' => (string) ($request->ip() ?? ''),
            ]);
        } catch (\Throwable $e) {
            // Ignore login-history issues so auth flow is not blocked.
        }
    }

    private function recordSingleCompanyUserLogin(Request $request, User $user): void
    {
        try {
            if (!Schema::hasTable('user_logins')) {
                return;
            }

            UserLogin::create([
                'user_id' => (int) $user->id,
                'login_date_time' => now(),
                'is_web_app_login' => 1,
                'browser_detail' => (string) ($request->userAgent() ?? ''),
                'ip_number' => (string) ($request->ip() ?? ''),
            ]);
        } catch (\Throwable $e) {
            // Ignore login-history issues so auth flow is not blocked.
        }
    }

    private function recordTenantUserLogout(User $user): void
    {
        if (!app()->bound('currentTenant')) {
            return;
        }

        try {
            if (!Schema::hasTable('user_logins')) {
                return;
            }

            $lastLogin = UserLogin::query()
                ->where('user_id', (int) $user->id)
                ->whereNull('logout_date_time')
                ->latest('id')
                ->first();

            if ($lastLogin) {
                $lastLogin->update([
                    'logout_date_time' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            // Ignore login-history issues so logout flow is not blocked.
        }
    }
}
