@extends('layouts.app')

@section('page-css')
    <style>
        .bank-form-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .bank-form-suite .hero-shell,
        .bank-form-suite .form-shell {
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        }

        .bank-form-suite .hero-shell {
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.14), transparent 30%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        .bank-form-suite .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            padding: 7px 12px;
            border-radius: 999px;
            border: 1px solid #dbeafe;
            background: rgba(255, 255, 255, 0.86);
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .bank-form-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.86);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .bank-form-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .bank-form-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
        }

        .bank-form-suite .section-card {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 16px;
        }
    </style>
@endsection

@section('content')
    <div class="page-content bank-form-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-7">
                                    <span class="hero-eyebrow">Banking Setup</span>
                                    <h2 class="mt-3 mb-2">Create Bank Account Detail</h2>
                                    <p class="text-muted mb-0">Add a new internal bank account with holder, branch, and IFSC details inside the same refreshed finance form shell.</p>
                                </div>
                                <div class="col-lg-5">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('bank-account-details.index') }}">Bank Account Detail</a></li>
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
                            <span class="label">Banking Setup</span>
                            <h3>New Account</h3>
                            <p class="text-muted mb-0 mt-2">Create an internal payout account using the same KPI-led finance form language as the refreshed banking workspace.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Fields</span>
                            <h3>Holder + Branch</h3>
                            <p class="text-muted mb-0 mt-2">Keep holder, account, bank, branch, and IFSC details grouped into one focused setup section.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card form-shell">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title  mb-0">Bank Account Detail Add</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('bank-account-details.store') }}" method="POST" id="salesTargetForm">
                                @csrf

                                <div class="section-card">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="account_holder_name">Account Holder Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="account_holder_name" class="form-control"
                                                placeholder="Enter Account Holder Name"
                                                value="{{ old('account_holder_name') }}">
                                            @if ($errors->has('account_holder_name'))
                                                <div class="error text-danger">{{ $errors->first('account_holder_name') }}</div>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="account_no">Account Number<span
                                                    class="text-danger">*</span></label>
                                            <input type="number" name="account_no" class="form-control"
                                                placeholder="Enter Account Number" value="{{ old('account_no') }}">
                                            @if ($errors->has('account_no'))
                                                <div class="error text-danger">{{ $errors->first('account_no') }}</div>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="account_type">Account Type<span
                                                    class="text-danger">*</span></label>
                                            <select name="account_type" class="form-control">
                                                <option value="">Select Account Type</option>
                                                @foreach (\App\Models\BankDetail::getAccountTypes() as $key => $val)
                                                    <option value="{{ $key }}"
                                                        {{ old('account_type') == $key ? 'selected' : '' }}>
                                                        {{ $val }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('account_type'))
                                                <div class="error text-danger">{{ $errors->first('account_type') }}</div>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="bank_name">Bank Name<span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="bank_name" class="form-control"
                                                placeholder="Enter Bank Name" value="{{ old('bank_name') }}">
                                            @if ($errors->has('bank_name'))
                                                <div class="error text-danger">{{ $errors->first('bank_name') }}</div>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="branch_name">Branch Name<span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="branch_name" class="form-control"
                                                placeholder="Enter Branch Name" value="{{ old('branch_name') }}">
                                            @if ($errors->has('branch_name'))
                                                <div class="error text-danger">{{ $errors->first('branch_name') }}</div>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="ifsc_code">IFSC Code<span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="ifsc_code" class="form-control"
                                                placeholder="Enter IFSC Code" value="{{ old('ifsc_code') }}">
                                            @if ($errors->has('ifsc_code'))
                                                <div class="error text-danger">{{ $errors->first('ifsc_code') }}</div>
                                            @endif
                                        </div>
                                        <div class="text-center mb-3">
                                            <button type="submit" class="btn btn-sm btn-primary w-sm">Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
