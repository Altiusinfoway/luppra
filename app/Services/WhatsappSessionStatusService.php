<?php

namespace App\Services;

use App\Models\Device;
use Illuminate\Support\Facades\Http;

class WhatsappSessionStatusService
{
    public function forDevice(Device $device): array
    {
        $sessionId = $device->whatsappSessionId();

        try {
            $response = Http::get($this->whatsappServerUrl() . '/sessions/status/' . $sessionId);
        } catch (\Throwable $e) {
            return $this->buildStatus(
                'not_ready',
                'WhatsApp session is not ready yet. Please reconnect this device.',
                null
            );
        }

        if ($response->status() === 404) {
            return $this->fallbackStatusFromDevice($device, null);
        }

        if ($response->status() !== 200) {
            return $this->fallbackStatusFromDevice($device, null);
        }

        $payload = $response->json();
        $normalizedStatus = data_get($payload, 'data.status');
        $canOpenChat = (bool) data_get($payload, 'data.can_open_chat', false);
        $shouldRedirectToQr = (bool) data_get($payload, 'data.should_redirect_to_qr', true);
        $qrAvailable = (bool) data_get($payload, 'data.qr_available', false);
        $message = (string) data_get($payload, 'data.message', data_get($payload, 'message', ''));

        if (is_string($normalizedStatus) && in_array($normalizedStatus, ['connected', 'connecting', 'disconnected', 'qr_required', 'not_ready'], true)) {
            return [
                'status' => $normalizedStatus,
                'message' => $message !== '' ? $message : $this->defaultMessage($normalizedStatus),
                'raw_status' => $normalizedStatus,
                'connected' => $normalizedStatus === 'connected' && $canOpenChat,
                'can_open_chat' => $canOpenChat,
                'should_redirect_to_qr' => $shouldRedirectToQr,
                'qr_available' => $qrAvailable,
            ];
        }

        $rawStatus = data_get($payload, 'data.status');
        return $this->mapLegacyStatus($device, is_string($rawStatus) ? $rawStatus : null);
    }

    private function mapLegacyStatus(Device $device, ?string $rawStatus): array
    {
        return match ($rawStatus) {
            'authenticated' => $this->buildStatus(
                'connected',
                'WhatsApp session is connected.',
                $rawStatus
            ),
            'connecting' => $this->buildStatus(
                'connecting',
                'WhatsApp is still connecting. Please try again in a moment.',
                $rawStatus
            ),
            'disconnecting', 'disconnected' => $this->buildStatus(
                'disconnected',
                'WhatsApp session is disconnected. Please reconnect this device.',
                $rawStatus
            ),
            'connected' => $this->fallbackStatusFromDevice($device, $rawStatus),
            default => $this->fallbackStatusFromDevice($device, $rawStatus),
        };
    }

    private function fallbackStatusFromDevice(Device $device, ?string $rawStatus): array
    {
        if (!empty($device->qr)) {
            return $this->buildStatus(
                'qr_required',
                'Please scan the QR code to connect this device.',
                $rawStatus
            );
        }

        return $this->buildStatus(
            'not_ready',
            'WhatsApp session is not ready yet. Please reconnect this device.',
            $rawStatus
        );
    }

    private function buildStatus(string $status, string $message, ?string $rawStatus): array
    {
        return [
            'status' => $status,
            'message' => $message,
            'raw_status' => $rawStatus,
            'connected' => $status === 'connected',
            'can_open_chat' => $status === 'connected',
            'should_redirect_to_qr' => $status !== 'connected',
            'qr_available' => $status === 'qr_required',
        ];
    }

    private function defaultMessage(string $status): string
    {
        return match ($status) {
            'connected' => 'WhatsApp session is connected.',
            'connecting' => 'WhatsApp is still connecting. Please try again in a moment.',
            'disconnected' => 'WhatsApp session is disconnected. Please reconnect this device.',
            'qr_required' => 'Please scan the QR code to connect this device.',
            default => 'WhatsApp session is not ready yet. Please reconnect this device.',
        };
    }

    private function whatsappServerUrl(): string
    {
        $url = (string) env('WA_SERVER_URL', '');
        if (trim($url) === '') {
            $url = 'http://127.0.0.1:8002';
        }

        return rtrim($url, '/');
    }
}
