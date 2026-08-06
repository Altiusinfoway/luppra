@php
    $timelineActivities = $activities ?? null;
    $emptyMessage = $emptyMessage ?? 'No activity found.';
    $activityTimezone = $activityTimezone ?? 'Asia/Kolkata';
    $normalizeActivityValue = static function ($value) use (&$normalizeActivityValue) {
        if (is_array($value)) {
            $normalized = [];

            foreach ($value as $key => $item) {
                $normalized[$key] = $normalizeActivityValue($item);
            }

            if (array_is_list($normalized)) {
                return array_values($normalized);
            }

            ksort($normalized);

            return $normalized;
        }

        if (is_object($value)) {
            return $normalizeActivityValue((array) $value);
        }

        return $value;
    };

    $activityValuesMatch = static function ($before, $after) use ($normalizeActivityValue) {
        return $normalizeActivityValue($before) === $normalizeActivityValue($after);
    };

    $formatActivityValue = static function ($value) use (&$formatActivityValue) {
        if ($value === null || $value === '') {
            return '-';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            if (empty($value)) {
                return '-';
            }

            if (array_is_list($value)) {
                return collect($value)->map($formatActivityValue)->implode(', ');
            }

            return collect($value)->map(function ($item, $key) use ($formatActivityValue) {
                return ucwords(str_replace('_', ' ', (string) $key)) . ': ' . $formatActivityValue($item);
            })->implode(' | ');
        }

        if (is_object($value)) {
            return $formatActivityValue((array) $value);
        }

        return (string) $value;
    };
@endphp

@once
    <style>
        .activity-history-timeline {
            position: relative;
        }

        .activity-history-item {
            position: relative;
            padding-left: 2.75rem;
            padding-bottom: 1.5rem;
        }

        .activity-history-item:last-child {
            padding-bottom: 0;
        }

        .activity-history-item::before {
            content: "";
            position: absolute;
            left: 0.82rem;
            top: 2rem;
            bottom: -0.5rem;
            width: 2px;
            background: #e9ebec;
        }

        .activity-history-item:last-child::before {
            display: none;
        }

        .activity-history-dot {
            position: absolute;
            left: 0;
            top: 0.2rem;
            width: 1.6rem;
            height: 1.6rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .activity-history-card {
            border: 1px solid rgba(255, 255, 255, 0.82);
            border-radius: 18px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
        }

        .activity-history-changes {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #f8fafc;
            padding: 10px 12px;
            margin-top: 12px;
        }
    </style>
@endonce

@if ($timelineActivities && $timelineActivities->count())
    <div class="activity-history-timeline">
        @foreach ($timelineActivities as $activity)
            @php
                $userName = data_get($activity, 'user.name') ?: 'System';
                $action = (string) ($activity->action ?? 'update');
                $module = (string) ($activity->module ?? '');
                $eventKey = (string) ($activity->event_key ?? '');
                $description = trim((string) ($activity->description ?? ''));
                $properties = is_array($activity->properties) ? $activity->properties : [];
                $changes = $properties['changes'] ?? [];

                if (empty($description)) {
                    $description = $eventKey !== ''
                        ? ucwords(str_replace(['.', '_'], [' ', ' '], $eventKey))
                        : ucfirst(str_replace('_', ' ', $action));
                }

                if (empty($changes) && isset($properties['before'], $properties['after']) && is_array($properties['before']) && is_array($properties['after'])) {
                    $changeKeys = array_unique(array_merge(array_keys($properties['before']), array_keys($properties['after'])));

                    foreach ($changeKeys as $changeKey) {
                        $beforeValue = $properties['before'][$changeKey] ?? null;
                        $afterValue = $properties['after'][$changeKey] ?? null;

                        if ($activityValuesMatch($beforeValue, $afterValue)) {
                            continue;
                        }

                        $changes[$changeKey] = [
                            'before' => $beforeValue,
                            'after' => $afterValue,
                        ];
                    }
                }

                $actionClass = match ($action) {
                    'create' => 'bg-success-subtle text-success',
                    'assign' => 'bg-info-subtle text-info',
                    'change_status' => 'bg-warning-subtle text-warning',
                    'convert' => 'bg-primary-subtle text-primary',
                    default => 'bg-secondary-subtle text-secondary',
                };
            @endphp

            <div class="activity-history-item">
                <span class="activity-history-dot {{ $actionClass }}">
                    {{ strtoupper(substr($userName, 0, 1)) }}
                </span>

                <div class="card border shadow-none mb-0 activity-history-card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <h6 class="mb-0">{{ $userName }}</h6>
                            <span class="badge {{ $actionClass }}">{{ ucfirst(str_replace('_', ' ', $action)) }}</span>
                            @if ($module !== '')
                                <span class="badge bg-light text-muted">{{ ucfirst($module) }}</span>
                            @endif
                            <small class="text-muted ms-auto">
                                {{ optional($activity->created_at)->timezone($activityTimezone)->format('d M Y, h:i A') }}
                            </small>
                        </div>

                        <p class="text-muted mb-0">{{ $description }}</p>

                        @if (!empty($changes))
                            <div class="activity-history-changes">
                                @foreach ($changes as $field => $change)
                                    <div class="small text-muted mb-1">
                                        <span class="fw-semibold text-body">{{ ucwords(str_replace('_', ' ', $field)) }}</span>:
                                        <span>{{ $formatActivityValue(data_get($change, 'before')) }}</span>
                                        <span class="mx-1">&rarr;</span>
                                        <span>{{ $formatActivityValue(data_get($change, 'after')) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if (method_exists($timelineActivities, 'hasPages') && $timelineActivities->hasPages())
        <div class="mt-3">
            {{ $timelineActivities->onEachSide(1)->links() }}
        </div>
    @endif
@else
    <div class="text-center text-muted py-4">{{ $emptyMessage }}</div>
@endif
