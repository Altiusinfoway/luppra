@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Invoice Template Details</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('setting.invoice-templates.edit', $template->id) }}" class="btn btn-sm btn-primary">Edit Template</a>
                        <a href="{{ route('setting.invoice-templates.index') }}" class="btn btn-sm btn-outline-primary">Back</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-4 col-md-6">
                <div class="card h-100">
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
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-medium fs-12 mb-2">Document Setup</p>
                        <div class="mb-2"><strong>Paper:</strong> {{ $template->paper_size }}</div>
                        <div class="mb-2 text-capitalize"><strong>Orientation:</strong> {{ $template->orientation }}</div>
                        <div><strong>View:</strong> {{ $template->view_name }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-12">
                <div class="card h-100">
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

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">What Is Different</h5>
            </div>
            <div class="card-body">
                @if($template->is_default)
                    <div class="alert alert-info mb-0">
                        This is the default baseline template. Other templates can be compared against this one.
                    </div>
                @elseif(empty($differences))
                    <div class="alert alert-success mb-0">
                        No differences found compared to the current default template.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
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
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Section Configuration</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($template->sections as $section)
                        <div class="col-xl-6">
                            <div class="border rounded h-100 p-3">
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div>
                                        <h6 class="mb-1">{{ $section->sort_order }}. {{ $section->section_label }}</h6>
                                        <div class="text-muted small">{{ $section->section_key }}</div>
                                    </div>
                                    <span class="badge {{ $section->is_visible ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                        {{ $section->is_visible ? 'Visible' : 'Hidden' }}
                                    </span>
                                </div>

                                @php
                                    $settings = is_array($section->settings_json) ? $section->settings_json : [];
                                @endphp

                                @if(empty($settings))
                                    <div class="text-muted small">No extra settings.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="table-light">
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
