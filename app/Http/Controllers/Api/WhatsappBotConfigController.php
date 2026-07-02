<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerPhone;
use App\Models\Device;
use App\Models\Entity;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStage;
use App\Models\Tenant;
use App\Models\WhatsappBotKnowledge;
use App\Models\WhatsappBotRule;
use App\Support\Tenancy\TenancyManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class WhatsappBotConfigController extends Controller
{
    public function resolve(Request $request)
    {
        if (!$this->isAuthorized($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'session_id' => 'required|string|max:80',
            'remote_jid' => 'required|string|max:80',
            'message' => 'nullable|string|max:2000',
        ]);

        $deviceId = $this->extractDeviceId($validated['session_id']);
        if (!$deviceId) {
            return response()->json(['message' => 'Invalid session id'], 422);
        }

        $device = $this->findDeviceForRequest($deviceId, $request);
        if (!$device || !$device->user) {
            return response()->json(['message' => 'Device not found'], 404);
        }

        $creatorId = $device->user->creatorId();
        $settings = DB::connection(app()->bound('currentTenant') ? 'tenant' : 'landlord')->table('settings')
            ->where('created_by', $creatorId)
            ->whereIn('name', [
                'wa_ai_bot_enabled',
                'wa_ai_bot_model',
                'wa_ai_bot_cooldown_seconds',
                'wa_ai_bot_reply_delay_ms',
                'wa_ai_bot_business_name',
                'wa_ai_bot_business_context',
                'wa_ai_bot_tone',
                'wa_ai_bot_fallback_text',
                'wa_ai_bot_system_prompt',
            ])
            ->pluck('value', 'name');

        $phone = $this->normalizePhoneFromJid($validated['remote_jid']);

        if (!Schema::hasTable('whatsapp_bot_rules') || !Schema::hasTable('whatsapp_bot_knowledge')) {
            return response()->json([
                'enabled' => false,
                'config' => [
                    'model' => (string) ($settings['wa_ai_bot_model'] ?? 'gpt-4o-mini'),
                    'cooldown_seconds' => (int) ($settings['wa_ai_bot_cooldown_seconds'] ?? 45),
                    'reply_delay_ms' => (int) ($settings['wa_ai_bot_reply_delay_ms'] ?? 600),
                    'business_name' => (string) ($settings['wa_ai_bot_business_name'] ?? ''),
                    'business_context' => (string) ($settings['wa_ai_bot_business_context'] ?? ''),
                    'tone' => (string) ($settings['wa_ai_bot_tone'] ?? ''),
                    'fallback_text' => (string) ($settings['wa_ai_bot_fallback_text'] ?? ''),
                    'system_prompt' => (string) ($settings['wa_ai_bot_system_prompt'] ?? ''),
                ],
                'lead' => null,
                'rule' => null,
                'knowledge' => [],
            ]);
        }

        $lead = $this->resolveLead($creatorId, $phone);

        $rule = null;
        if ($lead && $lead->stage_id) {
            $rule = WhatsappBotRule::where('created_by', $creatorId)
                ->where('lead_stage_id', $lead->stage_id)
                ->where('is_active', 1)
                ->first();
        }

        $knowledge = WhatsappBotKnowledge::where('created_by', $creatorId)
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit(50)
            ->get(['id', 'title', 'keywords', 'answer']);

        return response()->json([
            'enabled' => ($settings['wa_ai_bot_enabled'] ?? '0') === '1',
            'config' => [
                'model' => (string) ($settings['wa_ai_bot_model'] ?? 'gpt-4o-mini'),
                'cooldown_seconds' => (int) ($settings['wa_ai_bot_cooldown_seconds'] ?? 45),
                'reply_delay_ms' => (int) ($settings['wa_ai_bot_reply_delay_ms'] ?? 600),
                'business_name' => (string) ($settings['wa_ai_bot_business_name'] ?? ''),
                'business_context' => (string) ($settings['wa_ai_bot_business_context'] ?? ''),
                'tone' => (string) ($settings['wa_ai_bot_tone'] ?? ''),
                'fallback_text' => (string) ($settings['wa_ai_bot_fallback_text'] ?? ''),
                'system_prompt' => (string) ($settings['wa_ai_bot_system_prompt'] ?? ''),
            ],
            'lead' => $lead ? [
                'id' => $lead->id,
                'name' => $lead->name,
                'phone' => $lead->phone,
                'stage_id' => $lead->stage_id,
                'stage_name' => optional($lead->getLeadStatus)->name,
            ] : null,
            'rule' => $rule ? [
                'mode' => $rule->mode,
                'prompt_hint' => (string) $rule->prompt_hint,
                'template_text' => (string) $rule->template_text,
            ] : null,
            'knowledge' => $knowledge,
        ]);
    }

    public function captureLeadMessage(Request $request)
    {
        if (!$this->isAuthorized($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        Log::info('whatsapp_lead_capture_request', [
            'session_id' => $request->input('session_id'),
            'remote_jid' => $request->input('remote_jid'),
            'remote_jid_alt' => $request->input('remote_jid_alt'),
            'tenant_id' => $request->header((string) config('tenancy.header_tenant_id', 'X-Tenant-Id')),
            'tenant_slug' => $request->header((string) config('tenancy.header_tenant_slug', 'X-Tenant-Slug')),
        ]);

        $validated = $request->validate([
            'session_id' => 'required|string|max:80',
            'remote_jid' => 'required|string|max:120',
            'remote_jid_alt' => 'nullable|string|max:120',
            'participant' => 'nullable|string|max:120',
            'message' => 'nullable|string|max:2000',
            'push_name' => 'nullable|string|max:190',
        ]);

        $deviceId = $this->extractDeviceId($validated['session_id']);
        if (!$deviceId) {
            return response()->json(['message' => 'Invalid session id'], 422);
        }

        $device = $this->findDeviceForRequest($deviceId, $request);
        if (!$device || !$device->user) {
            return response()->json(['message' => 'Device not found'], 404);
        }

        $lead = $this->captureLeadForDevice($device, $validated);

        return response()->json([
            'enabled' => (int) ($device->is_lead_mobile_number ?? 0) === 1,
            'captured' => (bool) $lead,
            'lead_id' => $lead?->id,
        ]);
    }

    private function isAuthorized(Request $request): bool
    {
        $internalToken = (string) env('AUTHENTICATION_GLOBAL_AUTH_TOKEN', '');
        $headerToken = (string) ($request->header('X-Internal-Token') ?? '');

        return $internalToken !== '' && $headerToken === $internalToken;
    }

    private function findDeviceForRequest(int $deviceId, Request $request): ?Device
    {
        if (app()->bound('currentTenant')) {
            return Device::with('user')->find($deviceId);
        }

        $tenant = $this->tenantFromRequest($request);
        if ($tenant && $this->initializeTenant($tenant)) {
            $device = Device::with('user')->find($deviceId);
            if ($device && $device->user) {
                return $device;
            }

            $this->clearTenantContext();
        }

        $device = Device::with('user')->find($deviceId);
        if ($device && $device->user) {
            return $device;
        }

        foreach (Tenant::query()->where('is_active', true)->get() as $candidateTenant) {
            if (!$this->initializeTenant($candidateTenant)) {
                continue;
            }

            $device = Device::with('user')->find($deviceId);
            if ($device && $device->user) {
                return $device;
            }

            $this->clearTenantContext();
        }

        return null;
    }

    private function tenantFromRequest(Request $request): ?Tenant
    {
        $tenantIdHeader = (string) config('tenancy.header_tenant_id', 'X-Tenant-Id');
        $tenantSlugHeader = (string) config('tenancy.header_tenant_slug', 'X-Tenant-Slug');
        $tenantId = $request->header($tenantIdHeader) ?? $request->query('tenant_id');
        $tenantSlug = $request->header($tenantSlugHeader) ?? $request->query('tenant');

        if (!empty($tenantId)) {
            return Tenant::query()->where('id', (int) $tenantId)->where('is_active', true)->first();
        }

        if (!empty($tenantSlug)) {
            return Tenant::query()->where('slug', (string) $tenantSlug)->where('is_active', true)->first();
        }

        return null;
    }

    private function initializeTenant(Tenant $tenant): bool
    {
        try {
            app(TenancyManager::class)->initialize($tenant);
            app()->instance('currentTenant', $tenant);

            return true;
        } catch (Throwable) {
            $this->clearTenantContext();

            return false;
        }
    }

    private function clearTenantContext(): void
    {
        app(TenancyManager::class)->end();
        app()->forgetInstance('currentTenant');
    }

    private function extractDeviceId(string $sessionId): ?int
    {
        if (!str_starts_with($sessionId, 'device_')) {
            return null;
        }

        $id = (int) str_replace('device_', '', $sessionId);
        return $id > 0 ? $id : null;
    }

    private function normalizePhoneFromJid(string $remoteJid): string
    {
        $raw = explode('@', $remoteJid)[0] ?? '';
        return preg_replace('/\D+/', '', $raw) ?? '';
    }

    private function normalizePhoneFromCandidates(array $candidates): string
    {
        foreach ($candidates as $candidate) {
            if (str_contains((string) $candidate, '@lid')) {
                continue;
            }

            $phone = $this->normalizePhoneFromJid((string) $candidate);
            if ($phone !== '' && strlen($phone) >= 8) {
                return $phone;
            }
        }

        return '';
    }

    private function captureLeadForDevice(Device $device, array $payload): ?Lead
    {
        if ((int) ($device->is_lead_mobile_number ?? 0) !== 1) {
            return null;
        }

        $phone = $this->normalizePhoneFromCandidates([
            $payload['remote_jid_alt'] ?? '',
            $payload['remote_jid'] ?? '',
            $payload['participant'] ?? '',
        ]);

        if ($phone === '') {
            return null;
        }

        $connectionName = $device->getConnectionName() ?: config('database.default');

        return DB::connection($connectionName)->transaction(function () use ($device, $payload, $phone) {
            $creatorId = (int) $device->user->creatorId();
            $assignedUserId = $device->user->type === 'company' ? null : $device->user_id;
            $customerPhone = $this->findCustomerPhone($creatorId, $phone);
            $customer = null;

            if ($customerPhone) {
                $customer = Entity::where('id', $customerPhone->customer_id)
                    ->where('type', 'customer')
                    ->where('created_by', $creatorId)
                    ->first();
            }

            if (!$customer) {
                $name = trim((string) ($payload['push_name'] ?? ''));
                if ($name === '') {
                    $name = '+' . $phone;
                }

                $customer = Entity::create([
                    'name' => $name,
                    'company_name' => $name,
                    'type' => 'customer',
                    'is_active' => 1,
                    'created_by' => $creatorId,
                    'user_id' => $assignedUserId,
                ]);

                CustomerPhone::create([
                    'customer_id' => $customer->id,
                    'phone' => $phone,
                    'is_primary' => 1,
                    'is_secondary' => 0,
                    'is_whatsapp' => 1,
                ]);
            }

            $existingLead = Lead::where('customer_id', $customer->id)
                ->where('created_by', $creatorId)
                ->where('is_converted', 0)
                ->orderByDesc('id')
                ->first();

            if ($existingLead) {
                return $existingLead;
            }

            $stage = LeadStage::where('created_by', $creatorId)
                ->orderBy('order')
                ->orderBy('id')
                ->first();

            $source = LeadSource::firstOrCreate(
                [
                    'name' => 'WhatsApp',
                    'created_by' => $creatorId,
                ],
                [
                    'order' => 0,
                ]
            );

            $incomingText = trim((string) ($payload['message'] ?? ''));
            $notes = $incomingText !== ''
                ? 'WhatsApp incoming message: ' . $incomingText
                : 'Auto-generated from WhatsApp incoming message.';

            return Lead::create([
                'customer_id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $phone,
                'user_id' => $assignedUserId,
                'stage_id' => $stage?->id,
                'sources' => (string) $source->id,
                'notes' => $notes,
                'created_by' => $creatorId,
                'is_active' => 1,
                'date' => now()->toDateString(),
            ]);
        });
    }

    private function findCustomerPhone(int $creatorId, string $phone): ?CustomerPhone
    {
        $last10 = strlen($phone) > 10 ? substr($phone, -10) : $phone;

        return CustomerPhone::query()
            ->whereHas('customer', function ($query) use ($creatorId) {
                $query->where('type', 'customer')
                    ->where('created_by', $creatorId);
            })
            ->where(function ($query) use ($phone, $last10) {
                $query->whereRaw('REPLACE(REPLACE(REPLACE(phone, "+", ""), " ", ""), "-", "") = ?', [$phone])
                    ->orWhereRaw('RIGHT(REPLACE(REPLACE(REPLACE(phone, "+", ""), " ", ""), "-", ""), 10) = ?', [$last10]);
            })
            ->orderByDesc('is_primary')
            ->orderByDesc('id')
            ->first();
    }

    private function resolveLead(int $creatorId, string $phone): ?Lead
    {
        if ($phone === '') {
            return null;
        }

        $last10 = strlen($phone) > 10 ? substr($phone, -10) : $phone;

        return Lead::with('getLeadStatus')
            ->where('created_by', $creatorId)
            ->where(function ($query) use ($phone, $last10) {
                $query->whereRaw('REPLACE(REPLACE(REPLACE(phone, "+", ""), " ", ""), "-", "") = ?', [$phone])
                    ->orWhereRaw('RIGHT(REPLACE(REPLACE(REPLACE(phone, "+", ""), " ", ""), "-", ""), 10) = ?', [$last10]);
            })
            ->orderByDesc('id')
            ->first();
    }
}
