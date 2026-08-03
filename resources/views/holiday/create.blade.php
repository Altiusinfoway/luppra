@extends('layouts.app')

@section('page-css')
    <style>
        .hr-form-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .hr-form-suite .hero-shell,
        .hr-form-suite .form-shell {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
        }

        .hr-form-suite .hero-eyebrow {
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

        .hr-form-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.84);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .hr-form-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .hr-form-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
        }

        .hr-form-suite .section-intro {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 16px 18px;
            margin-bottom: 22px;
        }

        .hr-form-suite .form-actions {
            border-top: 1px solid #e2e8f0;
            margin-top: 8px;
            padding-top: 20px;
        }
    </style>
@endsection

@section('content')
<div class="page-content hr-form-suite">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-8">
                                <span class="hero-eyebrow">Calendar Setup</span>
                                <h1 class="mb-3">Create Holiday</h1>
                                <p class="text-muted mb-0">Add a new holiday period with status and description inside the refreshed scheduling form shell.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('holidays.index') }}">Holiday</a></li>
                                        <li class="breadcrumb-item active">Create</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Calendar</span>
                        <h3>Holiday</h3>
                        <p class="text-muted mb-0 mt-2">Create date blocks for company-wide non-working periods in a clearer scheduling form.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Coverage</span>
                        <h3>Start To End</h3>
                        <p class="text-muted mb-0 mt-2">Use explicit date ranges so the holiday calendar stays predictable for the team.</p>
                    </div>
                </div>
            </div>
            <div class="col-xxl-10 col-lg-10">
                <div class="card form-shell">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title mb-0">Holiday Add</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('holidays.store') }}" method="POST" id="holidaysForm">
                            @csrf
                            <div class="section-intro">
                                <h6 class="mb-1">Holiday details</h6>
                                <p class="text-muted mb-0">Define the holiday name, active date range, and status so planning and attendance tools stay aligned.</p>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label" for="name">name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" id="name" placeholder="Enter name">
                                    @if($errors->has('name'))
                                        <div class="error text-danger">{{ $errors->first('name') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="start_date">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" class="form-control datepicker-range flatpickr-input active" id="datepicker-range" data-provider="flatpickr" data-range="true" placeholder="Select Start Date">
                                    @if($errors->has('start_date'))
                                        <div class="error text-danger">{{ $errors->first('start_date') }}</div>
                                    @endif
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="end_date">End Date<span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" class="form-control datepicker-range flatpickr-input active" id="datepicker-range" data-provider="flatpickr" data-range="true" placeholder="Select End Date">
                                    @if($errors->has('end_date'))
                                        <div class="error text-danger">{{ $errors->first('end_date') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6 mb-3">
                                    <label for="" class="form-label">Status <span class="text-danger">*</span></label>
                                    <div>
                                        <input class="form-check-input" class="form-check" type="radio" name="is_active" id="active" value="1" checked>
                                        <label class="form-check-label" for="flexRadioDefault1">Active</label>
                                        &nbsp;
                                        <input class="form-check-input" class="form-check" type="radio" name="is_active" value="0" id="in-active">
                                        <label class="form-check-label" for="flexRadioDefault2">In-Active</label>
                                    </div>
                                    @if($errors->has('is_active'))
                                        <div class="error text-danger">{{ $errors->first('is_active') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3">
                                    <label class="form-label" for="description">Description</label>
                                    <textarea name="description" class="form-control" id="description" rows="10"></textarea>
                                </div>
                            </div>
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
