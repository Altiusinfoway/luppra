<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAccess();

        if ($request->ajax()) {
            return $this->dataTable($request);
        }

        return view('activity.index', [
            'activityLogUsers' => $this->activityUsers(),
            'activityLogModules' => $this->distinctValues('module'),
            'activityLogActions' => $this->distinctValues('action'),
        ]);
    }

    protected function dataTable(Request $request)
    {
        $query = $this->activityQuery($request)->with('user:id,name');

        return DataTables::eloquent($query)
            ->addColumn('row_id', function (ActivityLog $activity) {
                return '#' . $activity->id;
            })
            ->editColumn('created_at', function (ActivityLog $activity) {
                return optional($activity->created_at)->format('d M Y, h:i A');
            })
            ->addColumn('created_at_full', function (ActivityLog $activity) {
                return optional($activity->created_at)->format('d M Y, h:i:s A');
            })
            ->addColumn('user_name', function (ActivityLog $activity) {
                return optional($activity->user)->name ?: 'System';
            })
            ->addColumn('who_when_html', function (ActivityLog $activity) {
                $userName = optional($activity->user)->name ?: 'System';
                $timestamp = optional($activity->created_at)->format('d M Y, h:i A') ?: '-';
                $sourceLabel = $this->detectSourceLabel($activity->user_agent);
                $sourceBadge = $this->badge($sourceLabel, $this->sourceBadgeTheme($sourceLabel));

                return '<div class="d-flex flex-column">'
                    . '<span class="fw-semibold text-body">' . e($userName) . '</span>'
                    . '<div class="d-flex flex-wrap align-items-center gap-1">'
                    . '<span class="text-muted small">' . e($timestamp) . '</span>'
                    . $sourceBadge
                    . '</div>'
                    . '</div>';
            })
            ->addColumn('module_label', function (ActivityLog $activity) {
                return $this->badge($activity->module, 'secondary');
            })
            ->addColumn('action_label', function (ActivityLog $activity) {
                return $this->badge($activity->action, 'info');
            })
            ->addColumn('event_key_display', function (ActivityLog $activity) {
                return $activity->event_key ?: '-';
            })
            ->addColumn('description_text', function (ActivityLog $activity) {
                return $activity->description ?: $this->fallbackDescription($activity);
            })
            ->addColumn('activity_html', function (ActivityLog $activity) {
                $description = $activity->description ?: $this->fallbackDescription($activity);
                $badges = $this->badge($activity->module, 'secondary') . ' ' . $this->badge($activity->action, 'info');

                if ($activity->event_key) {
                    $badges .= ' <span class="badge bg-light text-muted border">' . e($activity->event_key) . '</span>';
                }

                return '<div class="d-flex flex-column gap-1">'
                    . '<div class="fw-semibold text-body">' . e($description) . '</div>'
                    . '<div class="d-flex flex-wrap gap-1">' . $badges . '</div>'
                    . '</div>';
            })
            ->addColumn('subject_label', function (ActivityLog $activity) {
                return $this->recordLabel($activity->subject_type, $activity->subject_id);
            })
            ->addColumn('reference_label', function (ActivityLog $activity) {
                return $this->recordLabel($activity->reference_type, $activity->reference_id);
            })
            ->addColumn('record_html', function (ActivityLog $activity) {
                $subject = $this->recordLabel($activity->subject_type, $activity->subject_id);
                $reference = $this->recordLabel($activity->reference_type, $activity->reference_id);
                $html = '<div class="d-flex flex-column">';
                $html .= '<span class="fw-semibold text-body">' . e($subject) . '</span>';

                if ($reference !== '-') {
                    $html .= '<span class="text-muted small">Related to: ' . e($reference) . '</span>';
                }

                $html .= '</div>';

                return $html;
            })
            ->addColumn('ip_address_display', function (ActivityLog $activity) {
                return $activity->ip_address ?: '-';
            })
            ->addColumn('source_label', function (ActivityLog $activity) {
                return $this->detectSourceLabel($activity->user_agent);
            })
            ->addColumn('changes_preview_html', function (ActivityLog $activity) {
                return $this->renderPropertiesPreviewHtml($activity->properties);
            })
            ->addColumn('properties_html', function (ActivityLog $activity) {
                return $this->renderPropertiesHtml($activity->properties);
            })
            ->addColumn('action_buttons', function () {
                return '<button type="button" class="btn btn-sm btn-soft-primary js-view-activity" data-bs-toggle="modal" data-bs-target="#activityDetailsModal">View Details</button>';
            })
            ->rawColumns([
                'who_when_html',
                'module_label',
                'action_label',
                'activity_html',
                'record_html',
                'changes_preview_html',
                'properties_html',
                'action_buttons',
            ])
            ->make(true);
    }

    protected function authorizeAccess(): void
    {
        $user = Auth::user();
        $isAdminUser = $user && in_array((string) $user->type, ['company', 'super admin'], true);

        if ($isAdminUser || ($user && $user->can('view activity logs'))) {
            return;
        }

        abort(403, 'Permission denied.');
    }

    protected function activityQuery(Request $request): Builder
    {
        $query = ActivityLog::query()->latestFirst();

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->input('date_from'))->startOfDay());
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->input('date_to'))->endOfDay());
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        }

        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('event_key')) {
            $query->where('event_key', 'like', '%' . trim((string) $request->input('event_key')) . '%');
        }

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->input('keyword'));

            $query->where(function (Builder $searchQuery) use ($keyword) {
                $searchQuery->where('description', 'like', '%' . $keyword . '%')
                    ->orWhere('module', 'like', '%' . $keyword . '%')
                    ->orWhere('action', 'like', '%' . $keyword . '%')
                    ->orWhere('event_key', 'like', '%' . $keyword . '%')
                    ->orWhere('subject_type', 'like', '%' . $keyword . '%')
                    ->orWhere('subject_id', 'like', '%' . $keyword . '%')
                    ->orWhere('reference_type', 'like', '%' . $keyword . '%')
                    ->orWhere('reference_id', 'like', '%' . $keyword . '%')
                    ->orWhere('ip_address', 'like', '%' . $keyword . '%');
            });
        }

        return $query;
    }

    protected function activityUsers(): Collection
    {
        $userIds = ActivityLog::query()
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id')
            ->filter()
            ->values()
            ->all();

        if (empty($userIds)) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $userIds)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    protected function distinctValues(string $column): Collection
    {
        return ActivityLog::query()
            ->whereNotNull($column)
            ->select($column)
            ->distinct()
            ->orderBy($column)
            ->pluck($column);
    }

    protected function recordLabel(?string $type, $id): string
    {
        if (!$type || !$id) {
            return '-';
        }

        $label = class_exists($type) ? class_basename($type) : $type;

        return Str::headline($label) . ' #' . $id;
    }

    protected function fallbackDescription(ActivityLog $activity): string
    {
        if ($activity->event_key) {
            return Str::headline(str_replace('.', ' ', $activity->event_key));
        }

        return Str::headline($activity->action ?: 'activity logged');
    }

    protected function badge(?string $value, string $theme): string
    {
        $label = $value ? Str::headline($value) : '-';

        return '<span class="badge bg-' . e($theme) . '-subtle text-' . e($theme) . '">' . e($label) . '</span>';
    }

    protected function detectSourceLabel(?string $userAgent): string
    {
        $agent = Str::lower(trim((string) $userAgent));

        if ($agent === '') {
            return 'System';
        }

        if (Str::contains($agent, ['postmanruntime', 'postman'])) {
            return 'Postman';
        }

        if (Str::contains($agent, ['mozilla/', 'chrome/', 'safari/', 'firefox/', 'edg/'])) {
            return 'Web';
        }

        if (Str::contains($agent, ['insomnia', 'paw/', 'curl/', 'guzzlehttp', 'okhttp', 'axios', 'python-requests'])) {
            return 'API';
        }

        return 'Script';
    }

    protected function sourceBadgeTheme(string $source): string
    {
        return match ($source) {
            'Postman' => 'warning',
            'Web' => 'success',
            'API' => 'info',
            'System' => 'secondary',
            default => 'dark',
        };
    }

    protected function renderPropertiesHtml($properties): string
    {
        if (!is_array($properties) || empty($properties)) {
            return '';
        }

        $changeRows = $this->extractChangeRows($properties);
        $sections = $this->extractMetadataSections($properties);
        $html = '';

        if (!empty($changeRows)) {
            $html .= '<div class="border rounded p-3 bg-light-subtle mb-3">';
            $html .= '<h6 class="fw-semibold mb-3">Changed Fields</h6>';
            $html .= '<div class="table-responsive"><table class="table table-sm align-middle mb-0">';
            $html .= '<thead><tr><th>Field</th><th>Old Value</th><th>New Value</th></tr></thead><tbody>';

            foreach ($changeRows as $row) {
                $html .= '<tr>';
                $html .= '<td class="fw-semibold">' . e($row['field']) . '</td>';
                $html .= '<td class="text-muted">' . e($row['before']) . '</td>';
                $html .= '<td class="text-success fw-semibold">' . e($row['after']) . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table></div></div>';
        }

        foreach ($sections as $section) {
            $html .= '<div class="border rounded p-3 bg-body mb-3">';
            $html .= '<h6 class="fw-semibold mb-3">' . e($section['title']) . '</h6>';
            $html .= '<div class="table-responsive"><table class="table table-sm align-middle mb-0"><tbody>';

            foreach ($section['rows'] as $row) {
                $html .= '<tr>';
                $html .= '<th class="text-muted" style="width: 220px;">' . e($row['label']) . '</th>';
                $html .= '<td>' . e($row['value']) . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table></div></div>';
        }

        return $html;
    }

    protected function renderPropertiesPreviewHtml($properties): string
    {
        if (!is_array($properties) || empty($properties)) {
            return '<span class="text-muted">No field changes captured</span>';
        }

        $changeRows = $this->extractChangeRows($properties);
        if (empty($changeRows)) {
            $sections = $this->extractMetadataSections($properties);

            if (empty($sections)) {
                return '<span class="text-muted">No field changes captured</span>';
            }

            $firstSection = $sections[0];
            $firstRows = array_slice($firstSection['rows'], 0, 2);
            $html = '<div class="d-flex flex-column gap-1">';

            foreach ($firstRows as $row) {
                $html .= '<div class="small text-muted">'
                    . '<span class="fw-semibold text-body">' . e($row['label']) . ':</span> '
                    . e($row['value'])
                    . '</div>';
            }

            if (count($firstSection['rows']) > 2 || count($sections) > 1) {
                $html .= '<span class="small text-primary">More details in view</span>';
            }

            $html .= '</div>';

            return $html;
        }

        $previewRows = array_slice($changeRows, 0, 3);
        $html = '<div class="d-flex flex-column gap-1">';

        foreach ($previewRows as $row) {
            $html .= '<div class="small text-muted">'
                . '<span class="fw-semibold text-body">' . e($row['field']) . ':</span> '
                . e($row['before']) . ' <span class="mx-1">&rarr;</span> ' . e($row['after'])
                . '</div>';
        }

        if (count($changeRows) > 3) {
            $html .= '<span class="small text-primary">+' . e((string) (count($changeRows) - 3)) . ' more changes</span>';
        }

        $html .= '</div>';

        return $html;
    }

    protected function extractChangeRows(array $properties): array
    {
        $rows = [];

        if (!empty($properties['changes']) && is_array($properties['changes'])) {
            foreach ($properties['changes'] as $field => $change) {
                if (!is_array($change) || !array_key_exists('before', $change) || !array_key_exists('after', $change)) {
                    continue;
                }

                $rows[] = [
                    'field' => Str::headline((string) $field),
                    'before' => $this->stringifyValue($change['before']),
                    'after' => $this->stringifyValue($change['after']),
                ];
            }

            return $rows;
        }

        if (
            isset($properties['before'], $properties['after']) &&
            is_array($properties['before']) &&
            is_array($properties['after'])
        ) {
            $keys = array_unique(array_merge(array_keys($properties['before']), array_keys($properties['after'])));

            foreach ($keys as $field) {
                $rows[] = [
                    'field' => Str::headline((string) $field),
                    'before' => $this->stringifyValue($properties['before'][$field] ?? null),
                    'after' => $this->stringifyValue($properties['after'][$field] ?? null),
                ];
            }

            return $rows;
        }

        foreach ($properties as $field => $value) {
            if (!is_array($value) || !array_key_exists('before', $value) || !array_key_exists('after', $value)) {
                continue;
            }

            $rows[] = [
                'field' => Str::headline((string) $field),
                'before' => $this->stringifyValue($value['before']),
                'after' => $this->stringifyValue($value['after']),
            ];
        }

        return $rows;
    }

    protected function extractMetadataSections(array $properties): array
    {
        $sections = [];

        foreach ($properties as $key => $value) {
            if (in_array($key, ['changes', 'before', 'after'], true)) {
                continue;
            }

            if (is_array($value) && array_key_exists('before', $value) && array_key_exists('after', $value)) {
                continue;
            }

            $rows = [];

            if (is_array($value)) {
                foreach ($value as $nestedKey => $nestedValue) {
                    $rows[] = [
                        'label' => Str::headline((string) $nestedKey),
                        'value' => $this->stringifyValue($nestedValue),
                    ];
                }
            } else {
                $rows[] = [
                    'label' => Str::headline((string) $key),
                    'value' => $this->stringifyValue($value),
                ];
            }

            if (!empty($rows)) {
                $sections[] = [
                    'title' => Str::headline((string) $key),
                    'rows' => $rows,
                ];
            }
        }

        return $sections;
    }

    protected function stringifyValue($value): string
    {
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
                return collect($value)->map(fn ($item) => $this->stringifyValue($item))->implode(', ');
            }

            return collect($value)->map(function ($item, $key) {
                return Str::headline((string) $key) . ': ' . $this->stringifyValue($item);
            })->implode(' | ');
        }

        return (string) $value;
    }
}
