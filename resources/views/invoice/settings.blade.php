@extends('layouts.app')

@section('content')
<style>
    .layout-card {
        border: 1px solid #e6ebf1;
        border-radius: 14px;
        background: #fff;
        height: 100%;
        box-shadow: 0 8px 20px rgba(16, 24, 40, 0.05);
    }
    .layout-card.selected {
        border-color: #198754;
        box-shadow: 0 0 0 2px rgba(25, 135, 84, .12);
    }
    .layout-preview {
        border: 1px solid #d9e1e8;
        border-radius: 10px;
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
</style>

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Invoice View Setting</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Invoice View</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('setting.invoice.save') }}">
            @csrf
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
                <div class="text-danger mt-2">{{ $message }}</div>
            @enderror

            <div class="d-flex flex-wrap gap-2 mt-3">
                <button type="submit" class="btn btn-success">Save Invoice Layout</button>
                <a href="{{ route('invoices.index') }}" class="btn btn-outline-primary">View Invoices</a>
                @if(!empty($sample_order_id))
                    <a href="{{ route('orders.invoice.preview', $sample_order_id) }}?original=1" target="_blank" class="btn btn-outline-secondary">Preview Sample Invoice</a>
                @endif
            </div>
        </form>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
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
