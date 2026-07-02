<?php

namespace App\Support\Tenancy;

use App\Models\TenantAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TenantAuditLogger
{
    public static function log(string $event, ?int $tenantId = null, ?int $userId = null, string $message = '', array $meta = []): void
    {
        try {
            TenantAuditLog::query()->create([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'event' => $event,
                'message' => $message,
                'ip_address' => request()?->ip(),
                'user_agent' => self::trimUserAgent(request()),
                'meta' => $meta,
            ]);
        } catch (\Throwable $e) {
            Log::warning('tenant_audit_log_failed', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private static function trimUserAgent(?Request $request): ?string
    {
        $ua = $request?->userAgent();
        if (!$ua) {
            return null;
        }

        return mb_substr($ua, 0, 500);
    }
}

