<?php

namespace App\Support\Tenancy;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantUsage;
use App\Models\User;
use Carbon\Carbon;

class TenantUsageService
{
    public function currentTenant(): ?Tenant
    {
        if (!app()->bound('currentTenant')) {
            return null;
        }

        $tenant = app('currentTenant');
        return $tenant instanceof Tenant ? $tenant : null;
    }

    public function currentPlan(): ?Plan
    {
        $tenant = $this->currentTenant();
        if (!$tenant) {
            return null;
        }

        $subscription = Subscription::query()
            ->where('tenant_id', $tenant->id)
            ->latest('id')
            ->first();

        if (!$subscription) {
            return null;
        }

        return Plan::query()->find($subscription->plan_id);
    }

    public function isEnforced(): bool
    {
        return (bool) config('tenancy.enforce_usage_limits', false);
    }

    public function userLimit(): ?int
    {
        $plan = $this->currentPlan();
        return $plan && !empty($plan->user_limit) ? (int) $plan->user_limit : null;
    }

    public function whatsappLimit(): ?int
    {
        $plan = $this->currentPlan();
        return $plan && !empty($plan->whatsapp_limit) ? (int) $plan->whatsapp_limit : null;
    }

    public function canCreateUser(int $increment = 1): bool
    {
        $tenant = $this->currentTenant();
        $limit = $this->userLimit();

        if (!$tenant || !$this->isEnforced() || $limit === null) {
            return true;
        }

        $count = User::query()->where('tenant_id', $tenant->id)->count();
        return ($count + $increment) <= $limit;
    }

    public function canSendWhatsapp(int $increment = 1): bool
    {
        $tenant = $this->currentTenant();
        $limit = $this->whatsappLimit();

        if (!$tenant || !$this->isEnforced() || $limit === null) {
            return true;
        }

        $used = $this->usageValue($tenant->id, 'whatsapp_messages_sent', $this->periodKey());
        return ($used + $increment) <= $limit;
    }

    public function recordWhatsappSent(int $count = 1): void
    {
        $tenant = $this->currentTenant();
        if (!$tenant || $count <= 0) {
            return;
        }

        $usage = TenantUsage::query()->firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'metric' => 'whatsapp_messages_sent',
                'period_key' => $this->periodKey(),
            ],
            ['value' => 0]
        );

        $usage->increment('value', $count);
    }

    private function usageValue(int $tenantId, string $metric, string $periodKey): int
    {
        return (int) TenantUsage::query()
            ->where('tenant_id', $tenantId)
            ->where('metric', $metric)
            ->where('period_key', $periodKey)
            ->value('value');
    }

    private function periodKey(): string
    {
        return Carbon::now()->format('Y-m');
    }
}
