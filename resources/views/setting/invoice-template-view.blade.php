@extends('layouts.app')

@section('page-css')
<style>
    .settings-suite {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
    }
    .settings-suite .hero-shell,
    .settings-suite .settings-shell {
        border: 1px solid rgba(255, 255, 255, 0.78);
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
    }
    .settings-suite .hero-shell {
        border-radius: 28px;
        background:
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
            radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
            linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
        margin-bottom: 1rem;
    }
    .settings-suite .settings-shell {
        border-radius: 22px;
    }
    .settings-suite .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.76);
        border: 1px solid #dbeafe;
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .settings-suite .hero-title {
        font-size: clamp(2rem, 3vw, 2.7rem);
        line-height: 1.05;
        letter-spacing: -0.04em;
        font-weight: 800;
        margin: 1rem 0 .45rem;
        color: #0f172a;
    }
    .settings-suite .hero-subtitle {
        color: #64748b;
    }
    .settings-suite .table-wrap {
        border: 1px solid #dce4ee;
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
    }
    .settings-suite .table-wrap table {
        margin-bottom: 0;
    }
    .settings-suite .table-wrap thead th {
        background: #f8fafc;
        color: #334155;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .06em;
        border-bottom-color: #dce4ee;
    }
    .settings-suite .section-card {
        border: 1px solid #dce4ee;
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        padding: 1rem;
        height: 100%;
    }
    .settings-suite .section-card .section-key {
        color: #64748b;
        font-size: 12px;
    }
    .settings-suite .section-card .settings-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 14px;
        background: #f8fafc;
        color: #64748b;
        font-size: 12px;
        padding: .9rem 1rem;
    }
    .settings-suite .section-settings-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
    }
    .settings-suite .status-banner {
        border: 1px solid #dce4ee;
        border-radius: 18px;
        padding: 1rem 1.15rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }
    .settings-suite .status-banner.status-info {
        background: linear-gradient(135deg, #eff6ff 0%, #f8fbff 100%);
        border-color: #bfdbfe;
        color: #1e3a8a;
    }
    .settings-suite .status-banner.status-success {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border-color: #bbf7d0;
        color: #166534;
    }
</style>
@endsection

@section('content')
<div class="page-content settings-suite">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="hero-shell">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-8">
                                <span class="hero-eyebrow">Templates</span>
                                <h1 class="hero-title">Invoice Template Details</h1>
                                <p class="hero-subtitle mb-0">Inspect template metadata, section differences, and preview configuration in the same modern template-management shell as the rest of the refreshed admin UI.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end gap-2">
                                    <a href="{{ route('setting.invoice-templates.edit', $template->id) }}" class="btn btn-sm btn-primary">Edit Template</a>
                                    <a href="{{ route('setting.invoice-templates.index') }}" class="btn btn-sm btn-outline-primary">Back</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-4 col-md-6">
                <div class="card h-100 settings-shell">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-medium fs-12 mb-2">Template</p>
                        <h4 class="mb-1">{{ $template->name }}</h4>
                        <div class="text-muted">{{ $template->code }}</div>
                        <div class="mt-3 d-flex gap-2 flex-wrap">
                            @if($template->is_default)
                                <span class="badge bg-success">Default</span>
                            @endif
                            @if($template->is_active)
                                <span class="badge bg-primary-subtle text-primary">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">Inactive</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card h-100 settings-shell">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-medium fs-12 mb-2">Document Setup</p>
                        <div class="mb-2"><strong>Paper:</strong> {{ $template->paper_size }}</div>
                        <div class="mb-2 text-capitalize"><strong>Orientation:</strong> {{ $template->orientation }}</div>
                        <div><strong>View:</strong> {{ $template->view_name }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-12">
                <div class="card h-100 settings-shell">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-medium fs-12 mb-2">Compare Baseline</p>
                        @if($defaultTemplate)
                            <div class="mb-2"><strong>Default Template:</strong> {{ $defaultTemplate->name }}</div>
                            <div class="mb-0"><strong>Differences Found:</strong> {{ count($differences) }}</div>
                        @else
                            <div class="text-muted">No default template available for comparison.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @include('setting.partials.invoice-template-preview-dompdf', [
            'template' => $template,
            'sectionMap' => $sectionMap,
            'previewData' => $previewData,
            'previewTheme' => $previewTheme,
        ])

        <div class="card mb-3 settings-shell">
            <div class="card-header">
                <h5 class="card-title mb-0">What Is Different</h5>
            </div>
            <div class="card-body">
                @if($template->is_default)
                    <div class="status-banner status-info mb-0">
                        This is the default baseline template. Other templates can be compared against this one.
                    </div>
                @elseif(empty($differences))
                    <div class="status-banner status-success mb-0">
                        No differences found compared to the current default template.
                    </div>
                @else
                    <div class="table-wrap">
                        <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 24%;">Section</th>
                                    <th style="width: 18%;">Field</th>
                                    <th style="width: 29%;">Default</th>
                                    <th style="width: 29%;">Current</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($differences as $difference)
                                    <tr>
                                        <td>{{ $difference['section'] }}</td>
                                        <td><code>{{ $difference['field'] }}</code></td>
                                        <td>{{ $difference['default'] }}</td>
                                        <td>{{ $difference['current'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card settings-shell">
            <div class="card-header">
                <h5 class="card-title mb-0">Section Configuration</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($template->sections as $section)
                        <div class="col-xl-6">
                            <div class="section-card">
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div>
                                        <h6 class="mb-1">{{ $section->sort_order }}. {{ $section->section_label }}</h6>
                                        <div class="section-key">{{ $section->section_key }}</div>
                                    </div>
                                    <span class="badge {{ $section->is_visible ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                        {{ $section->is_visible ? 'Visible' : 'Hidden' }}
                                    </span>
                                </div>

                                @php
                                    $settings = is_array($section->settings_json) ? $section->settings_json : [];
                                @endphp

                                @if(empty($settings))
                                    <div class="settings-empty">No extra settings configured for this section yet.</div>
                                @else
                                    <div class="table-wrap">
                                        <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0 section-settings-table">
                                            <thead>
                                                <tr>
                                                    <th>Setting</th>
                                                    <th>Value</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($settings as $key => $value)
                                                    <tr>
                                                        <td><code>{{ $key }}</code></td>
                                                        <td>
                                                            @if(is_array($value))
                                                                {{ json_encode($value, JSON_UNESCAPED_UNICODE) }}
                                                            @elseif(is_bool($value))
                                                                {{ $value ? 'true' : 'false' }}
                                                            @elseif($value === null || $value === '')
                                                                -
                                                            @else
                                                                {{ $value }}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
