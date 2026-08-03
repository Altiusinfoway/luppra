@extends('layouts.app')

@section('page-css')
    <style>
        .vendor-form-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .vendor-form-suite .hero-shell {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
        }

        .vendor-form-suite .hero-eyebrow {
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

        .vendor-form-suite .form-shell,
        .vendor-form-suite .section-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.05);
        }

        .vendor-form-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.84);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .vendor-form-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .vendor-form-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
        }
    </style>
@endsection

@section('content')

    <div class="page-content vendor-form-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-8">
                                    <span class="hero-eyebrow">Procurement Directory</span>
                                    <h1 class="mb-3">Create Vendor</h1>
                                    <p class="text-muted mb-0">Add a new supplier with company, rating, address, and product details inside the updated form shell.</p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('vendors.index') }}">Vendors</a></li>
                                            <li class="breadcrumb-item active">Create</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Workflow</span>
                            <h3>Create</h3>
                            <p class="text-muted mb-0 mt-2">Register a new supplier profile with address and supply mapping in one flow.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Coverage</span>
                            <h3>Profile + Stock</h3>
                            <p class="text-muted mb-0 mt-2">Capture core business details and connect the vendor to supplied products together.</p>
                        </div>
                    </div>
                </div>
            </div>

            @include('vendor._create', ['data' => $product_list])

        </div>
        <!-- container-fluid -->
    </div>


@endsection
