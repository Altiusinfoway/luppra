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
                                <span class="hero-eyebrow">Payments</span>
                                <h1 class="hero-title">Razorpay Settings</h1>
                                <p class="hero-subtitle mb-0">Configure gateway credentials, enable checkout, and move into transaction review from the same modern admin shell as the rest of the refreshed project.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Admin</a></li>
                                        <li class="breadcrumb-item active">Razorpay</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-xl-7">
                <div class="card settings-shell">
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
