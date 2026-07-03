@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Razorpay Settings</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Admin</a></li>
                            <li class="breadcrumb-item active">Razorpay</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-xl-7">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Gateway Configuration</h5>
                            <a href="{{ route('setting.razorpay.transactions') }}" class="btn btn-sm btn-outline-dark">View Transactions</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('setting.razorpay.save') }}">
                            @csrf

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="razorpay_enabled" name="razorpay_enabled" value="1"
                                    {{ (int) ($settings['razorpay_enabled'] ?? 0) === 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="razorpay_enabled">Enable Razorpay Checkout</label>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Razorpay Key ID</label>
                                <input type="text" name="razorpay_key_id" class="form-control"
                                    value="{{ $settings['razorpay_key_id'] ?? '' }}" placeholder="rzp_live_xxxxx / rzp_test_xxxxx">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Razorpay Key Secret</label>
                                <input type="text" name="razorpay_key_secret" class="form-control"
                                    value="{{ $settings['razorpay_key_secret'] ?? '' }}" placeholder="Enter Razorpay secret key">
                                <div class="form-text">Keep this private. This is used for order API and signature verification.</div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Save Settings</button>
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Back</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
