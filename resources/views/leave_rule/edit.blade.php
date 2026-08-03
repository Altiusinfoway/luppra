@extends('layouts.app')

@section('page-css')
<style>
.leave-rule-suite {
    background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
}

.leave-rule-suite .hero-shell,
.leave-rule-suite .form-shell {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
}

.leave-rule-suite .hero-shell {
    background:
        radial-gradient(circle at top right, rgba(16, 185, 129, 0.14), transparent 30%),
        radial-gradient(circle at left center, rgba(59, 130, 246, 0.12), transparent 30%),
        linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}

.leave-rule-suite .hero-eyebrow {
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

.leave-rule-suite .summary-card {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.86);
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
}

.leave-rule-suite .summary-card .label {
    display: block;
    margin-bottom: 8px;
    color: #64748b;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.leave-rule-suite .summary-card h3 {
    margin: 0;
    font-size: 1.7rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: #0f172a;
}

.leave-rule-suite .section-card {
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    background: #ffffff;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
}

.leave-rule-suite .section-intro {
    border: 1px solid #d1fae5;
    border-radius: 18px;
    background: #f0fdf4;
    padding: 16px 18px;
    margin-bottom: 20px;
}

.leave-rule-suite .form-actions {
    border-top: 1px solid #e2e8f0;
    margin-top: 8px;
    padding-top: 20px;
}
</style>
@endsection

@section('content')
<div class="page-content leave-rule-suite">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="card hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <span class="hero-eyebrow">Policy Setup</span>
                                <h2 class="mt-3 mb-2">Leave Rule</h2>
                                <p class="text-muted mb-0">Adjust total leave policy settings inside the same cleaner HR form system used across the refreshed admin pages.</p>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('leave-rules.edit',1) }}">Leave Rule</a></li>
                                        <li class="breadcrumb-item active">Edit</li>
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
                        <span class="label">Policy</span>
                        <h3>Leave Rule</h3>
                        <p class="text-muted mb-0 mt-2">Maintain the annual leave baseline with the same clean card rhythm used across the refreshed admin pages.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Current Total</span>
                        <h3>{{ $leave_rule['total_leave'] ?? 0 }}</h3>
                        <p class="text-muted mb-0 mt-2">See the current total leave allowance before adjusting the HR policy value.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Mode</span>
                        <h3>Edit Rule</h3>
                        <p class="text-muted mb-0 mt-2">This screen updates the shared leave cap used across employee leave management flows.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="card form-shell">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title  mb-0">Leave Rule Edit</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('leave-rules.update',1) }}" method="POST" id="holidaysForm">
                        @csrf
                            <div class="section-card p-3 p-lg-4">
                                <div class="section-intro">
                                    <h6 class="mb-1">Annual leave baseline</h6>
                                    <p class="text-muted mb-0">Keep the total leave allowance aligned with your current HR policy so downstream leave requests start from the correct default.</p>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label" for="total_leave">Total Leave <span class="text-danger">*</span></label>
                                        <input type="number" name="total_leave" class="form-control" id="total_leave" placeholder="Enter Total Leave" value="{{ $leave_rule['total_leave'] ?? '' }}">
                                        @if($errors->has('total_leave'))
                                            <div class="error text-danger">{{ $errors->first('total_leave') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-center form-actions">
                                    <button type="submit" class="btn btn-primary w-sm">Save Leave Rule</button>
                                </div>
                            </div>

                        </form>
                        <!-- end card body -->
                        </div>
                </div>
            </div><!--end col-->
        </div><!--end row-->

    </div>
    <!-- container-fluid -->
</div>




@endsection
