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

        .hr-form-suite .status-banner {
            border: 1px solid #fecaca;
            border-radius: 18px;
            padding: 1rem 1.1rem;
            background: linear-gradient(180deg, #fef2f2 0%, #fffafa 100%);
            color: #b91c1c;
            box-shadow: 0 12px 26px rgba(239, 68, 68, 0.08);
            margin-bottom: 1rem;
        }

        .hr-form-suite .status-banner .banner-label {
            display: block;
            margin-bottom: 0.3rem;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            opacity: .82;
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

        .hr-form-suite .section-card {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        }

        .hr-form-suite .section-intro {
            border: 1px solid #dbeafe;
            border-radius: 18px;
            background: #f8fbff;
            padding: 16px 18px;
            margin-bottom: 20px;
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
                                <span class="hero-eyebrow">Time Off Management</span>
                                <h1 class="mb-3">Edit Leave</h1>
                                <p class="text-muted mb-0">Update leave approval details, dates, and remarks inside the same polished time-off form shell.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('leaves.index') }}">Leave</a></li>
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
                        <span class="label">Workflow</span>
                        <h3>Edit Leave</h3>
                        <p class="text-muted mb-0 mt-2">Refine an existing time-off request inside the same structured review layout used by the refreshed admin forms.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Employee</span>
                        <h3>{{ $leave['employee_name'] ?? 'Request' }}</h3>
                        <p class="text-muted mb-0 mt-2">Keep the request context visible while updating dates, reason, approval status, or remarks.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Current Status</span>
                        <h3>{{ $leave['status'] == 1 ? 'Pending' : ($leave['status'] == 2 ? 'Accepted' : ($leave['status'] == 3 ? 'Rejected' : 'Draft')) }}</h3>
                        <p class="text-muted mb-0 mt-2">Use this screen to reconcile approval state with the rest of the leave request details.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-xxl-10 col-lg-12">
                <div class="card form-shell">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title mb-0">Leave Edit</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('leaves.update', $leave['id']) }}" method="POST" id="customerForm">
                            @csrf

                            @if (session('error_msg'))
                                <div class="status-banner" id="error_model">
                                    <span class="banner-label">Leave request issue</span>
                                    {{ session('error_msg') }}
                                </div>
                            @endif
                            <div class="section-card p-3 p-lg-4">
                                <div class="section-intro">
                                    <h6 class="mb-1">Request review and update</h6>
                                    <p class="text-muted mb-0">Adjust the assignee, leave type, dates, and approval notes from one clean review block before saving changes.</p>
                                </div>

                                <div class="row">
                                    @if (\Auth::user()->type == 'HRM' || \Auth::user()->type == 'company')
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="leave_type">Select Employee <span class="text-danger">*</span></label>
                                                <select name="employee_id" class="form-control">
                                                    <option value="0">Select Employee</option>
                                                    @foreach ($emp_list as $elist)
                                                        <option value="{{ $elist['id'] }}" {{ $elist['id'] == $leave['employee_id'] ? 'selected' : '' }}>
                                                            {{ $elist['name'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @if ($errors->has('leave_type'))
                                                    <div class="error text-danger">{{ $errors->first('leave_type') }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="leave_type">Leave Type <span class="text-danger">*</span></label>
                                            <select name="leave_type" class="form-control">
                                                <option value="">Select Leave Type</option>
                                                @foreach ($leave_type_list as $id => $name)
                                                    <option value="{{ $id }}" {{ $id == $leave['leave_type'] ? 'selected' : '' }}>
                                                        {{ $name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('leave_type'))
                                                <div class="error text-danger">{{ $errors->first('leave_type') }}</div>
                                            @endif
                                        </div>
                                    </div>

                                    @if (!(\Auth::user()->type == 'HRM' || \Auth::user()->type == 'company'))
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="hours_leave">Leave Half / Full Day </label>
                                                <select name="hours_leave" class="form-control">
                                                    <option>select half/full day</option>
                                                    <option value="1" @if ($leave['hours_leave'] == 1) selected @endif>Half Day</option>
                                                    <option value="2" @if ($leave['hours_leave'] == 2) selected @endif>Full Day</option>
                                                </select>
                                                @if ($errors->has('hours_leave'))
                                                    <div class="error text-danger">{{ $errors->first('hours_leave') }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="start_date">Start Date <span class="text-danger">*</span></label>
                                            <input type="date" name="start_date" class="form-control datepicker-range flatpickr-input active" id="datepicker-range" data-provider="flatpickr" data-range="true" value="{{ $leave['start_date'] }}">
                                            @if ($errors->has('start_date'))
                                                <div class="error text-danger">{{ $errors->first('start_date') }}</div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="end_date">End Date <span class="text-danger">*</span></label>
                                            <input type="date" name="end_date" class="form-control datepicker-range flatpickr-input active" id="datepicker-range" data-provider="flatpickr" data-range="true" value="{{ $leave['end_date'] }}">
                                            @if ($errors->has('end_date'))
                                                <div class="error text-danger">{{ $errors->first('end_date') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    @if (\Auth::user()->type == 'HRM' || \Auth::user()->type == 'company')
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="hours_leave">Leave Half / Full Day </label>
                                                <select name="hours_leave" class="form-control">
                                                    <option>select half/full day</option>
                                                    <option value="1" @if ($leave['hours_leave'] == 1) selected @endif>Half Day</option>
                                                    <option value="2" @if ($leave['hours_leave'] == 2) selected @endif>Full Day</option>
                                                </select>
                                                @if ($errors->has('hours_leave'))
                                                    <div class="error text-danger">{{ $errors->first('hours_leave') }}</div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                                                <select name="status" class="form-control">
                                                    <option value="0">Select Status</option>
                                                    <option value="1" {{ $leave['status'] == 1 ? 'selected' : '' }}>Pending</option>
                                                    <option value="2" {{ $leave['status'] == 2 ? 'selected' : '' }}>Accept</option>
                                                    <option value="3" {{ $leave['status'] == 3 ? 'selected' : '' }}>Reject</option>
                                                </select>
                                                @if ($errors->has('status'))
                                                    <div class="error text-danger">{{ $errors->first('status') }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="row">
                                    <div class="mb-3 col-md-12">
                                        <label class="form-label" for="reason">Reason <span class="text-danger">*</span></label>
                                        <textarea name="reason" rows=8 class="form-control" style="direction:ltr;text-align:left;">{{ $leave['reason'] }}</textarea>
                                        @if ($errors->has('reason'))
                                            <div class="error text-danger">{{ $errors->first('reason') }}</div>
                                        @endif
                                    </div>
                                </div>

                                @if (\Auth::user()->type == 'HRM' || \Auth::user()->type == 'company')
                                    <div>
                                        <div class="mb-3 col-md-12">
                                            <label class="form-label" for="remark">Remark </label>
                                            <textarea name="remark" rows=8 class="form-control" style="direction:ltr;text-align:left;">{{ $leave['remark'] }}</textarea>
                                            @if ($errors->has('remark'))
                                                <div class="error text-danger">{{ $errors->first('remark') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <div class="text-center form-actions">
                                    <button type="submit" class="btn btn-primary w-sm">Save Leave Request</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        setTimeout(function() {
            $('#error_model').fadeOut(2000);
        }, 1000);
    });
</script>
@endsection
