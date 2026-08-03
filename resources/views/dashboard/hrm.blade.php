@extends('layouts.app')

@section('page-css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
    .hr-dashboard-suite {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
    }

    .hr-dashboard-suite .hero-shell,
    .hr-dashboard-suite .surface-card {
        border: 1px solid rgba(255, 255, 255, 0.78);
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
    }

    .hr-dashboard-suite .hero-shell {
        border-radius: 28px;
        background:
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
            radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
            linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
        margin-bottom: 1rem;
    }

    .hr-dashboard-suite .surface-card {
        border-radius: 22px;
        height: 100%;
    }

    .hr-dashboard-suite .hero-eyebrow {
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

    .hr-dashboard-suite .hero-title {
        font-size: clamp(2rem, 3vw, 2.7rem);
        line-height: 1.05;
        letter-spacing: -0.04em;
        font-weight: 800;
        margin: 1rem 0 .45rem;
        color: #0f172a;
    }

    .hr-dashboard-suite .hero-subtitle,
    .hr-dashboard-suite .card-note {
        color: #64748b;
    }

    .hr-dashboard-suite .metric-label {
        color: #64748b;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: .08em;
        font-weight: 800;
        margin-bottom: .45rem;
    }

    .hr-dashboard-suite .metric-value {
        font-size: 1.8rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
        letter-spacing: -0.03em;
    }

    .hr-dashboard-suite .icon-badge {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(15, 118, 110, 0.14), rgba(37, 99, 235, 0.16));
        color: #0f766e;
        font-size: 20px;
        flex: 0 0 auto;
    }

    .hr-dashboard-suite .mini-list {
        display: grid;
        gap: .65rem;
    }

    .hr-dashboard-suite .mini-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #f8fafc;
        padding: .75rem .9rem;
    }
</style>
@endsection

@section('content')
<div class="page-content hr-dashboard-suite">
    <div class="container-fluid">
        <div class="hero-shell">
            <div class="card-body p-4 p-lg-5">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <span class="hero-eyebrow">People Operations</span>
                        <h1 class="hero-title">HR Dashboard</h1>
                        <p class="hero-subtitle mb-0">Keep employee health, attendance, leave, and department distribution visible in the same clean admin dashboard language as the rest of the platform.</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="d-flex justify-content-lg-end">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">HR</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card surface-card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div>
                                <div class="metric-label">Total Employees</div>
                                <div class="metric-value">{{ number_format($total_emp_count) }}</div>
                                <div class="card-note mt-2">Current active headcount in the HR workspace.</div>
                            </div>
                            <span class="icon-badge"><i class="fa fa-users"></i></span>
                        </div>
                        <a href="{{ route('employees.index') }}" class="btn btn-sm btn-primary mt-3">View All</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card surface-card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div>
                                <div class="metric-label">Leave Requests Today</div>
                                <div class="metric-value">{{ number_format($leave_pending_count) }}</div>
                                <div class="card-note mt-2">Pending leave decisions waiting for action today.</div>
                            </div>
                            <span class="icon-badge"><i class="fa-solid fa-house-circle-check"></i></span>
                        </div>
                        <a href="{{ route('leaves.index') }}" class="btn btn-sm btn-primary mt-3">Review Leaves</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card surface-card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div>
                                <div class="metric-label">Attendance Today</div>
                                <div class="metric-value">{{ number_format($present_emp_count) }} / {{ number_format($absent_emp_count) }}</div>
                                <div class="card-note mt-2">Present / absent split for {{ now()->format('d M Y') }}.</div>
                            </div>
                            <span class="icon-badge"><i class="fa-solid fa-person-chalkboard"></i></span>
                        </div>
                        <a href="{{ route('attendances.report') }}" class="btn btn-sm btn-primary mt-3">Open Report</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card surface-card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div>
                                <div class="metric-label">Late Comers</div>
                                <div class="metric-value">{{ number_format(count($late_emp_list)) }}</div>
                                <div class="card-note mt-2">Employees marked late in today’s attendance cycle.</div>
                            </div>
                            <span class="icon-badge"><i class="fa-solid fa-user-clock"></i></span>
                        </div>
                        <a href="{{ route('attendances.report') }}" class="btn btn-sm btn-primary mt-3">View Late Log</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-5">
                <div class="card surface-card">
                    <div class="card-header">
                        <div>
                            <h5 class="card-title mb-1">Department-wise Count</h5>
                            <div class="card-note">Team size distribution across departments.</div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mini-list">
                            @foreach ($department_acc_emp_list as $deptName => $empCount)
                                <div class="mini-row">
                                    <span class="fw-semibold">{{ $deptName }}</span>
                                    <span class="badge bg-primary-subtle text-primary">{{ $empCount }}</span>
                                </div>
                            @endforeach
                        </div>
                        <a href="{{ route('employees.index') }}" class="btn btn-sm btn-light mt-3">Manage Employees</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('public/build/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('public/build/assets/js/pages/dashboard-ecommerce.init.js') }}"></script>
    <script src="{{ asset('public/build/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.0/dayjs.min.js') }}"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.0/plugin/quarterOfYear.min.js') }}"></script>
    <script src="{{ asset('public/build/assets/js/pages/apexcharts-mixed.init.js') }}"></script>
    <script src="{{ asset('public/build/assets/js/pages/apexcharts-column.init.js') }}"></script>
@endsection
