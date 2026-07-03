<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WebsiteSignup;
use App\Support\Tenancy\TenancyManager;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\PermissionRegistrar;

class TenantOnboardingService
{
    public function activateWebsiteSignup(WebsiteSignup $signup, Plan $plan): array
    {
        $signup->refresh();
        $meta = (array) $signup->meta;

        if (!empty($meta['tenant_id']) && !empty($meta['user_id']) && !empty($meta['login_url'])) {
            $tenant = Tenant::query()->find((int) $meta['tenant_id']);
            $user = User::query()->find((int) $meta['user_id']);

            if ($tenant && $user) {
                return ['tenant' => $tenant, 'user' => $user];
            }
        }

        $this->ensureEmailIsAvailableForSignup($signup);

        $plainPassword = (string) ($meta['temp_password'] ?? Str::password(12));
        $templateCompanyId = $this->resolveTemplateCompanyId((int) env('WEBSITE_SIGNUP_TEMPLATE_COMPANY_ID', 0));
        $tenant = $this->createTenantUsingTenancyFlow(
            name: (string) ($signup->company_name ?: $signup->name ?: ('Tenant ' . $signup->id)),
            slug: Str::slug((string) ($signup->company_name ?: $signup->name ?: ('tenant-' . $signup->id))),
            templateCompanyId: $templateCompanyId,
            withSeed: true,
        );

        $meta = array_merge($meta, [
            'tenant_id' => $tenant->id,
            'tenant_slug' => $tenant->slug,
            'processing_stage' => 'tenant_created',
            'processing_message' => 'Workspace created. Finalizing account setup.',
            'temp_password' => $plainPassword,
        ]);
        $signup->update([
            'status' => 'provisioning',
            'meta' => $meta,
        ]);

        $companyUser = $this->createOrUpdateLandlordCompanyUser(
            tenant: $tenant,
            name: (string) $signup->name,
            email: (string) $signup->email,
            phone: $signup->phone,
            plainPassword: $plainPassword,
            plan: $plan,
        );

        $this->runProvisioningSequence($tenant, $companyUser, $templateCompanyId);
        $this->upsertSignupSubscription(
            tenant: $tenant,
            plan: $plan,
            user: $companyUser,
            amount: (float) $signup->amount,
            paymentRef: (string) ($signup->razorpay_payment_id ?: $signup->razorpay_order_id),
            notes: 'Website signup auto-activation',
        );

        $signup->refresh();
        $signup->update([
            'status' => 'activated',
            'meta' => array_merge((array) $signup->meta, [
                'tenant_id' => $tenant->id,
                'tenant_slug' => $tenant->slug,
                'user_id' => $companyUser->id,
                'login_email' => $companyUser->email,
                'temp_password' => $plainPassword,
                'login_url' => route('login', ['tenant' => $tenant->slug]),
                'activated_at' => now()->toDateTimeString(),
                'processing_stage' => 'completed',
                'processing_message' => 'Workspace is ready. You can login now.',
            ]),
        ]);

        return ['tenant' => $tenant, 'user' => $companyUser];
    }

    public function createTenantUsingTenancyFlow(
        string $name,
        ?string $slug = null,
        ?int $templateCompanyId = null,
        bool $withSeed = true,
    ): Tenant {
        $beforeMaxTenantId = (int) (Tenant::query()->max('id') ?? 0);
        $params = [
            'name' => $name,
            '--slug' => $slug,
        ];

        if ($withSeed) {
            $params['--with-seed'] = true;
        }

        if (($templateCompanyId ?? 0) > 0) {
            $params['--template-company-id'] = (string) $templateCompanyId;
        }

        $this->callArtisanOrFail(
            command: 'tenancy:create',
            params: array_filter($params, static fn ($value) => !is_null($value) && $value !== ''),
            errorMessage: 'Tenant creation command failed.'
        );

        $tenant = Tenant::query()
            ->where('id', '>', $beforeMaxTenantId)
            ->orderByDesc('id')
            ->first();

        if (!$tenant) {
            throw new RuntimeException('Tenant was created but tenant record could not be resolved.');
        }

        return $tenant;
    }

    public function createOrUpdateLandlordCompanyUser(
        Tenant $tenant,
        string $name,
        string $email,
        ?string $phone,
        string $plainPassword,
        Plan $plan,
    ): User {
        $user = User::query()->where('email', $email)->first();
        $planEndDate = $this->computePlanEndDate($plan);

        if (!$user) {
            $payload = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => Hash::make($plainPassword),
                'type' => 'company',
                'tenant_id' => $tenant->id,
                'plan' => $plan->id,
                'plan_expire_date' => $planEndDate,
                'created_by' => 0,
            ];

            if (Schema::hasColumn('users', 'is_enable_login')) {
                $payload['is_enable_login'] = 1;
            }
            if (Schema::hasColumn('users', 'is_active')) {
                $payload['is_active'] = 1;
            }
            if (Schema::hasColumn('users', 'delete_status')) {
                $payload['delete_status'] = 1;
            }

            $user = User::query()->create($payload);
            $user->update(['created_by' => $user->id]);
        } else {
            if ((int) ($user->tenant_id ?? 0) !== (int) $tenant->id) {
                throw new RuntimeException('This email is already registered. Please sign in with your existing account or use Forgot Password.');
            }

            $updates = [
                'name' => $name !== '' ? $name : $user->name,
                'phone' => $phone ?: $user->phone,
                'tenant_id' => $tenant->id,
                'type' => 'company',
                'plan' => $plan->id,
                'plan_expire_date' => $planEndDate,
            ];

            if (Schema::hasColumn('users', 'is_enable_login')) {
                $updates['is_enable_login'] = 1;
            }
            if (Schema::hasColumn('users', 'is_active')) {
                $updates['is_active'] = 1;
            }
            if (Schema::hasColumn('users', 'delete_status')) {
                $updates['delete_status'] = 1;
            }
            if ((int) ($user->created_by ?? 0) <= 0) {
                $updates['created_by'] = $user->id;
            }

            $user->update($updates);
        }

        $companyRole = Role::query()->where('name', 'company')->first();
        if ($companyRole && !$user->hasRole('company')) {
            $user->assignRole($companyRole);
        }

        return $user->fresh();
    }

    private function ensureEmailIsAvailableForSignup(WebsiteSignup $signup): void
    {
        $email = trim((string) $signup->email);
        if ($email === '') {
            return;
        }

        $meta = (array) $signup->meta;
        $linkedUserId = (int) ($meta['user_id'] ?? 0);

        $existingUser = User::query()
            ->where('email', $email)
            ->first();

        if (!$existingUser) {
            return;
        }

        if ($linkedUserId > 0 && (int) $existingUser->id === $linkedUserId) {
            return;
        }

        throw new RuntimeException('This email is already registered. Please sign in with your existing account or use Forgot Password.');
    }

    public function runProvisioningSequence(Tenant $tenant, User $companyUser, ?int $templateCompanyId = null): void
    {
        $this->callArtisanOrFail(
            command: 'tenancy:provision-sales',
            params: [
                'tenant' => (string) $tenant->id,
                '--sync-data' => true,
            ],
            errorMessage: 'Tenant provisioning failed.'
        );

        $this->callArtisanOrFail(
            command: 'tenancy:seed-permissions',
            params: [
                'tenant' => (string) $tenant->id,
                '--template-company-id' => (string) $companyUser->id,
            ],
            errorMessage: 'Tenant permission seed failed.'
        );

        $this->syncCompanyUserIntoTenant($tenant, $companyUser);
        $this->callArtisanOrFail(
            command: 'tenancy:seed-settings',
            params: array_filter([
                'tenant' => (string) $tenant->id,
                '--creator-id' => (string) $companyUser->id,
                '--template-creator-id' => ($templateCompanyId ?? 0) > 0 ? (string) $templateCompanyId : null,
            ], static fn ($value) => !is_null($value) && $value !== ''),
            errorMessage: 'Tenant settings seed failed.'
        );
    }

    public function upsertActiveSubscription(
        Tenant $tenant,
        Plan $plan,
        User $user,
        float $amount,
        ?string $paymentRef = null,
        string $notes = '',
    ): Subscription {
        $startsAt = now()->toDateString();
        $endsAt = $this->computePlanEndDate($plan);

        $existing = $tenant->currentSubscription()->first();
        if ($existing && (string) $existing->status !== 'trialing') {
            $existing->update([
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'amount' => $amount,
                'payment_ref' => $paymentRef,
                'notes' => $notes,
            ]);

            $subscription = $existing->fresh();
        } else {
            $subscription = Subscription::query()->create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'amount' => $amount,
                'payment_ref' => $paymentRef,
                'notes' => $notes,
                'created_by' => $user->id,
            ]);
        }

        $tenant->update(['is_active' => true]);

        return $subscription;
    }

    public function upsertSignupSubscription(
        Tenant $tenant,
        Plan $plan,
        User $user,
        float $amount,
        ?string $paymentRef = null,
        string $notes = '',
    ): Subscription {
        if ($this->planStartsAsTrial($plan)) {
            return $this->upsertTrialSubscription($tenant, $plan, $user, $notes);
        }

        return $this->upsertActiveSubscription($tenant, $plan, $user, $amount, $paymentRef, $notes);
    }

    public function upsertTrialSubscription(
        Tenant $tenant,
        Plan $plan,
        User $user,
        string $notes = '',
    ): Subscription {
        $startsAt = now()->toDateString();
        $endsAt = Carbon::today()->addDays((int) $plan->trial_days)->toDateString();
        $notes = $notes !== '' ? $notes : ((int) $plan->trial_days . ' day free trial');

        $existing = $tenant->currentSubscription()->first();
        if ($existing) {
            $existing->update([
                'plan_id' => $plan->id,
                'status' => 'trialing',
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'amount' => 0,
                'payment_ref' => null,
                'notes' => $notes,
            ]);

            $subscription = $existing->fresh();
        } else {
            $subscription = Subscription::query()->create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => 'trialing',
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'amount' => 0,
                'payment_ref' => null,
                'notes' => $notes,
                'created_by' => $user->id,
            ]);
        }

        $tenant->update(['is_active' => true]);

        return $subscription;
    }

    public function planStartsAsTrial(Plan $plan): bool
    {
        return (float) $plan->price <= 0 && (int) $plan->trial_days > 0;
    }

    public function resolveTemplateCompanyId(?int $preferred = null): int
    {
        $preferred = (int) ($preferred ?? 0);
        if ($preferred > 0 && User::query()->where('id', $preferred)->exists()) {
            return $preferred;
        }

        return (int) (User::query()->where('type', 'company')->orderBy('id')->value('id') ?? 0);
    }

    public function computePlanEndDate(Plan $plan): ?string
    {
        $start = Carbon::today();
        $cycle = (string) $plan->billing_cycle;

        if ($cycle === 'monthly') {
            return $start->copy()->addMonth()->toDateString();
        }
        if ($cycle === 'quarterly') {
            return $start->copy()->addMonths(3)->toDateString();
        }
        if ($cycle === 'yearly') {
            return $start->copy()->addYear()->toDateString();
        }
        if ($cycle === 'one_time') {
            return $start->copy()->addYears(10)->toDateString();
        }

        return null;
    }

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

            if (Schema::hasColumn('users', 'is_enable_login')) {
                $tenantUserData['is_enable_login'] = (int) ($landlordCompanyUser->is_enable_login ?? 1);
            }

            User::query()->updateOrCreate(
                ['id' => $landlordCompanyUser->id],
                $tenantUserData
            );

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

    private function callArtisanOrFail(string $command, array $params, string $errorMessage): string
    {
        $exitCode = Artisan::call($command, $params);
        $output = trim(Artisan::output());

        if ($exitCode !== 0) {
            Log::error($errorMessage, [
                'command' => $command,
                'params' => $params,
                'exit_code' => $exitCode,
                'output' => $output,
            ]);

            throw new RuntimeException($output !== '' ? $output : $errorMessage);
        }

        return $output;
    }
}
