<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\Utility;
use App\Support\Tenancy\TenancyManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\PermissionRegistrar;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $token = null;
        $tenantInitialized = false;

        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

            $credentials = $request->only('email', 'password');
            $tenant = $this->resolveTenantForApiLogin($request, (string) $credentials['email']);

            if (config('tenancy.enabled', false)) {
                if (!$tenant) {
                    return Utility::return_response(false, "Tenant account not found for this email.", "", 422);
                }

                app(TenancyManager::class)->initialize($tenant);
                app()->instance('currentTenant', $tenant);
                $this->configurePermissionScope((int) $tenant->id);
                $tenantInitialized = true;

                $tenantUser = User::query()
                    ->where('email', $credentials['email'])
                    ->first();

                if (!$tenantUser || (int) ($tenantUser->tenant_id ?? 0) !== (int) $tenant->id) {
                    return Utility::return_response(false, "Invalid tenant or credentials.", "", 422);
                }

                $credentials['tenant_id'] = (int) $tenant->id;
            }

            $claims = [];
            if ($tenant) {
                $claims['tenant_id'] = (int) $tenant->id;
            }

            if (!$token = JWTAuth::claims($claims)->attempt($credentials)) {
                return Utility::return_response(false, "Invalid credentials.", "", 422);
            }

            $authUser = Auth::user();
            if (!$authUser) {
                $this->invalidateIssuedToken($token);
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            if ((int) $authUser->is_active === 0) {
                $this->invalidateIssuedToken($token);
                return Utility::return_response(false, "User is in-active.", "", 422);
            }

            $data['access_token'] = $token;
            $data['user'] = User::select('name', 'email', 'phone', 'type', 'avatar', 'id', 'tenant_id')
                ->find($authUser->id);
            $data['is_allowed_discount'] = Utility::isDiscountAllowed();

            if (!$data['user'] || $data['user']->type !== 'Sales') {
                $this->invalidateIssuedToken($token);
                return Utility::return_response(false, "Only Sales Person can login.", "", 422);
            }

            $get_emp = Employee::where('user_id', $data['user']->id)->first();
            if (!$get_emp) {
                $this->invalidateIssuedToken($token);
                return Utility::return_response(false, "Employee Not Found.", "", 422);
            }

            $check_attendance = Attendance::where('employee_id', $get_emp->id)
                ->orderBy('id', 'desc')
                ->first();

            $flag_attendance = 0;
            if ($check_attendance) {
                if (!empty($check_attendance->check_in) && empty($check_attendance->check_out)) {
                    $flag_attendance = 1;
                }

                if (!empty($check_attendance->check_out) && !empty($check_attendance->check_out)) {
                    $flag_attendance = 0;
                }
            }

            $data['is_emp_login'] = $flag_attendance;
            $data['tenant'] = $tenant ? [
                'id' => (int) $tenant->id,
                'slug' => (string) $tenant->slug,
                'name' => (string) $tenant->name,
            ] : null;

            UserLogin::create([
                'user_id' => $data['user']->id,
                'login_date_time' => now(),
            ]);

            return Utility::return_response(true, "User Login successfully.", $data, 200);
        } catch (\Exception $e) {
            $this->invalidateIssuedToken($token);
            return Utility::return_response(false, "Token invalid or not provided.", "", 500);
        } finally {
            if ($tenantInitialized && !Auth::check()) {
                app(TenancyManager::class)->end();
                app()->forgetInstance('currentTenant');
                $this->configurePermissionScope(null);
            }
        }
    }

    public function get_user(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return Utility::return_response(false, "User not found.", '', 422);
            }

            $user_data = User::with('employee')->where('id', $user['id'])->first();

            return Utility::return_response(true, "user detail.", [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'employee_id' => $user_data['employee']->id ?? 0,
                'tenant_id' => (int) ($user['tenant_id'] ?? 0),
            ], 200);
        } catch (JWTException $e) {
            return Utility::return_response(false, "Token invalid or not provided.", "", 500);
        }
    }

    public function logout()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            $lastLogin = UserLogin::where('user_id', $user->id)
                ->whereNull('logout_date_time')
                ->latest()
                ->first();

            if ($lastLogin) {
                $lastLogin->update([
                    'logout_date_time' => now(),
                ]);
            }

            JWTAuth::invalidate(JWTAuth::getToken());

            return Utility::return_response(true, "User Logout Successfully.", "", 200);
        } catch (JWTException $e) {
            return Utility::return_response(false, "Token invalid or not provided.", "", 500);
        }
    }

    private function resolveTenantForApiLogin(Request $request, string $email): ?Tenant
    {
        if (!config('tenancy.enabled', false)) {
            return null;
        }

        $tenantIdHeader = (string) config('tenancy.header_tenant_id', 'X-Tenant-Id');
        $tenantSlugHeader = (string) config('tenancy.header_tenant_slug', 'X-Tenant-Slug');

        $tenantId = (int) ($request->header($tenantIdHeader) ?? $request->query('tenant_id') ?? 0);
        if ($tenantId > 0) {
            return Tenant::query()->where('id', $tenantId)->where('is_active', true)->first();
        }

        $tenantSlug = (string) ($request->header($tenantSlugHeader) ?? $request->query('tenant') ?? '');
        if ($tenantSlug !== '') {
            return Tenant::query()->where('slug', $tenantSlug)->where('is_active', true)->first();
        }

        $tenantIds = $this->resolveTenantIdsForEmailAcrossActiveTenants($email);
        if (count($tenantIds) === 1) {
            return Tenant::query()->where('id', $tenantIds[0])->where('is_active', true)->first();
        }

        $fallbackTenantId = $this->resolveTenantIdFromLandlordContext($email);
        if ($fallbackTenantId) {
            return Tenant::query()->where('id', $fallbackTenantId)->where('is_active', true)->first();
        }

        $host = strtolower((string) $request->getHost());
        if ($host === '' || $this->isCentralHost($host)) {
            return null;
        }

        $tenantDomain = TenantDomain::query()->where('domain', $host)->first();
        if (!$tenantDomain) {
            return null;
        }

        return Tenant::query()
            ->where('id', $tenantDomain->tenant_id)
            ->where('is_active', true)
            ->first();
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
                $this->configurePermissionScope((int) $tenant->id);

                if (User::query()->where('email', $email)->exists()) {
                    $tenantIds[] = (int) $tenant->id;
                }
            } catch (\Throwable $e) {
                // Skip unreachable tenant databases during discovery.
            } finally {
                $tenancy->end();
                app()->forgetInstance('currentTenant');
                $this->configurePermissionScope(null);
            }
        }

        return array_values(array_unique($tenantIds));
    }

    private function resolveTenantIdFromLandlordContext(string $email): ?int
    {
        if ($email === '') {
            return null;
        }

        $landlordUser = User::on('landlord')
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

        $creatorId = (int) ($landlordUser->created_by ?? 0);
        if ($creatorId > 0) {
            $creatorTenantId = (int) (User::on('landlord')
                ->where('id', $creatorId)
                ->value('tenant_id') ?? 0);

            if ($creatorTenantId > 0 && Tenant::query()->where('id', $creatorTenantId)->where('is_active', true)->exists()) {
                return $creatorTenantId;
            }
        }

        return null;
    }

    private function isCentralHost(string $host): bool
    {
        $centralHosts = (array) config('tenancy.central_hosts', ['localhost', '127.0.0.1']);
        $appHost = strtolower((string) parse_url((string) config('app.url', ''), PHP_URL_HOST));
        if ($appHost !== '') {
            $centralHosts[] = $appHost;
        }

        $normalizedHosts = array_values(array_unique(array_filter(array_map(
            static fn ($value) => strtolower(trim((string) $value)),
            $centralHosts
        ))));

        return in_array(strtolower(trim($host)), $normalizedHosts, true);
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

    private function invalidateIssuedToken(?string $token): void
    {
        if (empty($token)) {
            return;
        }

        try {
            JWTAuth::setToken($token)->invalidate();
        } catch (\Throwable $e) {
            // Best-effort cleanup only.
        }
    }
}
