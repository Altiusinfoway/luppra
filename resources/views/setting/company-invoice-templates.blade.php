@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">New Invoice Templates</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Template Selection</li>
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
            <div class="col-xl-4 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-medium fs-12 mb-2">Current Template</p>
                        <h4 class="mb-1">{{ $effectiveTemplate?->name ?? 'Not Available' }}</h4>
                        <div class="text-muted">{{ $effectiveTemplate?->code ?? 'No active template found' }}</div>
                        @if($effectiveTemplate?->is_default)
                            <span class="badge bg-success mt-3">Landlord Default</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-medium fs-12 mb-2">Available Templates</p>
                        <h4 class="mb-0">{{ $templates->count() }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-12">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-medium fs-12 mb-2">How It Works</p>
                        <div class="text-muted small">
                            You can preview and select an active landlord template here. The new template invoice route will use your selected template automatically.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($templates->isEmpty())
            <div class="alert alert-warning mb-0">
                No active invoice templates are available right now.
            </div>
        @else
            <div class="row g-3">
                @foreach($templates as $template)
                    @php
                        $isSelected = $effectiveTemplate && (int) $effectiveTemplate->id === (int) $template->id;
                    @endphp

                    <div class="col-xl-4 col-md-6">
                        <div class="card border h-100 {{ $isSelected ? 'border-success' : '' }}">
                            <div class="card-header">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div>
                                        <h5 class="card-title mb-1">{{ $template->name }}</h5>
                                        <div class="text-muted small">{{ $template->code }}</div>
                                    </div>
                                    <div class="text-end">
                                        @if($isSelected)
                                            <span class="badge bg-success">Selected</span>
                                        @endif
                                        @if($template->is_default)
                                            <span class="badge bg-primary-subtle text-primary">Default</span>
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
                                </div>

                                <h6 class="mb-2">Visible Sections</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($template->sections as $section)
                                        <span class="badge bg-light text-dark border">
                                            {{ $section->sort_order }}. {{ $section->section_label }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="card-footer bg-light-subtle">
                                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                    <div class="small text-muted">
                                        {{ $template->sections->count() }} visible sections
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('setting.company-invoice-templates.show', $template->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            Preview
                                        </a>
                                        @if($isSelected)
                                            <button type="button" class="btn btn-sm btn-success" disabled>Selected</button>
                                        @else
                                            <form method="POST" action="{{ route('setting.company-invoice-templates.select', $template->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary">Use Template</button>
                                            </form>
                                        @endif
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
@endsection
