@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Template Preview</h4>
                    <div class="d-flex gap-2">
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

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-xl-4 col-md-6">
                <div class="card h-100">
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
