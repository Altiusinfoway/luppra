<?php

namespace App\Services;

use App\Models\Address;
use App\Models\BankDetail;
use App\Models\GstSlabMaster;
use App\Models\InvoiceTemplate;
use App\Models\LeadSource;
use App\Models\LeadStage;
use App\Models\LeadType;
use App\Models\OrderStage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TenantDefaultInitializationService
{
    private const COMPLETION_KEY = 'tenant_defaults_v1_initialized_at';

    public function hasCompletedCompanySettings(User $user): bool
    {
        if ($user->type !== 'company' || !app()->bound('currentTenant')) {
            return true;
        }

        if (!Schema::hasTable('settings')) {
            return false;
        }

        $creatorId = (int) $user->creatorId();
        $settings = DB::connection()
            ->table('settings')
            ->where('created_by', $creatorId)
            ->whereIn('name', [
                'website_name',
                'website_url',
                'website_short_name',
                'email',
                'phone',
                'pan_no',
                'gst_no',
                'company_address_id',
                'billing_address_id',
            ])
            ->pluck('value', 'name')
            ->toArray();

        foreach (['website_name', 'website_url', 'website_short_name', 'email', 'phone', 'pan_no', 'gst_no'] as $key) {
            if (trim((string) ($settings[$key] ?? '')) === '') {
                return false;
            }
        }

        $companyAddressId = (int) ($settings['company_address_id'] ?? 0);
        $billingAddressId = (int) ($settings['billing_address_id'] ?? 0);

        if ($companyAddressId <= 0 || $billingAddressId <= 0) {
            return false;
        }

        return $this->isAddressComplete(Address::find($companyAddressId))
            && $this->isAddressComplete(Address::find($billingAddressId));
    }

    public function initializeTenantDefaults(User $user): void
    {
        if ($user->type !== 'company' || !app()->bound('currentTenant')) {
            return;
        }

        if (Schema::hasTable('settings')) {
            $this->ensureLeadImportSettings((int) $user->creatorId());
        }

        if (!$this->hasCompletedCompanySettings($user)) {
            return;
        }

        if (!Schema::hasTable('settings')) {
            return;
        }

        if ($this->isInitializationCompleted($user)) {
            return;
        }

        $creatorId = (int) $user->creatorId();
        $tenantId = (int) data_get(app('currentTenant'), 'id', 0);

        DB::connection()->transaction(function () use ($user, $creatorId, $tenantId) {
            $this->initializeCompanySettings($user);
            $this->initializeLeadSettings($creatorId);
            $this->initializeGstSlabs($creatorId);
            $this->initializeOrderSettings($creatorId);
            $this->initializeInvoiceTemplateSelection($creatorId);
            $this->initializeDefaultBankAccountsIfNeeded($creatorId);
            $this->markInitializationCompleted($creatorId);
        });

        Log::info('Tenant default initialization completed.', [
            'tenant_id' => $tenantId,
            'creator_id' => $creatorId,
        ]);
    }

    public function initializeCompanySettings(User $user): bool
    {
        return $this->hasCompletedCompanySettings($user);
    }

    private function ensureLeadImportSettings(int $creatorId): void
    {
        foreach (['india_mart_key', 'facebook_spreadsheet_id'] as $name) {
            $this->upsertSetting($creatorId, $name, (string) ($this->getSettingValue($creatorId, $name) ?? ''));
        }
    }

    public function initializeLeadSettings(int $creatorId): void
    {
        if (Schema::hasTable('lead_stages')) {
            $hasIsEditableColumn = Schema::hasColumn('lead_stages', 'is_editable');
            $stageDefaults = [
                ['name' => 'New', 'color' => '#0e66f3'],
                ['name' => 'Proposal', 'color' => '#1ca6e6'],
                ['name' => 'Negotiation', 'color' => '#8b5cf6'],
                ['name' => 'Won', 'color' => '#0f883ba6'],
                ['name' => 'Close', 'color' => '#f59e0b'],
                ['name' => 'Not Interested', 'color' => '#f93816'],
            ];

            $order = (int) (LeadStage::query()->max('order') ?? 0);
            foreach ($stageDefaults as $stage) {
                $exists = LeadStage::query()
                    ->where('created_by', $creatorId)
                    ->where('name', $stage['name'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                $existingStage = LeadStage::query()
                    ->where('name', $stage['name'])
                    ->orderBy('id')
                    ->first();

                if ($existingStage) {
                    $updates = ['created_by' => $creatorId];
                    if ($hasIsEditableColumn) {
                        $updates['is_editable'] = 0;
                    }
                    if (empty($existingStage->color)) {
                        $updates['color'] = $stage['color'];
                    }
                    if ((int) ($existingStage->order ?? 0) <= 0) {
                        $updates['order'] = ++$order;
                    }
                    $existingStage->update($updates);
                    continue;
                }

                $payload = [
                    'name' => $stage['name'],
                    'color' => $stage['color'],
                    'created_by' => $creatorId,
                    'order' => ++$order,
                ];

                if ($hasIsEditableColumn) {
                    $payload['is_editable'] = 0;
                }

                LeadStage::query()->create($payload);
            }
        }

        if (Schema::hasTable('lead_sources')) {
            $hasSourceIsEditableColumn = Schema::hasColumn('lead_sources', 'is_editable');
            $sourceDefaults = ['facebook', 'others', 'instagram', 'india mart'];
            $order = (int) (LeadSource::query()->max('order') ?? 0);

            foreach ($sourceDefaults as $source) {
                $exists = LeadSource::query()
                    ->where('created_by', $creatorId)
                    ->where('name', $source)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $existingSource = LeadSource::query()
                    ->where('name', $source)
                    ->orderBy('id')
                    ->first();

                if ($existingSource) {
                    $updates = ['created_by' => $creatorId];
                    if ($hasSourceIsEditableColumn) {
                        $updates['is_editable'] = 0;
                    }
                    if ((int) ($existingSource->order ?? 0) <= 0) {
                        $updates['order'] = ++$order;
                    }
                    $existingSource->update($updates);
                    continue;
                }

                $payload = [
                    'name' => $source,
                    'created_by' => $creatorId,
                    'order' => ++$order,
                ];

                if ($hasSourceIsEditableColumn) {
                    $payload['is_editable'] = 0;
                }

                LeadSource::query()->create($payload);
            }
        }

        if (Schema::hasTable('lead_types')) {
            foreach (['Information Qualified Lead', 'Marketing Qualified Leads'] as $type) {
                $exists = LeadType::query()
                    ->where('created_by', $creatorId)
                    ->where('name', $type)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $existingType = LeadType::query()
                    ->where('name', $type)
                    ->orderBy('id')
                    ->first();

                if ($existingType) {
                    $existingType->update(['created_by' => $creatorId]);
                    continue;
                }

                LeadType::query()->create([
                    'name' => $type,
                    'created_by' => $creatorId,
                ]);
            }
        }
    }

    public function initializeGstSlabs(int $creatorId): void
    {
        if (!Schema::hasTable('gst_slab_masters')) {
            return;
        }

        foreach ([0, 5, 12, 18, 28] as $rate) {
            $exists = GstSlabMaster::query()
                ->where('created_by', $creatorId)
                ->where('rate', $rate)
                ->exists();

            if ($exists) {
                continue;
            }

            $existingRate = GstSlabMaster::query()
                ->where('rate', $rate)
                ->orderBy('id')
                ->first();

            if ($existingRate) {
                $existingRate->update(['created_by' => $creatorId]);
                continue;
            }

            GstSlabMaster::query()->create([
                'rate' => $rate,
                'created_by' => $creatorId,
            ]);
        }
    }

    public function initializeOrderSettings(int $creatorId): void
    {
        if (Schema::hasTable('order_stages')) {
            $stageDefaults = [
                ['name' => 'New', 'color' => '#3b82f6'],
                ['name' => 'Confirmed', 'color' => '#8b5cf6'],
                ['name' => 'Delivered', 'color' => '#22c55e'],
            ];

            $hasOrderColumn = Schema::hasColumn('order_stages', 'order');
            $order = $hasOrderColumn
                ? (int) (OrderStage::query()->max('order') ?? 0)
                : 0;

            $creatorStageCount = OrderStage::query()
                ->where('created_by', $creatorId)
                ->count();

            if ($creatorStageCount === 0) {
                $existingStagesQuery = OrderStage::query()->orderBy('id');

                if ($hasOrderColumn) {
                    $existingStagesQuery->orderBy('order');
                }

                foreach ($existingStagesQuery->get() as $existingStage) {
                    $updates = [
                        'created_by' => $creatorId,
                    ];

                    $defaultStage = collect($stageDefaults)->firstWhere('name', $existingStage->name);
                    if ($defaultStage && empty($existingStage->color)) {
                        $updates['color'] = $defaultStage['color'];
                    }

                    if ($hasOrderColumn && (int) ($existingStage->order ?? 0) <= 0) {
                        $updates['order'] = ++$order;
                    }

                    $existingStage->update($updates);
                }
            }

            $order = $hasOrderColumn
                ? (int) (OrderStage::query()->max('order') ?? 0)
                : 0;

            foreach ($stageDefaults as $stage) {
                $exists = OrderStage::query()
                    ->where('created_by', $creatorId)
                    ->where('name', $stage['name'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                $existingStage = OrderStage::query()
                    ->where('name', $stage['name'])
                    ->orderBy('id')
                    ->first();

                if ($existingStage) {
                    $updates = ['created_by' => $creatorId];
                    if (empty($existingStage->color)) {
                        $updates['color'] = $stage['color'];
                    }
                    if ($hasOrderColumn && (int) ($existingStage->order ?? 0) <= 0) {
                        $updates['order'] = ++$order;
                    }
                    $existingStage->update($updates);
                    continue;
                }

                $payload = [
                    'name' => $stage['name'],
                    'color' => $stage['color'],
                    'created_by' => $creatorId,
                ];

                if ($hasOrderColumn) {
                    $payload['order'] = ++$order;
                }

                OrderStage::query()->create($payload);
            }
        }

        $this->upsertSetting($creatorId, 'order_code_prefix', 'ORDER-');
    }

    public function initializeInvoiceTemplateSelection(int $creatorId): void
    {
        $current = trim((string) $this->getSettingValue($creatorId, 'invoice_layout'));
        if ($current === '') {
            $this->upsertSetting($creatorId, 'invoice_layout', 'layout_1');
        }

        $selectedTemplateId = trim((string) $this->getSettingValue($creatorId, 'default_invoice_template_id'));
        if ($selectedTemplateId !== '') {
            return;
        }

        if (!Schema::hasTable('invoice_templates')) {
            return;
        }

        $defaultTemplateId = (int) (InvoiceTemplate::query()
            ->where('is_default', true)
            ->value('id') ?? 0);

        if ($defaultTemplateId > 0) {
            $this->upsertSetting($creatorId, 'default_invoice_template_id', (string) $defaultTemplateId);
        }
    }

    public function initializeDefaultBankAccountsIfNeeded(int $creatorId): void
    {
        if (!Schema::hasTable('bank_details')) {
            return;
        }

        $count = BankDetail::query()->count();
        Log::info('Tenant bank account initialization skipped by default.', [
            'creator_id' => $creatorId,
            'existing_bank_accounts' => $count,
        ]);
    }

    private function isInitializationCompleted(User $user): bool
    {
        return trim((string) $this->getSettingValue((int) $user->creatorId(), self::COMPLETION_KEY)) !== '';
    }

    private function markInitializationCompleted(int $creatorId): void
    {
        $this->upsertSetting($creatorId, self::COMPLETION_KEY, now()->toDateTimeString());
    }

    private function getSettingValue(int $creatorId, string $name): ?string
    {
        return DB::connection()
            ->table('settings')
            ->where('created_by', $creatorId)
            ->where('name', $name)
            ->value('value');
    }

    private function upsertSetting(int $creatorId, string $name, string $value): void
    {
        DB::connection()->table('settings')->updateOrInsert(
            ['name' => $name, 'created_by' => $creatorId],
            ['value' => $value]
        );
    }

    private function isAddressComplete(?Address $address): bool
    {
        if (!$address) {
            return false;
        }

        return !empty($address->country)
            && !empty($address->state)
            && !empty($address->city)
            && trim((string) $address->zipcode) !== ''
            && trim((string) $address->address_line_1) !== '';
    }
}
