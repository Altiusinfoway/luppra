@extends('layouts.app')

@section('page-css')
    <style>
        .device-form-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .device-form-suite .hero-shell,
        .device-form-suite .form-shell {
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        }

        .device-form-suite .hero-shell {
            background:
                radial-gradient(circle at top right, rgba(34, 197, 94, 0.14), transparent 30%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        .device-form-suite .hero-eyebrow {
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

        .device-form-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.86);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .device-form-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .device-form-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
        }

        .device-form-suite .section-intro {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 16px 18px;
            margin-bottom: 22px;
        }

        .device-form-suite .form-actions {
            border-top: 1px solid #e2e8f0;
            margin-top: 8px;
            padding-top: 20px;
        }
    </style>
@endsection

@section('content')
    <div class="page-content device-form-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-7">
                                    <span class="hero-eyebrow">Whatsapp Devices</span>
                                    <h2 class="mt-3 mb-2">Create Device</h2>
                                    <p class="text-muted mb-0">Register a new device, attach phone details, and prepare webhook routing from the same cleaner communication setup form.</p>
                                </div>
                                <div class="col-lg-5">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('device.index') }}">Device</a></li>
                                            <li class="breadcrumb-item active">Create</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Setup</span>
                            <h3>Device</h3>
                            <p class="text-muted mb-0 mt-2">Register a new WhatsApp device with the same clean form rhythm used across the dashboard.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Requires</span>
                            <h3>Name + Phone</h3>
                            <p class="text-muted mb-0 mt-2">Add the core identity details first, then extend routing through the webhook field if needed.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card form-shell">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title  mb-0">Device Add</h5>
                            </div>
                        </div>
                        <div class="card-body">
                           <form method="POST" class="ajaxform_instant_reload" action="{{ route('device.store') }}">
                            @csrf
                            <div class="section-intro">
                                <h6 class="mb-1">Device details</h6>
                                <p class="text-muted mb-0">Create the device record here before scanning the QR and starting live chat activity.</p>
                            </div>
                            <div class="form-group row mb-4">
                            <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Device Name') }}</label>
                            <div class="col-sm-12 col-md-7">
                                <input type="text" name="name" placeholder="My Iphone 13 Pro" class="form-control">
                            </div>
                        </div>
                        <div class="form-group row mb-4">
                            <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Phone Number') }}</label>
                            <div class="col-sm-12 col-md-7">
                                <input type="tel" name="phone" required placeholder="Enter the phone number" class="form-control">

                            </div>

                        </div>
                        <div class="form-group row mb-4">
                            <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Webhook Url') }}</label>
                            <div class="col-sm-12 col-md-7">
                                <input type="url" name="webhook_url" placeholder="your webhook receiver url" class="form-control">
                                <small class="text-danger">{{ env('APP_NAME').__(' will sent via post method to this url') }}</small>
                            </div>

                        </div>
                        {{-- <div class="form-group row mb-4">
                            <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Lead Phone') }}</label>
                            <div class="col-sm-12 col-md-7">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_lead_mobile_number" id="is_lead_mobile_number" value="1">
                                    <label class="form-check-label" for="is_lead_mobile_number">{{ __('Create lead from incoming customer messages on this device') }}</label>
                                </div>
                                <small class="text-muted">{{ __('When enabled, new incoming WhatsApp numbers will be checked in customers and a lead will be created automatically.') }}</small>
                            </div>

                        </div> --}}


                                <div class="text-center mb-3 form-actions">
                                    <button type="submit" class="btn btn-success w-sm">Submit</button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
