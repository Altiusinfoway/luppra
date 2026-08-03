@extends('layouts.app')

@section('page-css')
    <style>
        .transport-form-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .transport-form-suite .hero-shell,
        .transport-form-suite .form-shell {
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        }

        .transport-form-suite .hero-shell {
            background:
                radial-gradient(circle at top right, rgba(16, 185, 129, 0.14), transparent 30%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        .transport-form-suite .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            padding: 7px 12px;
            border-radius: 999px;
            border: 1px solid #d1fae5;
            background: rgba(255, 255, 255, 0.86);
            color: #047857;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .transport-form-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.86);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .transport-form-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .transport-form-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
        }
    </style>
@endsection

@section('content')
    <div class="page-content transport-form-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-7">
                                    <span class="hero-eyebrow">Logistics Directory</span>
                                    <h2 class="mt-3 mb-2">Create Transport</h2>
                                    <p class="text-muted mb-0">Add a new transport partner with contact and address details inside the same updated logistics form shell.</p>
                                </div>
                                <div class="col-lg-5">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('transports.index') }}">Transport</a></li>
                                            <li class="breadcrumb-item active">Create</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-3 mb-4 justify-content-center">
                <div class="col-md-6 col-xl-3">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Logistics Setup</span>
                            <h3>New Partner</h3>
                            <p class="text-muted mb-0 mt-2">Create a transport partner profile in the same KPI-led setup language used across the refreshed logistics workspace.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Profile Scope</span>
                            <h3>Contacts + Address</h3>
                            <p class="text-muted mb-0 mt-2">Keep company details, contact numbers, and address coverage grouped into one focused transport form flow.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-xl-12 col-xxl-10 ">
                    <div class="card form-shell">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title  mb-0">Transport Add</h5>
                            </div>
                        </div>

                        <div class="card-body">

                            @include('transport._create')

                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
