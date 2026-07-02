<?php

namespace App\Traits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Device;
use App\Models\Template;
use App\Models\Smstransaction;
use Http;
trait Whatsapp
{
    private function resolveDeviceSessionId($deviceId): string
    {
        static $sessionIds = [];

        $deviceId = (int) $deviceId;
        if ($deviceId <= 0) {
            return 'device_' . $deviceId;
        }

        if (!array_key_exists($deviceId, $sessionIds)) {
            $device = Device::query()->select('id', 'uuid')->find($deviceId);
            $sessionIds[$deviceId] = $device?->whatsappSessionId() ?? ('device_' . $deviceId);
        }

        return $sessionIds[$deviceId];
    }

    private function resolveDeviceStorePath($deviceId): string
    {
        return base_path('sessions/' . $this->resolveDeviceSessionId($deviceId) . '_store.json');
    }

    private function whatsappServerUrl()
    {
        $url = (string) env('WA_SERVER_URL', '');
        if (trim($url) === '') {
            $url = 'http://127.0.0.1:8002';
        }
        return rtrim($url, '/');
    }

    private function resolveAttachmentForNode($attachment)
    {
        $attachment = (string) $attachment;
        if ($attachment === '') {
            return $attachment;
        }

        if (filter_var($attachment, FILTER_VALIDATE_URL)) {
            $safePath = $this->resolveSafeStoredAttachmentPath($attachment);
            if ($safePath !== null) {
                return $safePath;
            }
        }

        return $attachment;
    }

    private function attachmentAllowedRoots()
    {
        $roots = [
            public_path('uploads'),
            public_path('storage/uploads'),
            storage_path('uploads'),
        ];

        return array_values(array_filter(array_map(function ($root) {
            $resolved = realpath($root);
            return is_string($resolved) && $resolved !== '' ? $resolved : null;
        }, $roots)));
    }

    private function isPathInsideAllowedAttachmentRoots($path)
    {
        $resolvedPath = realpath($path);
        if (!is_string($resolvedPath) || $resolvedPath === '') {
            return false;
        }

        foreach ($this->attachmentAllowedRoots() as $root) {
            if ($resolvedPath === $root || str_starts_with($resolvedPath, $root . DIRECTORY_SEPARATOR)) {
                return true;
            }
        }

        return false;
    }

    private function resolveSafeStoredAttachmentPath($attachment)
    {
        $attachment = trim((string) $attachment);
        if ($attachment === '') {
            return null;
        }

        $rawPath = $attachment;
        if (filter_var($attachment, FILTER_VALIDATE_URL)) {
            $rawPath = (string) (parse_url($attachment, PHP_URL_PATH) ?: '');
        }

        if ($rawPath === '') {
            return null;
        }

        $rawPath = str_replace('\\', '/', $rawPath);
        $appBasePath = trim((string) parse_url(url('/'), PHP_URL_PATH), '/');
        $rawPath = ltrim($rawPath, '/');

        if ($appBasePath !== '' && str_starts_with($rawPath, $appBasePath . '/')) {
            $rawPath = substr($rawPath, strlen($appBasePath . '/'));
        }

        $candidates = [public_path($rawPath)];

        if (str_starts_with($rawPath, 'storage/uploads/')) {
            $candidates[] = storage_path(substr($rawPath, strlen('storage/')));
        }

        if (preg_match('/^[A-Za-z]:[\\\\\\/]/', $rawPath) === 1 || str_starts_with($rawPath, '/')) {
            $candidates[] = $rawPath;
        }

        foreach ($candidates as $candidate) {
            if (!is_string($candidate) || $candidate === '' || !File::exists($candidate)) {
                continue;
            }

            // Only allow server-generated files that stay inside strict upload directories.
            if ($this->isPathInsideAllowedAttachmentRoots($candidate)) {
                $resolved = realpath($candidate);
                if (is_string($resolved) && $resolved !== '') {
                    return $resolved;
                }
            }
        }

        return null;
    }

    private function messageSend($data,$from, $reciver,$type,$filter=false,$delay = 0)
    {
        \Log::info('----------- messageSend() ----------');

        \Log::info('messageSend()', [
            'data' => $data,
            'from' => $from,
            'receiver' => $reciver,
            'type' => $type,
            'filter' => $filter,
            'delay' => $delay,
        ]);

        // $delay = $delay == 0 ? env('DELAY_TIME',1000) : $delay;

        // if ($delay < 500) {
        //     $delay = 1;
        // }
        // else{
        //    $delay =  $delay/1000;
        //    $delay = round($delay);
        // }

        // sleep($delay);

        $device=Device::where('id',$from)->first();
        if (empty($device)) {
            return false;
        }
        \Log::info('device ',[ $device]);

        //creating session id
        $session_id = $device->whatsappSessionId();

        //formating message
        $message=$this->formatBody($data['message'] ?? '',$device->user_id);

        //formating array context
        $formatedBody= $filter == false ? $this->formatArray($data,$message,$type) : $data;

        //get server url
        $whatsServer = $this->whatsappServerUrl();

        //formating array before sending data to server
        $body['receiver']=$reciver;
        $body['delay']=0;
        $body['message']=$formatedBody;

        //sending data to whatsapp server
        try {
            $response=Http::post($whatsServer.'/chats/send?id='.$session_id,$body);

            \Log::info("inside try block",[$response]);
            $status=$response->status();

            if ($status != 200) {
                $responseBody=json_decode($response->body());
                $responseData['message']=$responseBody->message;
                $responseData['status']=$status;

                \Log::info('----status not 200 & its response ------ ',[$responseData]);
            }
            else{
                $responseData['status'] = 200;
            }
            \Log::info('----out----');
            return $responseData;
       } catch (\Throwable $e) {
         \Log::error('----messageSend exception ----', ['error' => $e->getMessage()]);
           $responseData['status'] = 403;
           $responseData['message'] = $e->getMessage();
           return $responseData;
       }

    }

    private function getChats($device_id)
    {
        $session_id = $this->resolveDeviceSessionId($device_id);
        $whatsServer = $this->whatsappServerUrl();

        $response=Http::get($whatsServer.'/chats?id='.$session_id);
        $status=$response->status();
        $contactIndexes = $this->getStoreContactIndexes($device_id);
        $resolvedChats = [];

        if ($status == 200) {
            $responseBody  =json_decode($response->body());
            $colllections  = collect($responseBody->data);
            $resolvedChats = $this->mergeResolvedChats(
                $resolvedChats,
                $colllections->map(function($item) use ($contactIndexes) {
                    return $this->buildResolvedChatData($contactIndexes, [
                        'raw_jid' => $item->id ?? '',
                        'pn_jid' => $item->resolvedPhoneJid ?? $item->pnJid ?? '',
                        'alt_jid' => $item->altJid ?? $this->extractChatAltJid($item),
                        'account_lid' => $item->accountLid ?? '',
                        'labels' => [
                            $item->contactName ?? '',
                            $item->name ?? '',
                            $item->notify ?? '',
                            $item->displayName ?? '',
                        ],
                        'unread' => $item->unreadCount ?? 0,
                        'timestamp' => $item->conversationTimestamp ?? 0,
                        'latest_message' => $this->extractLatestMessagePreview($item->messages ?? []),
                    ]);
                })->all()
            );
        }

        $resolvedChats = $this->mergeResolvedChats(
            $resolvedChats,
            $this->getPrimaryStoreResolvedChats($device_id, $contactIndexes)
        );

        $contacts = collect($resolvedChats)
            ->filter(function ($item) {
                return !empty($item['number']);
            })
            ->map(function ($item) use ($device_id) {
                return $this->applyChatReadState($device_id, $item);
            })
            ->sortByDesc('timestamp')
            ->values();

        if ($contacts->isNotEmpty()) {
            $responseData['status'] = 200;
            $responseData['data'] = $contacts;
            return $responseData;
        }

        if ($status != 200) {
            $responseBody=json_decode($response->body());
            $responseData['message']=$responseBody->message ?? 'Unable to load chats';
            $responseData['status']=$status;
            return $responseData;
        }

        $responseData['status'] = 200;
        $responseData['data'] = collect();

        return $responseData;

    }

    private function getChatMessages($device_id, $jid, $limit = 60, $isGroup = false, $number = '')
    {
        $session_id = $this->resolveDeviceSessionId($device_id);
        $whatsServer = $this->whatsappServerUrl();
        $jid = trim((string) $jid);

        $query = [
            'id' => $session_id,
            'limit' => (int) $limit,
            'isGroup' => $isGroup ? 'true' : 'false',
        ];

        \Log::info('WA getChatMessages request', [
            'session_id' => $session_id,
            'jid' => $jid,
            'limit' => (int) $limit,
            'is_group' => $isGroup,
            'url' => $whatsServer . '/chats/' . urlencode($jid),
        ]);

        $response = Http::get($whatsServer . '/chats/' . urlencode($jid), $query);
        $status = $response->status();

        if ($status != 200) {
            $responseBody = json_decode($response->body(), true);
            \Log::warning('WA getChatMessages non-200', [
                'session_id' => $session_id,
                'jid' => $jid,
                'status' => $status,
                'body' => $response->body(),
            ]);
            $responseData['message'] = $responseBody['message'] ?? 'Failed to load messages';
            $responseData['status'] = $status;
            return $responseData;
        }

        $responseBody = json_decode($response->body(), true);
        $messages = $responseBody['data'] ?? [];
        \Log::info('WA getChatMessages response', [
            'session_id' => $session_id,
            'jid' => $jid,
            'status' => $status,
            'raw_count' => is_array($messages) ? count($messages) : 0,
        ]);

        if (!is_array($messages)) {
            $messages = [];
        }

        $this->markMessagesAsRead($device_id, $this->extractReadKeys($messages));

        if (!empty($messages) && array_keys($messages) !== range(0, count($messages) - 1)) {
            $messages = array_values($messages);
        }

        $normalized = collect($messages)
            ->map(function ($message) {
                return $this->normalizeChatMessage($message);
            })
            ->filter(function ($message) {
                return !empty($message['id']);
            })
            ->sortBy('timestamp')
            ->values()
            ->all();

        $this->rememberChatReadState($device_id, $number, $jid, $normalized);

        $responseData['status'] = 200;
        $responseData['data'] = $normalized;
        return $responseData;
    }

    private function normalizeChatMessage($message)
    {
        $message = is_array($message) ? $message : [];
        $key = $message['key'] ?? [];
        $rawMessage = $message['message'] ?? [];
        [$resolvedMessage, $type] = $this->resolveMessageNode($rawMessage);

        $timestamp = $message['messageTimestamp'] ?? 0;
        if (is_array($timestamp)) {
            $timestamp = $timestamp['low'] ?? 0;
        }

        $remoteJid = (string) ($key['remoteJid'] ?? '');
        $participant = (string) ($key['participant'] ?? '');
        $senderJid = $participant !== '' ? $participant : $remoteJid;

        return [
            'id' => (string) ($key['id'] ?? ''),
            'from_me' => (bool) ($key['fromMe'] ?? false),
            'timestamp' => (int) $timestamp,
            'sender' => $this->cleanJid($senderJid),
            'remote_jid' => $remoteJid,
            'type' => $type ?? 'unknown',
            'text' => $this->extractMessageText($resolvedMessage, $type),
            'media' => $this->extractMediaMetadata($resolvedMessage, $type),
        ];
    }

    private function normalizeStoredMessageEntry($entry)
    {
        if (is_object($entry)) {
            $entry = json_decode(json_encode($entry), true);
        }

        if (!is_array($entry)) {
            return [];
        }

        $message = $entry['message'] ?? $entry;
        if (is_object($message)) {
            $message = json_decode(json_encode($message), true);
        }

        return is_array($message) ? $message : [];
    }

    private function extractLatestMessagePreview($messages)
    {
        if (!is_array($messages) || empty($messages)) {
            return '';
        }

        $latestText = '';
        $latestTimestamp = 0;

        foreach ($messages as $entry) {
            $message = $this->normalizeStoredMessageEntry($entry);
            if (empty($message)) {
                continue;
            }

            $timestamp = $this->normalizeTimestampValue($message['messageTimestamp'] ?? $message['timestamp'] ?? 0);
            if ($timestamp < $latestTimestamp) {
                continue;
            }

            $normalized = $this->normalizeChatMessage($message);
            $text = trim((string) ($normalized['text'] ?? ''));

            if ($timestamp > $latestTimestamp || $latestText === '') {
                $latestTimestamp = $timestamp;
                $latestText = $text;
            }
        }

        return $latestText;
    }

    private function extractReadKeys(array $messages)
    {
        $keys = [];
        $seen = [];

        foreach ($messages as $entry) {
            $message = $this->normalizeStoredMessageEntry($entry);
            if (empty($message)) {
                continue;
            }

            $key = is_array($message['key'] ?? null) ? $message['key'] : [];
            $messageId = trim((string) ($key['id'] ?? ''));
            $remoteJid = trim((string) ($key['remoteJid'] ?? ''));
            $participant = trim((string) ($key['participant'] ?? ''));
            $fromMe = (bool) ($key['fromMe'] ?? false);

            if ($fromMe || $messageId === '' || $remoteJid === '') {
                continue;
            }

            $dedupeKey = $remoteJid . ':' . $messageId . ':' . $participant;
            if (isset($seen[$dedupeKey])) {
                continue;
            }

            $seen[$dedupeKey] = true;

            $payload = [
                'remoteJid' => $remoteJid,
                'id' => $messageId,
                'fromMe' => false,
            ];

            if ($participant !== '') {
                $payload['participant'] = $participant;
            }

            $keys[] = $payload;
        }

        return $keys;
    }

    private function markMessagesAsRead($device_id, array $keys)
    {
        if (empty($keys)) {
            return;
        }

        $session_id = $this->resolveDeviceSessionId($device_id);
        $whatsServer = $this->whatsappServerUrl();

        try {
            Http::post($whatsServer . '/chats/read?id=' . $session_id, [
                'keys' => $keys,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('WA markMessagesAsRead failed', [
                'session_id' => $session_id,
                'count' => count($keys),
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function rememberChatReadState($device_id, $number, $jid, array $messages)
    {
        $keys = $this->chatReadStateKeys($number, $jid);
        if (empty($keys)) {
            return;
        }

        $latestTimestamp = 0;
        foreach ($messages as $message) {
            $latestTimestamp = max($latestTimestamp, $this->normalizeTimestampValue($message['timestamp'] ?? 0));
        }

        $readAt = max(time(), $latestTimestamp);
        $cacheKey = $this->chatReadStateCacheKey($device_id);
        $state = Cache::get($cacheKey, []);
        if (!is_array($state)) {
            $state = [];
        }

        foreach ($keys as $key) {
            $state[$key] = max($this->normalizeTimestampValue($state[$key] ?? 0), $readAt);
        }

        Cache::put($cacheKey, $state, now()->addDays(30));
    }

    private function applyChatReadState($device_id, array $chat)
    {
        if ((int) ($chat['unread'] ?? 0) <= 0) {
            return $chat;
        }

        $state = Cache::get($this->chatReadStateCacheKey($device_id), []);
        if (!is_array($state) || empty($state)) {
            return $chat;
        }

        $readAt = 0;
        foreach ($this->chatReadStateKeys($chat['number'] ?? '', $chat['jid'] ?? '') as $key) {
            $readAt = max($readAt, $this->normalizeTimestampValue($state[$key] ?? 0));
        }
        foreach (['raw_jid', 'pn_jid', 'alt_jid'] as $field) {
            foreach ($this->chatReadStateKeys('', $chat[$field] ?? '') as $key) {
                $readAt = max($readAt, $this->normalizeTimestampValue($state[$key] ?? 0));
            }
        }

        $timestamp = $this->normalizeTimestampValue($chat['timestamp'] ?? 0);
        if ($readAt > 0 && ($timestamp === 0 || $timestamp <= $readAt)) {
            $chat['unread'] = 0;
        }

        return $chat;
    }

    private function chatReadStateCacheKey($device_id)
    {
        return 'wa_chat_read_state_' . (int) $device_id;
    }

    private function chatReadStateKeys($number, $jid)
    {
        $keys = [];
        $normalizedNumber = preg_replace('/\D+/', '', (string) $number);
        $normalizedJid = trim((string) $jid);

        if ($normalizedNumber !== '') {
            $keys[] = 'number:' . $normalizedNumber;
        }

        if ($normalizedJid !== '') {
            $keys[] = 'jid:' . $normalizedJid;

            if (!str_contains($normalizedJid, '@')) {
                $jidDigits = preg_replace('/\D+/', '', $normalizedJid);
                if ($jidDigits !== '') {
                    $keys[] = 'number:' . $jidDigits;
                }
            }
        }

        return array_values(array_unique($keys));
    }

    private function resolveMessageNode($rawMessage)
    {
        $messageNode = is_array($rawMessage) ? $rawMessage : [];
        $maxDepth = 6;

        while ($maxDepth-- > 0 && is_array($messageNode) && !empty($messageNode)) {
            $keys = array_keys($messageNode);
            $type = $keys[0] ?? null;

            if (empty($type)) {
                break;
            }

            if (in_array($type, ['ephemeralMessage', 'viewOnceMessage', 'viewOnceMessageV2', 'viewOnceMessageV2Extension'], true)) {
                $nextNode = $messageNode[$type]['message'] ?? [];
                if (is_array($nextNode) && !empty($nextNode)) {
                    $messageNode = $nextNode;
                    continue;
                }
            }

            if ($type === 'documentWithCaptionMessage') {
                $nextNode = $messageNode[$type]['message'] ?? [];
                if (is_array($nextNode) && !empty($nextNode)) {
                    $messageNode = $nextNode;
                    continue;
                }
            }

            return [$messageNode, $type];
        }

        return [$messageNode, null];
    }

    private function extractMessageText($rawMessage, $type = null)
    {
        if (!is_array($rawMessage) || empty($rawMessage)) {
            return '';
        }

        if (isset($rawMessage['conversation'])) {
            return (string) $rawMessage['conversation'];
        }

        if (isset($rawMessage['extendedTextMessage']['text'])) {
            return (string) $rawMessage['extendedTextMessage']['text'];
        }

        if (isset($rawMessage['imageMessage']['caption'])) {
            return (string) $rawMessage['imageMessage']['caption'];
        }

        if (isset($rawMessage['videoMessage']['caption'])) {
            return (string) $rawMessage['videoMessage']['caption'];
        }

        if (isset($rawMessage['documentMessage']['caption'])) {
            return (string) $rawMessage['documentMessage']['caption'];
        }

        if (isset($rawMessage['buttonsResponseMessage']['selectedDisplayText'])) {
            return (string) $rawMessage['buttonsResponseMessage']['selectedDisplayText'];
        }

        if (isset($rawMessage['listResponseMessage']['title'])) {
            return (string) $rawMessage['listResponseMessage']['title'];
        }

        if ($type === 'audioMessage') {
            return '[Audio]';
        }
        if ($type === 'stickerMessage') {
            return '[Sticker]';
        }
        if ($type === 'imageMessage') {
            return '[Image]';
        }
        if ($type === 'videoMessage') {
            return '[Video]';
        }
        if ($type === 'documentMessage') {
            return '[Document]';
        }

        return '[Unsupported message]';
    }

    private function extractMediaMetadata($rawMessage, $type = null)
    {
        if (!is_array($rawMessage) || empty($type)) {
            return null;
        }

        $supportedTypes = [
            'imageMessage',
            'videoMessage',
            'audioMessage',
            'documentMessage',
        ];

        if (!in_array($type, $supportedTypes, true)) {
            return null;
        }

        $mediaNode = $rawMessage[$type] ?? [];
        if (!is_array($mediaNode)) {
            $mediaNode = [];
        }

        return [
            'kind' => match ($type) {
                'imageMessage' => 'image',
                'videoMessage' => 'video',
                'audioMessage' => 'audio',
                'documentMessage' => 'document',
                default => 'unknown',
            },
            'mime_type' => (string) ($mediaNode['mimetype'] ?? ''),
            'file_name' => (string) ($mediaNode['fileName'] ?? ''),
            'caption' => (string) ($mediaNode['caption'] ?? ''),
        ];
    }

    private function cleanJid($jid)
    {
        if (!is_string($jid) || $jid === '') {
            return '';
        }

        $parts = explode('@', $jid);
        return (string) ($parts[0] ?? '');
    }

    private function normalizeTimestampValue($value)
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_object($value) || is_array($value)) {
            $arr = (array) $value;
            if (isset($arr['low']) && is_numeric($arr['low'])) {
                return (int) $arr['low'];
            }
        }

        return 0;
    }

    private function getStoreContactIndexes($device_id)
    {
        $storePath = $this->resolveDeviceStorePath($device_id);
        $byJid = [];
        $byLid = [];
        $phoneByLid = [];
        $nameByLid = [];
        $nameByJid = [];

        foreach ($this->candidateStorePaths($storePath) as $candidateStorePath) {
            $payload = $this->loadStorePayload($candidateStorePath);
            if (!is_array($payload)) {
                continue;
            }

            $this->mergeStorePayloadIntoIndexes(
                $payload,
                $byJid,
                $byLid,
                $phoneByLid,
                $nameByLid,
                $nameByJid
            );
        }

        return [
            'by_jid' => $byJid,
            'by_lid' => $byLid,
            'phone_by_lid' => $phoneByLid,
            'name_by_lid' => $nameByLid,
            'name_by_jid' => $nameByJid,
        ];
    }

    private function extractChatAltJid($item)
    {
        $messages = $item->messages ?? [];
        if (!is_array($messages)) {
            return '';
        }

        foreach ($messages as $message) {
            if (!is_object($message)) {
                continue;
            }

            $messageNode = $message->message ?? null;
            if (!is_object($messageNode)) {
                continue;
            }

            $key = $messageNode->key ?? null;
            if (is_object($key)) {
                $altJid = trim((string) ($key->remoteJidAlt ?? ''));
                if ($altJid !== '') {
                    return $altJid;
                }
            }
        }

        return '';
    }

    private function resolveChatContactFromStore($indexes, $rawJid, $pnJid, $altJid, $accountLid = '')
    {
        $byJid = $indexes['by_jid'] ?? [];
        $byLid = $indexes['by_lid'] ?? [];
        $phoneByLid = $indexes['phone_by_lid'] ?? [];
        $nameByLid = $indexes['name_by_lid'] ?? [];
        $nameByJid = $indexes['name_by_jid'] ?? [];

        $contact = $byJid[$pnJid] ?? $byJid[$altJid] ?? $byLid[$rawJid] ?? $byLid[$accountLid] ?? [];
        $resolvedPhoneJid = $this->pickPhoneJid([
            $contact['id'] ?? '',
            $pnJid,
            $altJid,
            $phoneByLid[$rawJid] ?? '',
            $phoneByLid[$accountLid] ?? '',
        ]);
        $resolvedName = $this->pickChatDisplayName([
            $contact['name'] ?? '',
            $nameByLid[$rawJid] ?? '',
            $nameByLid[$accountLid] ?? '',
            $nameByJid[$resolvedPhoneJid] ?? '',
        ], $this->cleanJid($resolvedPhoneJid), $rawJid !== '' ? $rawJid : $accountLid);

        if ($resolvedPhoneJid !== '') {
            $contact['id'] = $resolvedPhoneJid;
        }
        if ($resolvedName !== '') {
            $contact['name'] = $resolvedName;
        }

        return $contact;
    }

    private function buildResolvedChatData(array $contactIndexes, array $chatData)
    {
        $rawJid = trim((string) ($chatData['raw_jid'] ?? ''));
        $pnJid = trim((string) ($chatData['pn_jid'] ?? ''));
        $altJid = trim((string) ($chatData['alt_jid'] ?? ''));
        $accountLid = trim((string) ($chatData['account_lid'] ?? ''));
        $labels = is_array($chatData['labels'] ?? null) ? $chatData['labels'] : [];
        $contact = $this->resolveChatContactFromStore($contactIndexes, $rawJid, $pnJid, $altJid, $accountLid);
        $resolvedPhoneJid = $this->pickPhoneJid([
            $pnJid,
            $altJid,
            $contact['id'] ?? '',
            $contactIndexes['phone_by_lid'][$rawJid] ?? '',
            $contactIndexes['phone_by_lid'][$accountLid] ?? '',
            $rawJid,
        ]);

        $resolvedNumber = $this->cleanJid($resolvedPhoneJid);
        if ($resolvedNumber === '') {
            $resolvedNumber = $this->extractPhoneDigitsFromCandidates([
                $contact['name'] ?? '',
                $contactIndexes['name_by_lid'][$rawJid] ?? '',
                $contactIndexes['name_by_lid'][$accountLid] ?? '',
                $contactIndexes['name_by_jid'][$resolvedPhoneJid] ?? '',
                ...$labels,
            ], $rawJid);
        }

        $displayName = $this->pickChatDisplayName([
            $contact['name'] ?? '',
            $contactIndexes['name_by_lid'][$rawJid] ?? '',
            $contactIndexes['name_by_lid'][$accountLid] ?? '',
            $contactIndexes['name_by_jid'][$resolvedPhoneJid] ?? '',
            ...$labels,
        ], $resolvedNumber, $rawJid);

        return [
            'number' => $resolvedNumber,
            'jid' => $rawJid !== '' ? $rawJid : ($resolvedPhoneJid !== '' ? $resolvedPhoneJid : $altJid),
            'raw_jid' => $rawJid,
            'pn_jid' => $resolvedPhoneJid,
            'alt_jid' => $altJid,
            'display_name' => $displayName !== '' ? $displayName : ($resolvedNumber !== '' ? '+' . $resolvedNumber : ''),
            'unread' => (int) ($chatData['unread'] ?? 0),
            'timestamp' => $this->normalizeTimestampValue($chatData['timestamp'] ?? 0),
            'latest_message' => trim((string) ($chatData['latest_message'] ?? '')),
        ];
    }

    private function pickPhoneJid(array $candidates)
    {
        foreach ($candidates as $candidate) {
            $jid = trim((string) $candidate);
            if ($jid !== '' && str_ends_with($jid, '@s.whatsapp.net')) {
                return $jid;
            }
        }

        return '';
    }

    private function getPrimaryStoreResolvedChats($device_id, array $contactIndexes)
    {
        $storePath = $this->resolveDeviceStorePath($device_id);
        $payload = $this->loadStorePayload($storePath);
        if (!is_array($payload)) {
            return [];
        }

        $resolvedChats = [];

        foreach ($payload['chats'] ?? [] as $entry) {
            if (!is_array($entry) || count($entry) < 2 || !is_array($entry[1])) {
                continue;
            }

            $chat = $entry[1];
            $rawJid = trim((string) ($chat['id'] ?? $entry[0] ?? ''));
            $pnJid = trim((string) ($chat['pnJid'] ?? ''));

            if (
                $rawJid === '' ||
                (
                    !str_ends_with($rawJid, '@lid') &&
                    !str_ends_with($rawJid, '@s.whatsapp.net') &&
                    !str_ends_with($pnJid, '@s.whatsapp.net')
                )
            ) {
                continue;
            }

            $resolvedChats[] = $this->buildResolvedChatData($contactIndexes, [
                'raw_jid' => $rawJid,
                'pn_jid' => $pnJid,
                'alt_jid' => $this->extractStoreChatAltJid($chat),
                'account_lid' => $chat['accountLid'] ?? '',
                'labels' => [
                    $chat['contactName'] ?? '',
                    $chat['name'] ?? '',
                    $chat['notify'] ?? '',
                    $chat['displayName'] ?? '',
                ],
                'unread' => $chat['unreadCount'] ?? 0,
                'timestamp' => $chat['conversationTimestamp'] ?? 0,
                'latest_message' => $this->extractLatestMessagePreview($chat['messages'] ?? []),
            ]);
        }

        return $resolvedChats;
    }

    private function mergeResolvedChats(array $baseChats, array $extraChats)
    {
        $merged = [];

        foreach (array_merge($baseChats, $extraChats) as $chat) {
            if (!is_array($chat)) {
                continue;
            }

            $key = $this->chatMergeKey($chat);
            if ($key === '') {
                continue;
            }

            if (!isset($merged[$key])) {
                $merged[$key] = $chat;
                continue;
            }

            $existing = $merged[$key];
            $existingTimestamp = (int) ($existing['timestamp'] ?? 0);
            $incomingTimestamp = (int) ($chat['timestamp'] ?? 0);
            $existingLatestMessage = trim((string) ($existing['latest_message'] ?? ''));
            $incomingLatestMessage = trim((string) ($chat['latest_message'] ?? ''));
            $newest = $incomingTimestamp > $existingTimestamp ? $chat : $existing;
            $oldest = $incomingTimestamp > $existingTimestamp ? $existing : $chat;
            $merged[$key] = [
                'number' => ($existing['number'] ?? '') !== '' ? $existing['number'] : ($chat['number'] ?? ''),
                'jid' => ($newest['jid'] ?? '') !== '' ? $newest['jid'] : ($oldest['jid'] ?? ''),
                'raw_jid' => ($newest['raw_jid'] ?? '') !== '' ? $newest['raw_jid'] : ($oldest['raw_jid'] ?? ''),
                'pn_jid' => ($existing['pn_jid'] ?? '') !== '' ? $existing['pn_jid'] : ($chat['pn_jid'] ?? ''),
                'alt_jid' => ($existing['alt_jid'] ?? '') !== '' ? $existing['alt_jid'] : ($chat['alt_jid'] ?? ''),
                'display_name' => ($existing['display_name'] ?? '') !== '' ? $existing['display_name'] : ($chat['display_name'] ?? ''),
                'unread' => max((int) ($existing['unread'] ?? 0), (int) ($chat['unread'] ?? 0)),
                'timestamp' => max($existingTimestamp, $incomingTimestamp),
                'latest_message' => $incomingTimestamp > $existingTimestamp
                    ? ($incomingLatestMessage !== '' ? $incomingLatestMessage : $existingLatestMessage)
                    : ($existingLatestMessage !== '' ? $existingLatestMessage : $incomingLatestMessage),
            ];
        }

        return array_values($merged);
    }

    private function chatMergeKey(array $chat)
    {
        $number = preg_replace('/\D+/', '', (string) ($chat['number'] ?? ''));
        if ($number !== '') {
            return 'number:' . $number;
        }

        foreach (['pn_jid', 'jid', 'raw_jid', 'alt_jid'] as $field) {
            $jid = trim((string) ($chat[$field] ?? ''));
            if ($jid !== '') {
                return 'jid:' . $jid;
            }
        }

        return '';
    }

    private function candidateStorePaths($primaryStorePath)
    {
        $primaryStorePath = (string) $primaryStorePath;
        $paths = [];

        if ($primaryStorePath !== '') {
            $paths[] = $primaryStorePath;
        }

        foreach (glob(base_path('sessions/device_*_store.json')) ?: [] as $path) {
            $path = (string) $path;
            if ($path === '' || $path === $primaryStorePath) {
                continue;
            }

            $paths[] = $path;
        }

        return $paths;
    }

    private function loadStorePayload($storePath)
    {
        if (!is_string($storePath) || $storePath === '' || !is_file($storePath)) {
            return null;
        }

        $json = @file_get_contents($storePath);
        if ($json === false || $json === '') {
            return null;
        }

        $payload = json_decode($json, true);
        return is_array($payload) ? $payload : null;
    }

    private function extractStoreChatAltJid(array $chat)
    {
        foreach ($chat['messages'] ?? [] as $messageEntry) {
            $message = is_array($messageEntry['message'] ?? null) ? $messageEntry['message'] : [];
            $key = is_array($message['key'] ?? null) ? $message['key'] : [];
            $altJid = trim((string) ($key['remoteJidAlt'] ?? ''));
            if ($altJid !== '') {
                return $altJid;
            }
        }

        return '';
    }

    private function mergeStorePayloadIntoIndexes(array $payload, array &$byJid, array &$byLid, array &$phoneByLid, array &$nameByLid, array &$nameByJid)
    {
        foreach ($payload['contacts'] ?? [] as $entry) {
            if (!is_array($entry) || count($entry) < 2 || !is_array($entry[1])) {
                continue;
            }

            $contact = $entry[1];
            $jid = trim((string) ($contact['id'] ?? $entry[0] ?? ''));
            $lid = trim((string) ($contact['lid'] ?? ''));

            if ($jid !== '' && empty($byJid[$jid])) {
                $byJid[$jid] = $contact;
            }

            if ($lid !== '' && empty($byLid[$lid])) {
                $byLid[$lid] = $contact;
            }

            if ($lid !== '' && str_ends_with($jid, '@s.whatsapp.net') && empty($phoneByLid[$lid])) {
                $phoneByLid[$lid] = $jid;
            }

            $contactName = $this->normalizeChatLabel(
                $contact['name'] ?? '',
                $this->cleanJid($jid),
                $lid
            );
            if ($contactName === '') {
                continue;
            }

            if ($jid !== '' && empty($nameByJid[$jid])) {
                $nameByJid[$jid] = $contactName;
            }
            if ($lid !== '' && empty($nameByLid[$lid])) {
                $nameByLid[$lid] = $contactName;
            }
        }

        foreach ($payload['chats'] ?? [] as $entry) {
            if (!is_array($entry) || count($entry) < 2 || !is_array($entry[1])) {
                continue;
            }

            $chat = $entry[1];
            $rawJid = trim((string) ($chat['id'] ?? $entry[0] ?? ''));
            $accountLid = trim((string) ($chat['accountLid'] ?? ''));
            $chatLabel = $this->normalizeChatLabel(
                $chat['name'] ?? '',
                $this->cleanJid($phoneByLid[$rawJid] ?? $phoneByLid[$accountLid] ?? ''),
                $rawJid !== '' ? $rawJid : $accountLid
            );

            foreach ($chat['messages'] ?? [] as $messageEntry) {
                $messageData = is_array($messageEntry) ? ($messageEntry['message'] ?? $messageEntry) : [];
                if (!is_array($messageData)) {
                    continue;
                }

                $this->collectStoreMessageSignals(
                    $messageData,
                    [$rawJid, $accountLid],
                    $phoneByLid,
                    $nameByLid,
                    $nameByJid,
                    $chatLabel
                );
            }
        }

        foreach ($payload['messages'] ?? [] as $chatKey => $entries) {
            if (!is_array($entries)) {
                continue;
            }

            foreach ($entries as $entry) {
                if (!is_array($entry) || count($entry) < 2 || !is_array($entry[1])) {
                    continue;
                }

                $this->collectStoreMessageSignals(
                    $entry[1],
                    [is_string($chatKey) ? $chatKey : ''],
                    $phoneByLid,
                    $nameByLid,
                    $nameByJid
                );
            }
        }
    }

    private function collectStoreMessageSignals(array $message, array $baseLids, array &$phoneByLid, array &$nameByLid, array &$nameByJid, $chatLabel = '')
    {
        $key = is_array($message['key'] ?? null) ? $message['key'] : [];
        $candidatePhoneJid = $this->pickPhoneJid([
            $key['remoteJidAlt'] ?? '',
            $key['participantAlt'] ?? '',
            $key['remoteJid'] ?? '',
            $key['participant'] ?? '',
        ]);

        $candidateLids = array_filter(array_unique(array_map('trim', array_filter([
            ...$baseLids,
            (string) ($key['remoteJid'] ?? ''),
            (string) ($key['participant'] ?? ''),
        ]))));

        foreach ($candidateLids as $lid) {
            if (!str_ends_with($lid, '@lid')) {
                continue;
            }

            if ($candidatePhoneJid !== '' && empty($phoneByLid[$lid])) {
                $phoneByLid[$lid] = $candidatePhoneJid;
            }
        }

        $fromMe = (bool) ($key['fromMe'] ?? false);
        $candidateName = $this->pickChatDisplayName([
            $chatLabel,
            !$fromMe ? ($message['pushName'] ?? '') : '',
            !$fromMe ? ($message['verifiedBizName'] ?? '') : '',
        ], $this->cleanJid($candidatePhoneJid), $candidateLids[0] ?? '');

        if ($candidateName === '') {
            return;
        }

        if ($candidatePhoneJid !== '' && empty($nameByJid[$candidatePhoneJid])) {
            $nameByJid[$candidatePhoneJid] = $candidateName;
        }

        foreach ($candidateLids as $lid) {
            if (str_ends_with($lid, '@lid') && empty($nameByLid[$lid])) {
                $nameByLid[$lid] = $candidateName;
            }
        }
    }

    private function pickChatDisplayName(array $candidates, $number = '', $rawJid = '')
    {
        foreach ($candidates as $candidate) {
            $label = $this->normalizeChatLabel($candidate, $number, $rawJid);
            if ($label !== '') {
                return $label;
            }
        }

        return '';
    }

    private function normalizeChatLabel($value, $number = '', $rawJid = '')
    {
        $label = trim((string) $value);
        if ($label === '' || $label === '.' || $label === 'WhatsApp Contact' || $label === 'Unknown Contact') {
            return '';
        }

        $normalizedNumber = preg_replace('/\D+/', '', (string) $number);
        $labelDigits = preg_replace('/\D+/', '', $label);
        $rawDigits = preg_replace('/\D+/', '', (string) $rawJid);

        if ($normalizedNumber !== '' && $labelDigits === $normalizedNumber) {
            return '';
        }

        if ($rawDigits !== '' && $labelDigits === $rawDigits) {
            return '';
        }

        return $label;
    }

    private function extractPhoneDigitsFromCandidates(array $candidates, $rawJid = '')
    {
        $rawDigits = preg_replace('/\D+/', '', (string) $rawJid);

        foreach ($candidates as $candidate) {
            $digits = preg_replace('/\D+/', '', trim((string) $candidate));
            if ($digits === '') {
                continue;
            }

            if ($rawDigits !== '' && $digits === $rawDigits) {
                continue;
            }

            if (strlen($digits) < 10 || strlen($digits) > 15) {
                continue;
            }

            return $digits;
        }

        return '';
    }

    public function getGroupList($device_id)
    {
        $session_id = $this->resolveDeviceSessionId($device_id);
        $whatsServer = $this->whatsappServerUrl();

        $response=Http::get($whatsServer.'/groups?id='.$session_id);
        $status=$response->status();

        if ($status != 200) {
            $responseBody=json_decode($response->body());
            $responseData['message']=$responseBody->message;
            $responseData['status']=$status;
        }
        else{
            $responseBody  =json_decode($response->body());
            $colllections  = collect($responseBody->data);

            $contacts = $colllections->map(function($item) {

                             $data['name'] = $item->name;
                             $data['id'] = $item->id;
                             return $data;
                        });

            $responseData['status'] = 200;
            $responseData['data'] = $contacts;
        }

        return $responseData;
    }

    private function sendMessageToGroup($data,$from, $reciver,$type,$filter=false,$delay = 0)
    {
       $delay = $delay == 0 ? env('DELAY_TIME',1000) : $delay;

        if ($delay < 500) {
            $delay = 1;
        }
        else{
           $delay =  $delay/1000;
           $delay = round($delay);
        }

        sleep($delay);

        $device=Device::where('id',$from)->first();
        if (empty($device)) {
            return false;
        }

        //creating session id
        $session_id = $device->whatsappSessionId();

        //formating message
        $message=$this->formatBody($data['message'] ?? '',$device->user_id);

        //formating array context
        $formatedBody= $filter == false ? $this->formatArray($data,$message,$type) : $data;

        //get server url
        $whatsServer = $this->whatsappServerUrl();

        //formating array before sending data to server

        $body['receiver']=$reciver;
        $body['isGroup']=true;
        $body['message']=$formatedBody;

        //sending data to whatsapp server
       try {
            $response=Http::post($whatsServer.'/chats/send?id='.$session_id,$body);
            $status=$response->status();



            if ($status != 200) {
                $responseBody=json_decode($response->body());
                $responseData['message']=$responseBody->message;
                $responseData['status']=$status;
            }
            else{
                $responseData['status'] = 200;
            }

            return $responseData;
       } catch (\Throwable $e) {
           $responseData['status'] = 403;
           $responseData['message'] = $e->getMessage();
           return $responseData;
       }

    }

    private function formatArray($data,$message,$type)
    {

        if ($type == 'plain-text') {
            $content['text']=$message;
        }
        elseif ($type == 'text-with-media') {
            $content['caption']=$message;
            $explode=explode('.', $data['attachment']);
            $file_type=strtolower(end($explode));
            $extentions=[
                'jpg'=>'image',
                'jpeg'=>'image',
                'png'=>'image',
                'webp'=>'image',
                'gif'=>'image',
                'pdf'=>'document',
                'doc'=>'document',
                'docx'=>'document',
                'xls'=>'document',
                'xlsx'=>'document',
                'csv'=>'document',
                'txt'=>'document',
                'zip'=>'document',
                'ppt'=>'document',
                'pptx'=>'document',
                'mp4'=>'video',
                'mov'=>'video',
                'avi'=>'video',
                'mkv'=>'video',
                'mp3'=>'audio',
                'wav'=>'audio',
                'ogg'=>'audio',
                'aac'=>'audio',
                'webm'=>'video',
            ];

            $mediaType = $extentions[$file_type] ?? 'document';
            $attachment = (string) ($data['attachment'] ?? '');
            $attachmentForNode = $this->resolveAttachmentForNode($attachment);

            // saveFile() already returns an absolute URL; avoid wrapping again with asset()
            // because that can produce an invalid URL like /http://...
            $content[$mediaType]=['url' => $attachmentForNode];

            if ($mediaType === 'document') {
                $customFileName = trim((string) ($data['attachment_name'] ?? ''));
                $content['fileName'] = $customFileName !== ''
                    ? $customFileName
                    : basename(parse_url($attachment, PHP_URL_PATH) ?: $attachment);
                $mimeTypes = [
                    'pdf' => 'application/pdf',
                    'doc' => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'xls' => 'application/vnd.ms-excel',
                    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'csv' => 'text/csv',
                    'txt' => 'text/plain',
                    'zip' => 'application/zip',
                    'ppt' => 'application/vnd.ms-powerpoint',
                    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                ];
                $content['mimetype'] = $mimeTypes[$file_type] ?? 'application/octet-stream';
            }

        }
        elseif ($type == 'text-with-button') {
            $buttons=[];
            foreach ($data['buttons'] as $key => $button) {
                $button_content['buttonId']='id'.$key;
                $button_content['buttonText']= array('displayText' => $button);
                $button_content['type']=1;

                array_push($buttons, $button_content);
            }


           $content['text']=$message;
           $content['footer']=$data['footer_text'];
           $content['buttons']=$buttons;
           $content['headerType']=1;
        }

        elseif ($type == 'text-with-template') {
             $templateButtons=[];
            foreach ($data['buttons'] as $key => $button) {
                $button_type='';
                $button_action_content='';

                if ($button['type'] == 'urlButton') {
                    $button_type='url';
                    $button_action_content=$button['action'];
                }
                elseif ($button['type'] == 'callButton') {
                    $button_type='phoneNumber';
                    $button_action_content=$button['action'];
                }
                else{
                    $button_type='id';
                    $button_action_content='action-id-'.$key;
                }

                $button_actions=[];
                $button_actions['displayText']=$button['displaytext'];
                $button_actions[$button_type]=$button_action_content;



                $button_context['index']=$key;
                $button_context[$button['type']]= $button_actions;

                array_push($templateButtons, $button_context);
                $button_context=null;

            }


           $content['text']=$message;
           $content['footer']=$data['footer_text'];
           $content['templateButtons']=$templateButtons;

        }
        elseif ($type == 'text-with-location') {
            $content['location']=array(
                'degreesLatitude'=>$data['degreesLatitude'],
                'degreesLongitude'=>$data['degreesLongitude']
            );
        }
        elseif ($type == 'text-with-vcard') {
            $vcard='BEGIN:VCARD\n' // metadata of the contact card
            . 'VERSION:3.0\n'
            . 'FN:'.$data['full_name'].'\n' // full name
            . 'ORG:'.$data['org_name'].';\n' // the organization of the contact
            . 'TEL;type=CELL;type=VOICE;waid='.$data['contact_number'].':'.$data['wa_number'].'\n' // WhatsApp ID + phone number
            . 'END:VCARD';


            $content = [
             "contacts" => [
               "displayName" => "maruf",
               "contacts" => [[$vcard]]
             ]
            ];
        }
        elseif ($type == 'text-with-list') {

            $templateButtons=[];

            foreach ($data['section'] as $section_key => $sections) {

               $rows=[];

               foreach ($sections['value'] as $value_key => $value) {

                   $rowArr['title']=$value['title'];
                   $rowArr['rowId']='option-'.$section_key.'-'.$value_key;

                   if ($value['description'] != null) {
                       $rowArr['description']=$value['description'];
                   }
                   array_push($rows, $rowArr);
                   $rowArr=[];
               }

               $row['title']=$sections['title'];
               $row['rows']=$rows;


              array_push($templateButtons, $row);
              $row=[];
            }

             $content = [
               "text" => $message,
               "footer" =>  $data['footer_text'],
               "title" => $data['header_title'],
               "buttonText" =>$data['button_text'],
               "sections" => $templateButtons
            ];


        }


        return $content;
    }

    private function saveTemplate($data,$message,$type,$user_id,$template_id=null)
    {
       if ($template_id == null) {
          $template= new Template;
       }
       else{
          $template=  Template::findorFail($template_id);
          $template->status=isset($data['status']) ? 1 : 0;
       }

       $template->title=$data['template_name'];
       $template->user_id=$user_id;
       $template->body=$this->formatArray($data,$message,$type);
       $template->type=$type;
       $template->save();

       return true;
    }

    private function saveFile(Request $request,$input)
    {
        \Log::info('----saveFile() ---- ');
        if ($request->hasFile($input)) {
            $file = $request->file($input);
            $filename = time() . '.' . $file->getClientOriginalExtension();

            $relativePath = 'uploads/message/' . \Auth::id() . '/' . date('y') . '/' . date('m');
            $fullPath = public_path($relativePath);
            File::makeDirectory($fullPath, 0775, true, true);
            $file->move($fullPath, $filename);

            return url('public/' . $relativePath . '/' . $filename);
        }

        $existingFile = $request->input($input);
        if (!empty($existingFile)) {
            $sourcePath = $this->resolveSafeStoredAttachmentPath($existingFile);
            if ($sourcePath === null) {
                return null;
            }

            $attachmentName = trim((string) $request->input('attachment_name', ''));
            if ($attachmentName !== '') {
                $safeFileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $attachmentName);
                $safeFileName = ltrim((string) $safeFileName, '.');
                $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
                if ($extension !== '' && !str_ends_with(strtolower($safeFileName), '.' . strtolower($extension))) {
                    $safeFileName .= '.' . $extension;
                }

                $relativePath = 'uploads/message/' . \Auth::id() . '/' . date('y') . '/' . date('m');
                $fullPath = public_path($relativePath);
                File::makeDirectory($fullPath, 0775, true, true);

                $targetPath = $fullPath . DIRECTORY_SEPARATOR . $safeFileName;
                File::copy($sourcePath, $targetPath);

                return $targetPath;
            }

            // Keep valid server-generated attachment references working, but never trust raw client paths.
            return $sourcePath;
        }

        return null;

        /*
        $file = $request->file($input);
        $filename = time().'.'.$file->getClientOriginalExtension();

        $relativePath = 'uploads/message/' . \Auth::id() . '/' . date('y') . '/' . date('m');
        $fullPath = public_path($relativePath);

        File::makeDirectory($fullPath, 0775, true, true);
        $file->move($fullPath, $filename);

        return url('public/'.$relativePath.'/'.$filename);
        */



    }

    private function formatBody($context='', $user_id)
    {
        if ($context == '') {
            return $context;
        }

        $user=User::where('id',$user_id)->first();

        if (empty($user)) {
           return $context;
        }
        else{
           return $context;
        }
    }


    private function groupMetaData($group_id, $device_id)
    {
        $whatsServer = $this->whatsappServerUrl();
        $sessionId = $this->resolveDeviceSessionId($device_id);
        $url = $whatsServer.'/groups/meta/'.$group_id.'?id='.$sessionId;

         try {

            $response=Http::get($url);
            $status=$response->status();

            if ($status != 200) {
                $responseBody=json_decode($response->body());
                $responseData['message']=$responseBody->message;
                $responseData['status']=$status;
            }
            else{
                $responseData['status'] = 200;
                $responseData['data']=json_decode($response->body());

            }

            return $responseData;
       } catch (\Throwable $e) {
           $responseData['status'] = 403;
           $responseData['message'] = $e->getMessage();
           return $responseData;
       }
    }

    private function formatText($context='', $contact_data = null,$senderdata = null)
    {
       if ($context == '') {
            return $context;
       }
       if ($contact_data != null) {
           $name=$contact_data['name'] ?? '';
           $phone=$contact_data['phone'] ?? '';

           $context=str_replace('{name}',$name,$context);
           $context=str_replace('{phone_number}',$phone,$context);

       }

       if ($senderdata != null) {
           $sender_name=$senderdata['name'] ?? '';
           $sender_phone=$senderdata['phone'] ?? '';
           $sender_email=$senderdata['email'] ?? '';

           $context=str_replace('{my_name}',$sender_name,$context);
           $context=str_replace('{my_contact_number}',$sender_phone,$context);
           $context=str_replace('{my_email}',$sender_email,$context);
       }

       return $context;


    }

    private function formatCustomText($context='', $replaceableData = [])
    {
        $filteredContent = $context;

        foreach ($replaceableData ?? [] as $key => $value) {
           $filteredContent = str_replace($key, $value, $filteredContent);
        }

        return $filteredContent;

    }

    private function saveLog($data)
    {
        // $log= new Smstransaction;
        // $log->user_id = $data['user_id'] ?? null;
        // $log->device_id = $data['device_id'] ?? null;
        // $log->app_id = $data['app_id'] ?? null;
        // $log->from = $data['from'] ?? null;
        // $log->to = $data['to'] ?? null;
        // $log->template_id = $data['template_id'] ?? null;
        // $log->type = $data['type'] ?? null;
        // $log->save();
    }

}
