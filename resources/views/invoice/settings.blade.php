@extends('layouts.app')

@section('page-css')
<style>
    .invoice-settings-suite {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
    }
    .invoice-settings-suite .hero-shell,
    .invoice-settings-suite .layout-shell {
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 26px;
        background:
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
            radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
            #ffffff;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
    }
    .invoice-settings-suite .hero-eyebrow {
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
    .invoice-settings-suite .hero-title {
        font-size: clamp(2rem, 3vw, 2.7rem);
        line-height: 1.05;
        letter-spacing: -0.04em;
        font-weight: 800;
        margin: 1rem 0 .45rem;
        color: #0f172a;
    }
    .layout-card {
        border: 1px solid #e6ebf1;
        border-radius: 18px;
        background: #fff;
        height: 100%;
        box-shadow: 0 8px 20px rgba(16, 24, 40, 0.05);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .layout-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
    }
    .layout-card.selected {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
    }
    .layout-preview {
        border: 1px solid #d9e1e8;
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
    }
    .layout-preview .head {
        padding: 8px 10px;
        font-weight: 600;
        font-size: 12px;
        border-bottom: 1px solid #d9e1e8;
    }
    .layout-preview .body {
        height: 100px;
        padding: 10px;
        background: repeating-linear-gradient(
            to bottom,
            #f7f9fc 0px,
            #f7f9fc 8px,
            #ffffff 8px,
            #ffffff 16px
        );
    }
    .theme-1 .head { background: #f2f4f7; }
    .theme-2 .head { background: #eef1f5; }
    .theme-3 .head { background: #dbe9ff; color: #1e4d8f; }
    .theme-4 .head { background: #d8f1e3; color: #0f5132; }
    .invoice-settings-suite .timeline-shell {
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 26px;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
    }
</style>
@endsection

@section('content')

<div class="page-content invoice-settings-suite">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-8">
                                <span class="hero-eyebrow">Invoice Design</span>
                                <h1 class="hero-title">Invoice View Settings</h1>
                                <p class="text-muted mb-0">Choose the invoice layout style that best matches your updated dashboard experience.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Invoice View</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('setting.invoice.save') }}">
            @csrf
            <div class="card layout-shell mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
                        <div>
                            <h5 class="card-title mb-1">Layout Library</h5>
                            <p class="text-muted mb-0">Switch between clean invoice compositions without leaving settings.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">Save Invoice Layout</button>
                            <a href="{{ route('invoices.index') }}" class="btn btn-light">View Invoices</a>
                            @if(!empty($sample_order_id))
                                <a href="{{ route('orders.invoice.preview', $sample_order_id) }}?original=1" target="_blank" class="btn btn-light">Preview Sample Invoice</a>
                            @endif
                        </div>
                    </div>

                    <div class="row g-3">
                        @php
                            $layouts = [
                                'layout_1' => 'Layout 1 (Classic)',
                                'layout_2' => 'Layout 2 (Compact)',
                                'layout_3' => 'Layout 3 (Blue Accent)',
                                'layout_4' => 'Layout 4 (Green Accent)',
                            ];
                        @endphp
                        @foreach($layouts as $key => $label)
                            <div class="col-12 col-md-6 col-xl-3">
                                <label class="layout-card p-3 d-block {{ $selected_layout === $key ? 'selected' : '' }}">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="fw-semibold">{{ $label }}</div>
                                        <input type="radio" name="invoice_layout" value="{{ $key }}" {{ $selected_layout === $key ? 'checked' : '' }}>
                                    </div>
                                    <div class="layout-preview theme-{{ explode('_', $key)[1] }}">
                                        <div class="head">Invoice Header</div>
                                        <div class="body"></div>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    @error('invoice_layout')
                        <div class="text-danger mt-3">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </form>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card timeline-shell">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Activity History</h5>
                    </div>
                    <div class="card-body">
                        @include('activity._timeline', [
                            'activities' => $settingsActivityTimeline,
                            'emptyMessage' => 'No activity found for invoice settings.',
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
