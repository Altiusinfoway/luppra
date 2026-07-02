@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Invoice Templates</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Invoice Templates</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-medium fs-12 mb-2">Total Templates</p>
                        <h4 class="mb-0">{{ $templates->count() }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-medium fs-12 mb-2">Active Templates</p>
                        <h4 class="mb-0">{{ $templates->where('is_active', true)->count() }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-medium fs-12 mb-2">Default Template</p>
                        <h4 class="mb-0">{{ optional($templates->firstWhere('is_default', true))->name ?? 'Not Set' }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Landlord Template Library</h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-info-subtle text-info">Readiness View</span>
                    <a href="{{ route('setting.invoice-templates.create') }}" class="btn btn-sm btn-primary">
                        Add Template
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($templates->isEmpty())
                    <div class="alert alert-warning mb-0">
                        No invoice templates found yet. Run the landlord invoice template seeder after migrations.
                    </div>
                @else
                    <div class="row g-3">
                        @foreach($templates as $template)
                            <div class="col-xl-4 col-md-6">
                                <div class="card border h-100 mb-0">
                                    <div class="card-header">
                                        <div class="d-flex align-items-start justify-content-between gap-2">
                                            <div>
                                                <h5 class="card-title mb-1">{{ $template->name }}</h5>
                                                <div class="text-muted small">{{ $template->code }}</div>
                                            </div>
                                            <div class="text-end">
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
                                    <div class="card-body">
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <div class="border rounded p-2 small">
                                                    <div class="text-muted">Paper</div>
                                                    <div class="fw-semibold">{{ $template->paper_size }}</div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="border rounded p-2 small">
                                                    <div class="text-muted">Orientation</div>
                                                    <div class="fw-semibold text-capitalize">{{ $template->orientation }}</div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="border rounded p-2 small">
                                                    <div class="text-muted">View Name</div>
                                                    <div class="fw-semibold">{{ $template->view_name }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <h6 class="mb-2">Sections</h6>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($template->sections as $section)
                                                <span class="badge bg-light text-dark border">
                                                    {{ $section->sort_order }}. {{ $section->section_label }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="card-footer bg-light-subtle">
                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                            <div class="small text-muted">
                                                {{ $template->sections->count() }} sections seeded
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('setting.invoice-templates.edit', $template->id) }}"
                                                    class="btn btn-sm btn-outline-secondary">
                                                    Edit
                                                </a>
                                                <a href="{{ route('setting.invoice-templates.show', $template->id) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    View Output
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
