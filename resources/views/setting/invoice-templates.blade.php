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
    .settings-suite .metric-card {
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.84);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
    }
    .settings-suite .metric-card .label {
        display: block;
        margin-bottom: 8px;
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .settings-suite .metric-card h4 {
        margin: 0;
        font-size: 1.7rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #0f172a;
    }
    .settings-suite .template-card {
        border: 1px solid #e2e8f0 !important;
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.05);
    }
    .settings-suite .template-card .card-header {
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
    }
    .settings-suite .template-meta {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 10px;
        background: #f8fafc;
    }
    .settings-suite .template-footer {
        background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%) !important;
        border-top: 1px solid #e2e8f0;
    }
    .settings-suite .status-banner {
        border: 1px solid #dce4ee;
        border-radius: 18px;
        padding: 1rem 1.15rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }
    .settings-suite .status-banner.status-success {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border-color: #bbf7d0;
        color: #166534;
    }
    .settings-suite .status-banner.status-danger {
        background: linear-gradient(135deg, #fef2f2 0%, #fff1f2 100%);
        border-color: #fecdd3;
        color: #be123c;
    }
    .settings-suite .status-banner.status-warning {
        background: linear-gradient(135deg, #fffbeb 0%, #fff7d6 100%);
        border-color: #fde68a;
        color: #92400e;
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
                                <h1 class="hero-title">Invoice Templates</h1>
                                <p class="hero-subtitle mb-0">Review template readiness, manage seeded sections, and create new invoice layouts in the same modern admin shell as the rest of the refreshed UI.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Invoice Templates</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="status-banner status-success mb-3">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="status-banner status-danger mb-3">{{ session('error') }}</div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card h-100 metric-card">
                    <div class="card-body">
                        <span class="label">Total Templates</span>
                        <h4 class="mb-0">{{ $templates->count() }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 metric-card">
                    <div class="card-body">
                        <span class="label">Active Templates</span>
                        <h4 class="mb-0">{{ $templates->where('is_active', true)->count() }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 metric-card">
                    <div class="card-body">
                        <span class="label">Default Template</span>
                        <h4 class="mb-0">{{ optional($templates->firstWhere('is_default', true))->name ?? 'Not Set' }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card settings-shell">
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
                    <div class="status-banner status-warning mb-0">
                        No invoice templates found yet. Run the landlord invoice template seeder after migrations.
                    </div>
                @else
                    <div class="row g-3">
                        @foreach($templates as $template)
                            <div class="col-xl-4 col-md-6">
                                <div class="card border h-100 mb-0 template-card">
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
                                                <div class="template-meta small">
                                                    <div class="text-muted">Paper</div>
                                                    <div class="fw-semibold">{{ $template->paper_size }}</div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="template-meta small">
                                                    <div class="text-muted">Orientation</div>
                                                    <div class="fw-semibold text-capitalize">{{ $template->orientation }}</div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="template-meta small">
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
                                    <div class="card-footer template-footer">
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
