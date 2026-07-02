<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantUsage;
use App\Models\User;
use App\Support\Tenancy\TenantAuditLogger;
use App\Support\Tenancy\TenancyManager;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Role;
use App\Services\TenantDefaultInitializationService;
use Spatie\Permission\PermissionRegistrar;

class TenantAdminController extends Controller
{
    private function syncCompanyUserIntoTenant(Tenant $tenant, User $landlordCompanyUser): void
    {
        app(TenancyManager::class)->initialize($tenant);
        app()->instance('currentTenant', $tenant);

        app('config')->set('permission.cache.key', "spatie.permission.cache.tenant.{$tenant->id}");
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        try {
            $tenantUserData = [
                'id' => $landlordCompanyUser->id,
                'name' => $landlordCompanyUser->name,
                'email' => $landlordCompanyUser->email,
                'phone' => $landlordCompanyUser->phone,
                'password' => $landlordCompanyUser->password,
                'type' => 'company',
                'tenant_id' => $tenant->id,
                'is_active' => (int) ($landlordCompanyUser->is_active ?? 1),
                'delete_status' => (int) ($landlordCompanyUser->delete_status ?? 1),
                'created_by' => $landlordCompanyUser->id,
            ];

            if (Schema::connection('tenant')->hasColumn('users', 'is_enable_login')) {
                $tenantUserData['is_enable_login'] = (int) ($landlordCompanyUser->is_enable_login ?? 1);
            }

            User::query()->updateOrCreate(
                ['id' => $landlordCompanyUser->id],
                $tenantUserData
            );

            if (Schema::connection('tenant')->hasTable('settings')) {
                DB::connection('tenant')->table('settings')->updateOrInsert(
                    ['name' => 'is_allowed_discount', 'created_by' => $landlordCompanyUser->id],
                    ['value' => (string) \App\Models\Utility::isDiscountAllowed($landlordCompanyUser->id)]
                );
            }

            $tenantCompanyUser = User::query()->find($landlordCompanyUser->id);
            $tenantCompanyRole = Role::query()->where('name', 'company')->first();

            if ($tenantCompanyUser && $tenantCompanyRole && !$tenantCompanyUser->hasRole('company')) {
                $tenantCompanyUser->assignRole($tenantCompanyRole);
            }
        } finally {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            app(TenancyManager::class)->end();
            app()->forgetInstance('currentTenant');
            app('config')->set('permission.cache.key', 'spatie.permission.cache.landlord');
        }
    }

    private function initializeTenantInvoiceTemplateSetting(Tenant $tenant, User $companyUser): void
    {
        app(TenancyManager::class)->initialize($tenant);
        app()->instance('currentTenant', $tenant);

        try {
            app(TenantDefaultInitializationService::class)
                ->initializeInvoiceTemplateSelection((int) $companyUser->creatorId());
        } finally {
            app(TenancyManager::class)->end();
            app()->forgetInstance('currentTenant');
        }
    }

    private function denyIfNotSuperAdmin()
    {
        if (!auth()->check() || auth()->user()->type !== 'super admin') {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        return null;
    }

    public function index()
    {
        if ($deny = $this->denyIfNotSuperAdmin()) {
            return $deny;
        }

        $tenants = Tenant::query()->orderByDesc('id')->get();
        $plans = Plan::query()
            ->where('created_by', auth()->user()->creatorId())
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();
        $requiredTables = (array) config('tenancy.health_required_tables', []);
        $tenantRows = [];

        foreach ($tenants as $tenant) {
            $status = [
                'db_ok' => false,
                'missing_tables' => [],
                'leads' => null,
                'quotes' => null,
                'orders' => null,
                'error' => null,
            ];

            try {
                app(TenancyManager::class)->initialize($tenant);
                DB::connection('tenant')->select('SELECT 1');
                $status['db_ok'] = true;

                foreach ($requiredTables as $table) {
                    if (!Schema::connection('tenant')->hasTable($table)) {
                        $status['missing_tables'][] = $table;
                    }
                }

                if (Schema::connection('tenant')->hasTable('leads')) {
                    $status['leads'] = DB::connection('tenant')->table('leads')->count();
                }
                if (Schema::connection('tenant')->hasTable('quotes')) {
                    $status['quotes'] = DB::connection('tenant')->table('quotes')->count();
                }
                if (Schema::connection('tenant')->hasTable('orders')) {
                    $status['orders'] = DB::connection('tenant')->table('orders')->count();
                }
            } catch (\Throwable $e) {
                $status['error'] = $e->getMessage();
            } finally {
                app(TenancyManager::class)->end();
            }

            $tenantRows[] = [
                'tenant' => $tenant,
                'status' => $status,
                'users_count' => User::query()->where('tenant_id', $tenant->id)->count(),
                'subscription' => $tenant->currentSubscription()->with('plan')->first(),
                'usage_whatsapp_month' => (int) TenantUsage::query()
                    ->where('tenant_id', $tenant->id)
                    ->where('metric', 'whatsapp_messages_sent')
                    ->where('period_key', now()->format('Y-m'))
                    ->value('value'),
            ];
        }

        $users = User::query()
            ->select('id', 'name', 'email', 'type', 'tenant_id')
            ->whereNotIn('type', ['super admin', 'client'])
            ->orderBy('id')
            ->get();

        return view('setting.tenancy', [
            'tenantRows' => $tenantRows,
            'users' => $users,
            'plans' => $plans,
        ]);
    }

    public function store(Request $request)
    {
        if ($deny = $this->denyIfNotSuperAdmin()) {
            return $deny;
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100',
            'database' => 'nullable|string|max:100',
            'domain' => 'nullable|string|max:190',
            'admin_name' => 'required|string|max:100',
            'admin_email' => 'required|email|max:190|unique:users,email',
            'admin_phone' => 'nullable|string|max:30',
            'admin_password' => 'required|string|min:8|confirmed',
            'template_company_id' => 'nullable|integer|exists:users,id',
            'with_seed' => 'nullable|boolean',
        ]);

        $beforeMaxTenantId = (int) (Tenant::query()->max('id') ?? 0);
        $params = [
            'name' => $request->name,
            '--slug' => $request->slug,
            '--database' => $request->database,
            '--domain' => $request->domain,
        ];

        $shouldSeed = $request->has('with_seed') ? $request->boolean('with_seed') : true;

        if ($shouldSeed) {
            $params['--with-seed'] = true;
            if (!empty($request->template_company_id)) {
                $params['--template-company-id'] = (string) $request->template_company_id;
            }
        }

        $createExitCode = Artisan::call('tenancy:create', array_filter($params, static fn ($val) => !is_null($val) && $val !== ''));
        $createOutput = trim(Artisan::output());

        if ($createExitCode !== 0) {
            Log::error('Tenant create command failed.', [
                'exit_code' => $createExitCode,
                'output' => $createOutput,
                'params' => $params,
            ]);

            return redirect()->route('setting.tenancy.index')->with('error', $createOutput !== '' ? $createOutput : 'Tenant creation command failed.');
        }

        $tenant = Tenant::query()
            ->where('id', '>', $beforeMaxTenantId)
            ->orderByDesc('id')
            ->first();

        if (!$tenant) {
            return redirect()->route('setting.tenancy.index')->with('error', 'Tenant was created but tenant record could not be resolved for admin user creation.');
        }

        $companyUserData = [
            'name' => $request->admin_name,
            'email' => $request->admin_email,
            'phone' => $request->admin_phone,
            'password' => Hash::make((string) $request->admin_password),
            'type' => 'company',
            'tenant_id' => $tenant->id,
            'is_active' => 1,
            'delete_status' => 1,
            'created_by' => 0,
        ];

        if (Schema::hasColumn('users', 'is_enable_login')) {
            $companyUserData['is_enable_login'] = 1;
        }

        $companyUser = User::query()->create($companyUserData);
        $companyUser->update(['created_by' => $companyUser->id]);

        if (Schema::connection('landlord')->hasTable('settings')) {
            DB::connection('landlord')->table('settings')->updateOrInsert(
                ['name' => 'is_allowed_discount', 'created_by' => $companyUser->id],
                ['value' => (string) \App\Models\Utility::isDiscountAllowed($companyUser->id)]
            );
        }

        $companyRole = Role::query()->where('name', 'company')->first();
        if ($companyRole && !$companyUser->hasRole('company')) {
            $companyUser->assignRole($companyRole);
        }

        // Re-provision with sync after admin user/role are created so tenant DB gets user + RBAC mappings.
        $provisionExitCode = Artisan::call('tenancy:provision-sales', [
            'tenant' => (string) $tenant->id,
            '--sync-data' => true,
        ]);
        if ($provisionExitCode !== 0) {
            $output = trim(Artisan::output());
            Log::error('Tenant provision command failed.', [
                'tenant_id' => $tenant->id,
                'exit_code' => $provisionExitCode,
                'output' => $output,
            ]);

            return redirect()->route('setting.tenancy.index')->with('error', $output !== '' ? $output : 'Tenant provisioning failed.');
        }

        $seedPermissionsExitCode = Artisan::call('tenancy:seed-permissions', [
            'tenant' => (string) $tenant->id,
            '--template-company-id' => (string) $companyUser->id,
        ]);
        if ($seedPermissionsExitCode !== 0) {
            $output = trim(Artisan::output());
            Log::error('Tenant seed-permissions command failed.', [
                'tenant_id' => $tenant->id,
                'exit_code' => $seedPermissionsExitCode,
                'output' => $output,
            ]);

            return redirect()->route('setting.tenancy.index')->with('error', $output !== '' ? $output : 'Tenant permission seed failed.');
        }

        $this->syncCompanyUserIntoTenant($tenant, $companyUser);
        $seedSettingsExitCode = Artisan::call('tenancy:seed-settings', array_filter([
            'tenant' => (string) $tenant->id,
            '--creator-id' => (string) $companyUser->id,
            '--template-creator-id' => !empty($request->template_company_id) ? (string) $request->template_company_id : null,
        ], static fn ($value) => !is_null($value) && $value !== ''));
        if ($seedSettingsExitCode !== 0) {
            $output = trim(Artisan::output());
            Log::error('Tenant seed-settings command failed.', [
                'tenant_id' => $tenant->id,
                'exit_code' => $seedSettingsExitCode,
                'output' => $output,
            ]);

            return redirect()->route('setting.tenancy.index')->with('error', $output !== '' ? $output : 'Tenant settings seed failed.');
        }
        $this->initializeTenantInvoiceTemplateSetting($tenant, $companyUser);

        $loginUrl = route('login', ['tenant' => $tenant->slug]);
        $successMessage = 'Tenant created successfully. Login URL: ' . $loginUrl;

        return redirect()->route('setting.tenancy.index')->with('success', $successMessage);
    }

    public function provision(Tenant $tenant)
    {
        if ($deny = $this->denyIfNotSuperAdmin()) {
            return $deny;
        }

        Artisan::call('tenancy:provision-sales', [
            'tenant' => (string) $tenant->id,
            '--sync-data' => true,
        ]);

        return redirect()->route('setting.tenancy.index')->with('success', trim(Artisan::output()));
    }

    public function seedMasters(Request $request, Tenant $tenant)
    {
        if ($deny = $this->denyIfNotSuperAdmin()) {
            return $deny;
        }

        $request->validate([
            'template_company_id' => 'nullable|integer|exists:users,id',
        ]);

        $params = [
            'tenant' => (string) $tenant->id,
        ];

        if (!empty($request->template_company_id)) {
            $params['--template-company-id'] = (string) $request->template_company_id;
        }

        Artisan::call('tenancy:seed-masters', $params);

        return redirect()->route('setting.tenancy.index')->with('success', trim(Artisan::output()));
    }

    public function health(Tenant $tenant)
    {
        if ($deny = $this->denyIfNotSuperAdmin()) {
            return $deny;
        }

        Artisan::call('tenancy:health', [
            'tenant' => (string) $tenant->id,
            '--strict' => true,
        ]);

        return redirect()->route('setting.tenancy.index')->with('success', trim(Artisan::output()));
    }

    public function assignUsers(Request $request, Tenant $tenant)
    {
        if ($deny = $this->denyIfNotSuperAdmin()) {
            return $deny;
        }

        $request->validate([
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
            'transfer_user_ids' => 'nullable|string|max:500',
        ]);

        $selectedIds = collect($request->input('user_ids', []))
            ->map(static fn ($id) => (int) $id)
            ->filter(static fn ($id) => $id > 0);

        if ($request->filled('transfer_user_ids')) {
            $transferIds = collect(explode(',', (string) $request->input('transfer_user_ids')))
                ->map(static fn ($id) => (int) trim($id))
                ->filter(static fn ($id) => $id > 0);

            $selectedIds = $selectedIds->merge($transferIds);
        }

        $selectedIds = $selectedIds->unique()->values();

        if ($selectedIds->isEmpty()) {
            return redirect()->back()->with('error', 'Please select at least one user.');
        }

        User::query()
            ->whereIn('id', $selectedIds->all())
            ->update(['tenant_id' => $tenant->id]);

        return redirect()->route('setting.tenancy.index')->with('success', 'Users mapped to tenant successfully.');
    }

    public function saveSubscription(Request $request, Tenant $tenant)
    {
        if ($deny = $this->denyIfNotSuperAdmin()) {
            return $deny;
        }

        $creatorId = auth()->user()->creatorId();
        $request->validate([
            'plan_id' => 'required|integer|exists:plans,id',
            'status' => 'required|in:trialing,active,expired,canceled',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'amount' => 'nullable|numeric|min:0',
            'payment_ref' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $plan = Plan::query()->where('id', $request->plan_id)->where('created_by', $creatorId)->first();
        if (!$plan) {
            return redirect()->back()->with('error', 'Selected plan is invalid for your account.');
        }

        [$startsAt, $endsAt] = $this->computeSubscriptionDates($plan, (string) $request->status, $request->starts_at);

        $existing = $tenant->currentSubscription()->first();
        if ($existing) {
            $existing->update([
                'plan_id' => $plan->id,
                'status' => $request->status,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'amount' => $request->amount ?? $plan->price,
                'payment_ref' => $request->payment_ref,
                'notes' => $request->notes,
            ]);
        } else {
            Subscription::query()->create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => $request->status,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'amount' => $request->amount ?? $plan->price,
                'payment_ref' => $request->payment_ref,
                'notes' => $request->notes,
                'created_by' => $creatorId,
            ]);
        }

        $tenant->update(['is_active' => in_array($request->status, ['trialing', 'active'], true)]);

        return redirect()->route('setting.tenancy.index')->with('success', 'Subscription updated successfully.');
    }

    private function computeSubscriptionDates(Plan $plan, string $status, ?string $startsAt): array
    {
        $start = $startsAt ? Carbon::parse($startsAt)->startOfDay() : Carbon::today();

        if ($status === 'trialing') {
            return [
                $start->toDateString(),
                $start->copy()->addDays((int) $plan->trial_days)->toDateString(),
            ];
        }

        if ($status !== 'active') {
            return [
                $start->toDateString(),
                $start->toDateString(),
            ];
        }

        $end = match ((string) $plan->billing_cycle) {
            'monthly' => $start->copy()->addMonth(),
            'quarterly' => $start->copy()->addMonths(3),
            'yearly' => $start->copy()->addYear(),
            'one_time' => $start->copy()->addYears(10),
            default => null,
        };

        return [
            $start->toDateString(),
            $end?->toDateString(),
        ];
    }

    public function toggleStatus(Tenant $tenant)
    {
        if ($deny = $this->denyIfNotSuperAdmin()) {
            return $deny;
        }

        $tenant->update(['is_active' => !$tenant->is_active]);

        return redirect()->route('setting.tenancy.index')->with('success', 'Tenant status updated.');
    }

    public function switchContext(Request $request)
    {
        if ($deny = $this->denyIfNotSuperAdmin()) {
            return $deny;
        }

        $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
        ]);

        $tenant = Tenant::query()->findOrFail((int) $request->tenant_id);
        $request->session()->put('impersonate_tenant_id', $tenant->id);

        TenantAuditLogger::log(
            event: 'tenant_context_switched',
            tenantId: $tenant->id,
            userId: (int) auth()->id(),
            message: 'Super admin switched tenant context.',
            meta: ['tenant_slug' => $tenant->slug]
        );

        return redirect()->back()->with('success', 'Tenant context switched to: '.$tenant->name);
    }

    public function clearContext(Request $request)
    {
        if ($deny = $this->denyIfNotSuperAdmin()) {
            return $deny;
        }

        $previousTenantId = (int) $request->session()->get('impersonate_tenant_id', 0);
        $request->session()->forget('impersonate_tenant_id');

        TenantAuditLogger::log(
            event: 'tenant_context_cleared',
            tenantId: $previousTenantId > 0 ? $previousTenantId : null,
            userId: (int) auth()->id(),
            message: 'Super admin cleared tenant context.'
        );

        return redirect()->back()->with('success', 'Tenant context cleared.');
    }
}
