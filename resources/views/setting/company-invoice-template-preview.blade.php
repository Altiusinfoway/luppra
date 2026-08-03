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
                                <h1 class="hero-title">Template Preview</h1>
                                <p class="hero-subtitle mb-0">Compare the selected invoice template, preview its structure, and switch your company’s default template from the same refreshed admin shell.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end gap-2">
                                    @if(!$effectiveTemplate || (int) $effectiveTemplate->id !== (int) $template->id)
                                        <form method="POST" action="{{ route('setting.company-invoice-templates.select', $template->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary">Use This Template</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('setting.company-invoice-templates.index') }}" class="btn btn-sm btn-outline-primary">Back</a>
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
            <div class="col-xl-4 col-md-6">
                <div class="card h-100 settings-shell">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-medium fs-12 mb-2">Template</p>
                        <h4 class="mb-1">{{ $template->name }}</h4>
                        <div class="text-muted">{{ $template->code }}</div>
                        <div class="mt-3 d-flex gap-2 flex-wrap">
                            @if($effectiveTemplate && (int) $effectiveTemplate->id === (int) $template->id)
                                <span class="badge bg-success">Currently Selected</span>
                            @endif
                            @if($template->is_default)
                                <span class="badge bg-primary-subtle text-primary">Landlord Default</span>
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
                        <p class="text-muted text-uppercase fw-medium fs-12 mb-2">Selection Status</p>
                        <div class="mb-2"><strong>Saved Template Id:</strong> {{ $selectedTemplateId ?: 'Not set' }}</div>
                        <div class="mb-0"><strong>Visible Sections:</strong> {{ $template->sections->count() }}</div>
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
    </div>
</div>
@endsection
