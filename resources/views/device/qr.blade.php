@extends('layouts.app')

@section('page-css')
    <style>
        .device-qr-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .device-qr-suite .hero-shell,
        .device-qr-suite .shell-card {
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        }

        .device-qr-suite .hero-shell {
            background:
                radial-gradient(circle at top right, rgba(34, 197, 94, 0.14), transparent 30%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        .device-qr-suite .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            padding: 7px 12px;
            border-radius: 999px;
            border: 1px solid #bbf7d0;
            background: rgba(255, 255, 255, 0.86);
            color: #15803d;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .device-qr-suite .qr-card-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .device-qr-suite .qr-area {
            min-height: 280px;
            border-radius: 22px;
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.95) 0%, rgba(239, 246, 255, 0.92) 100%);
            border: 1px dashed #bfdbfe;
            padding: 2rem;
        }

        .device-qr-suite .status-alert {
            display: none;
            align-items: center;
            gap: 12px;
            border-radius: 18px;
            padding: 14px 16px;
            font-weight: 700;
        }

        .device-qr-suite .status-alert.server_disconnect {
            border: 1px solid #fecaca;
            background: linear-gradient(135deg, #fff1f2 0%, #fff7ed 100%);
            color: #b91c1c;
        }

        .device-qr-suite .status-alert.logged-alert {
            border: 1px solid #bbf7d0;
            background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
            color: #166534;
        }

        .device-qr-suite .helper-box .btn {
            min-height: 48px;
        }
    </style>
@endsection

@section('content')
    <div class="page-content device-qr-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-7">
                                    <span class="hero-eyebrow">Whatsapp Devices</span>
                                    <h2 class="mt-3 mb-2">Connect Device</h2>
                                    <p class="text-muted mb-0">
                                        Pair your Whatsapp device, watch connection state, and jump into messaging actions from a clearer onboarding screen.
                                    </p>
                                </div>
                                <div class="col-lg-5">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('device.index') }}">Device</a></li>
                                            <li class="breadcrumb-item active">Connect</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card shell-card">
                        <div class="card-header">
                            <div class="qr-card-title">
                                <h4 class="mb-0">{{ __('Scan the QR Code On Your Whatsapp Mobile App') }}</h4>
                                <div class="card-header-action none loggout_area">
                                    <a href="javascript:void(0)" class="btn btn-sm btn-light logout-btn"
                                        data-id="{{ $device->uuid }}">
                                        <i class="fas fa-sign-out-alt me-1"></i>{{ __('Logout') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-center align-items-center qr-area">
                                <div class="text-center">
                                    <div class="spinner-grow text-primary" role="status">
                                        <span class="sr-only">{{ __('Loading...') }}</span>
                                    </div>
                                    <p class="mb-0 mt-3"><strong>{{ __('QR loading...') }}</strong></p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-grid gap-3">
                            <div class="status-alert server_disconnect none" role="alert">
                                <i class="ri-alert-line fs-5"></i>
                                <span>{{ __('Server disconnected. Reconnect the session to continue pairing this device.') }}</span>
                            </div>

                            <div class="status-alert logged-alert none" role="alert">
                                <i class="ri-checkbox-circle-line fs-5"></i>
                                <span>{{ __('Device connected successfully.') }}</span>
                                <img src="{{ asset('public/uploads/firework.png') }}" alt="">
                            </div>
                        </div>
                    </div>

                    <div class="card shell-card none helper-box mt-3">
                        <div class="card-body">
                            <div class="row">
                                @if($check_device_active)
                                    <div class="col-sm-6 mt-2">
                                        <a href="{{ url('/sent-text-message') }}" class="btn col-12 bg-primary text-white">
                                            <i class="fi fi-rs-paper-plane"></i>&nbsp {{ __('Send a message') }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-3 col-md-12">
                    <input type="hidden" id="device_status" value="{{ $device->status }}">
                    <input type="hidden" id="base_url" value="{{ url('/') }}">
                    <input type="hidden" id="device_id" value="{{ $device->uuid }}">
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
<script src="{{ asset('public/build/assets/js/pages/user/confetti.browser.min.js') }}"></script>
<script src="{{ asset('public/build/assets/js/pages/user/qr.js') }}"></script>
@endsection
