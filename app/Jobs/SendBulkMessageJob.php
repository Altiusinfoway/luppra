<?php

namespace App\Jobs;

use App\Models\CustomerPhone;
use App\Models\Device;
use App\Models\Entity;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Services\WhatsappSessionStatusService;
use App\Support\Tenancy\TenantUsageService;
use App\Traits\Whatsapp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Whatsapp;

    public int $timeout = 1200;
    public int $tries = 1;

    public function __construct(
        private int $tenantId,
        private int $userId,
        private string $sendMode,
        private ?int $stageId,
        private array $customerIds,
        private int $deviceId,
        private string $message
    ) {
    }

    public function handle(WhatsappSessionStatusService $sessionStatusService): void
    {
        $stage = $this->stageId ? LeadStage::query()->find($this->stageId) : null;
        $device = Device::query()
            ->where('user_id', $this->userId)
            ->where('status', 1)
            ->find($this->deviceId);

            if (($this->sendMode === 'lead_status' && !$stage) || !$device) {
                Log::warning('bulk_message_job_invalid_stage_or_device', [
                    'tenant_id' => $this->tenantId,
                    'stage_id' => $this->stageId,
                    'send_mode' => $this->sendMode,
                    'device_id' => $this->deviceId,
                    'user_id' => $this->userId,
                ]);
                return;
            }

            $session = $sessionStatusService->forDevice($device);
            if (($session['status'] ?? 'not_ready') !== 'connected') {
                Log::warning('bulk_message_job_device_not_connected', [
                    'tenant_id' => $this->tenantId,
                    'device_id' => $device->id,
                    'status' => $session['status'] ?? null,
                    'message' => $session['message'] ?? null,
                ]);
                return;
            }

            $recipients = $this->sendMode === 'direct_customers'
                ? $this->recipientsForCustomers($this->customerIds)
                : $this->recipientsForStage((int) $this->stageId);

            if ($recipients->isEmpty()) {
                Log::info('bulk_message_job_no_recipients', [
                    'tenant_id' => $this->tenantId,
                    'send_mode' => $this->sendMode,
                    'stage_id' => $stage?->id,
                    'customer_ids' => array_slice($this->customerIds, 0, 50),
                ]);
                return;
            }

            $usage = app(TenantUsageService::class);
            if (!$usage->canSendWhatsapp($recipients->count())) {
                Log::warning('bulk_message_job_limit_reached', [
                    'tenant_id' => $this->tenantId,
                    'send_mode' => $this->sendMode,
                    'stage_id' => $stage?->id,
                    'recipient_count' => $recipients->count(),
                ]);
                return;
            }

            $sentCount = 0;
            $errors = [];

            foreach ($recipients as $recipient) {
                $message = $this->formatText($this->message, [
                    'name' => $recipient['name'],
                    'phone' => $recipient['phone'],
                ]);

                $response = $this->messageSend(
                    ['text' => $message],
                    $device->id,
                    $recipient['phone'],
                    'plain-text',
                    true,
                    0
                );

                if (($response['status'] ?? 500) === 200) {
                    $sentCount++;
                    continue;
                }

                $errors[] = [
                    'phone' => $recipient['phone'],
                    'message' => $response['message'] ?? 'Message failed.',
                ];
            }

            if ($sentCount > 0) {
                $usage->recordWhatsappSent($sentCount);
            }

            Log::info('bulk_message_job_completed', [
                'tenant_id' => $this->tenantId,
                'send_mode' => $this->sendMode,
                'stage_id' => $stage?->id,
                'device_id' => $device->id,
                'sent_count' => $sentCount,
                'failed_count' => count($errors),
                'errors' => array_slice($errors, 0, 10),
            ]);
    }

    private function recipientsForStage(int $stageId)
    {
        $leads = Lead::query()
            ->with('customer')
            ->where('stage_id', $stageId)
            ->whereNotNull('customer_id')
            ->get(['id', 'name', 'customer_id']);

        $customerIds = $leads->pluck('customer_id')->filter()->unique()->values();
        if ($customerIds->isEmpty()) {
            return collect();
        }

        $phones = CustomerPhone::query()
            ->whereIn('customer_id', $customerIds)
            ->where(function ($query) {
                $query->where('is_whatsapp', 1)
                    ->orWhere('is_primary', 1);
            })
            ->orderByDesc('is_whatsapp')
            ->orderByDesc('is_primary')
            ->get(['customer_id', 'phone', 'is_whatsapp', 'is_primary'])
            ->unique('customer_id')
            ->keyBy('customer_id');

        return $leads
            ->unique('customer_id')
            ->map(function (Lead $lead) use ($phones) {
                $phone = $phones->get($lead->customer_id);
                if (!$phone) {
                    return null;
                }

                $digits = $this->normalizeWhatsappPhone($phone->phone);
                if (strlen($digits) < 10 || strlen($digits) > 15) {
                    return null;
                }

                return [
                    'customer_id' => (int) $lead->customer_id,
                    'name' => (string) ($lead->customer?->name ?? $lead->name ?? ''),
                    'phone' => $digits,
                ];
            })
            ->filter()
            ->unique('phone')
            ->values();
    }

    private function recipientsForCustomers(array $customerIds)
    {
        $customerIds = collect($customerIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($customerIds->isEmpty()) {
            return collect();
        }

        $customers = Entity::query()
            ->where('type', 'customer')
            ->whereIn('id', $customerIds)
            ->get(['id', 'name', 'company_name'])
            ->keyBy('id');

        if ($customers->isEmpty()) {
            return collect();
        }

        $phones = CustomerPhone::query()
            ->whereIn('customer_id', $customers->keys())
            ->where(function ($query) {
                $query->where('is_whatsapp', 1)
                    ->orWhere('is_primary', 1);
            })
            ->orderByDesc('is_whatsapp')
            ->orderByDesc('is_primary')
            ->get(['customer_id', 'phone', 'is_whatsapp', 'is_primary'])
            ->unique('customer_id')
            ->keyBy('customer_id');

        return $customers
            ->map(function (Entity $customer) use ($phones) {
                $phone = $phones->get($customer->id);
                if (!$phone) {
                    return null;
                }

                $digits = $this->normalizeWhatsappPhone($phone->phone);
                if (strlen($digits) < 10 || strlen($digits) > 15) {
                    return null;
                }

                return [
                    'customer_id' => (int) $customer->id,
                    'name' => (string) ($customer->name ?: $customer->company_name ?: ''),
                    'phone' => $digits,
                ];
            })
            ->filter()
            ->unique('phone')
            ->values();
    }

    private function normalizeWhatsappPhone($phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '91') && strlen($digits) === 12) {
            return $digits;
        }

        if (strlen($digits) === 10) {
            return '91' . $digits;
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            return '91' . substr($digits, 1);
        }

        return $digits;
    }
}
