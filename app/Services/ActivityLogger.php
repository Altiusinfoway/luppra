<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActivityLogger
{
    public function log(array $attributes): ?ActivityLog
    {
        $payload = $this->preparePayload($attributes);

        try {
            return ActivityLog::query()->create($payload);
        } catch (\Throwable $e) {
            Log::warning('activity_log_write_failed', [
                'module' => $payload['module'] ?? null,
                'action' => $payload['action'] ?? null,
                'event_key' => $payload['event_key'] ?? null,
                'subject_type' => $payload['subject_type'] ?? null,
                'subject_id' => $payload['subject_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function logFor(
        string $module,
        string $action,
        Model|string $subject,
        int|string|null $subjectId = null,
        array $options = []
    ): ?ActivityLog {
        [$resolvedSubjectType, $resolvedSubjectId] = ActivityLog::resolveTypeAndId($subject, $subjectId);

        $payload = [
            'module' => $module,
            'action' => $action,
            'event_key' => $options['event_key'] ?? null,
            'subject_type' => $resolvedSubjectType,
            'subject_id' => $resolvedSubjectId,
            'reference_type' => null,
            'reference_id' => null,
            'description' => $options['description'] ?? null,
            'properties' => $options['properties'] ?? null,
            'user_id' => $options['user_id'] ?? null,
            'ip_address' => $options['ip_address'] ?? null,
            'user_agent' => $options['user_agent'] ?? null,
            'created_at' => $options['created_at'] ?? null,
        ];

        if (array_key_exists('reference', $options) && $options['reference'] !== null) {
            [$payload['reference_type'], $payload['reference_id']] = ActivityLog::resolveTypeAndId(
                $options['reference'],
                $options['reference_id'] ?? null
            );
        } elseif (!empty($options['reference_type']) && array_key_exists('reference_id', $options)) {
            [$payload['reference_type'], $payload['reference_id']] = ActivityLog::resolveTypeAndId(
                $options['reference_type'],
                $options['reference_id']
            );
        }

        return $this->log($payload);
    }

    public function directHistoryQuery(Model|string $subject, int|string|null $subjectId = null): Builder
    {
        return ActivityLog::query()
            ->forSubject($subject, $subjectId)
            ->latestFirst();
    }

    public function relatedHistoryQuery(Model|string $reference, int|string|null $referenceId = null): Builder
    {
        return ActivityLog::query()
            ->forReference($reference, $referenceId)
            ->latestFirst();
    }

    public function recordHistoryQuery(Model|string $subject, int|string|null $subjectId = null): Builder
    {
        return ActivityLog::query()
            ->forRecordHistory($subject, $subjectId)
            ->latestFirst();
    }

    public function getActivityForRecord(
        Model|string $subject,
        int|string|null $subjectId = null,
        int $perPage = 15,
        string $pageName = 'activities_page'
    ): LengthAwarePaginator {
        return $this->recordHistoryQuery($subject, $subjectId)
            ->with('user:id,name')
            ->paginate($perPage, ['*'], $pageName)
            ->withQueryString();
    }

    public function moduleHistoryQuery(string|array $module, array $filters = []): Builder
    {
        $query = ActivityLog::query();
        $modules = is_array($module) ? array_values(array_filter($module)) : [$module];

        if (count($modules) === 1) {
            $query->where('module', $modules[0]);
        } elseif (!empty($modules)) {
            $query->whereIn('module', $modules);
        }

        $eventKeys = $filters['event_keys'] ?? $filters['event_key'] ?? null;
        if ($eventKeys !== null) {
            $eventKeys = is_array($eventKeys) ? array_values(array_filter($eventKeys)) : [$eventKeys];

            if (count($eventKeys) === 1) {
                $query->where('event_key', $eventKeys[0]);
            } elseif (!empty($eventKeys)) {
                $query->whereIn('event_key', $eventKeys);
            }
        }

        if (!empty($filters['subject'])) {
            [$subjectType, $subjectId] = ActivityLog::resolveTypeAndId(
                $filters['subject'],
                $filters['subject_id'] ?? null
            );

            $query->where('subject_type', $subjectType)
                ->where('subject_id', $subjectId);
        } elseif (isset($filters['subject_type'], $filters['subject_id'])) {
            [$subjectType, $subjectId] = ActivityLog::resolveTypeAndId(
                $filters['subject_type'],
                $filters['subject_id']
            );

            $query->where('subject_type', $subjectType)
                ->where('subject_id', $subjectId);
        }

        if (!empty($filters['reference'])) {
            [$referenceType, $referenceId] = ActivityLog::resolveTypeAndId(
                $filters['reference'],
                $filters['reference_id'] ?? null
            );

            $query->where('reference_type', $referenceType)
                ->where('reference_id', $referenceId);
        } elseif (isset($filters['reference_type'], $filters['reference_id'])) {
            [$referenceType, $referenceId] = ActivityLog::resolveTypeAndId(
                $filters['reference_type'],
                $filters['reference_id']
            );

            $query->where('reference_type', $referenceType)
                ->where('reference_id', $referenceId);
        }

        $propertyEquals = $filters['property_equals'] ?? [];
        foreach ($propertyEquals as $propertyKey => $propertyValue) {
            $jsonPath = 'properties->' . $propertyKey;

            if (is_array($propertyValue)) {
                $propertyValues = array_values(array_filter($propertyValue, static fn ($value) => $value !== null && $value !== ''));

                if (!empty($propertyValues)) {
                    $query->whereIn($jsonPath, $propertyValues);
                }
            } else {
                $query->where($jsonPath, $propertyValue);
            }
        }

        return $query->latestFirst();
    }

    public function getActivityForModule(
        string|array $module,
        int $perPage = 15,
        array $filters = [],
        string $pageName = 'activities_page'
    ): LengthAwarePaginator {
        return $this->moduleHistoryQuery($module, $filters)
            ->with('user:id,name')
            ->paginate($perPage, ['*'], $pageName)
            ->withQueryString();
    }

    public static function write(array $attributes): ActivityLog
    {
        return app(self::class)->log($attributes);
    }

    public static function writeFor(
        string $module,
        string $action,
        Model|string $subject,
        int|string|null $subjectId = null,
        array $options = []
    ): ?ActivityLog {
        return app(self::class)->logFor($module, $action, $subject, $subjectId, $options);
    }

    public static function directHistory(Model|string $subject, int|string|null $subjectId = null): Builder
    {
        return app(self::class)->directHistoryQuery($subject, $subjectId);
    }

    public static function relatedHistory(Model|string $reference, int|string|null $referenceId = null): Builder
    {
        return app(self::class)->relatedHistoryQuery($reference, $referenceId);
    }

    public static function recordHistory(Model|string $subject, int|string|null $subjectId = null): Builder
    {
        return app(self::class)->recordHistoryQuery($subject, $subjectId);
    }

    public static function activityForRecord(
        Model|string $subject,
        int|string|null $subjectId = null,
        int $perPage = 15,
        string $pageName = 'activities_page'
    ): LengthAwarePaginator {
        return app(self::class)->getActivityForRecord($subject, $subjectId, $perPage, $pageName);
    }

    public static function moduleHistory(string|array $module, array $filters = []): Builder
    {
        return app(self::class)->moduleHistoryQuery($module, $filters);
    }

    public static function activityForModule(
        string|array $module,
        int $perPage = 15,
        array $filters = [],
        string $pageName = 'activities_page'
    ): LengthAwarePaginator {
        return app(self::class)->getActivityForModule($module, $perPage, $filters, $pageName);
    }

    protected function preparePayload(array $attributes): array
    {
        $payload = $attributes;
        $payload['user_id'] = $payload['user_id'] ?? Auth::id();
        $payload['ip_address'] = $payload['ip_address'] ?? request()?->ip();
        $payload['user_agent'] = $payload['user_agent'] ?? $this->trimUserAgent(request()?->userAgent());

        if (array_key_exists('user_agent', $payload)) {
            $payload['user_agent'] = $this->trimUserAgent($payload['user_agent']);
        }

        return $payload;
    }

    protected function trimUserAgent(?string $userAgent): ?string
    {
        if (!$userAgent) {
            return null;
        }

        return mb_substr($userAgent, 0, 500);
    }

    public static function diff(array $before, array $after): array
    {
        $changes = [];

        foreach ($before as $key => $oldValue) {
            $newValue = $after[$key] ?? null;

            if ($oldValue === $newValue) {
                continue;
            }

            if (is_bool($oldValue) || is_bool($newValue)) {
                if ((bool) $oldValue === (bool) $newValue) {
                    continue;
                }
            } elseif (static::valuesAreEquivalent($oldValue, $newValue)) {
                continue;
            }

            $changes[$key] = [
                'before' => $oldValue,
                'after' => $newValue,
            ];
        }

        return $changes;
    }

    protected static function valuesAreEquivalent($oldValue, $newValue): bool
    {
        if (is_array($oldValue) || is_array($newValue)) {
            return static::normalizeValue($oldValue) === static::normalizeValue($newValue);
        }

        if (is_object($oldValue) || is_object($newValue)) {
            return static::normalizeValue($oldValue) === static::normalizeValue($newValue);
        }

        return (string) ($oldValue ?? '') === (string) ($newValue ?? '');
    }

    protected static function normalizeValue($value)
    {
        if (is_array($value)) {
            $normalized = [];

            foreach ($value as $key => $item) {
                $normalized[$key] = static::normalizeValue($item);
            }

            if (array_is_list($normalized)) {
                return array_values($normalized);
            }

            ksort($normalized);

            return $normalized;
        }

        if (is_object($value)) {
            return static::normalizeValue((array) $value);
        }

        if (is_bool($value)) {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        return (string) $value;
    }
}
