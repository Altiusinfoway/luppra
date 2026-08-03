@extends('layouts.app')

@section('page-css')
<style>
    .employee-dashboard-suite {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
    }

    .employee-dashboard-suite .hero-shell,
    .employee-dashboard-suite .surface-card {
        border: 1px solid rgba(255, 255, 255, 0.78);
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
    }

    .employee-dashboard-suite .hero-shell {
        border-radius: 28px;
        background:
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
            radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
            linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
        margin-bottom: 1rem;
    }

    .employee-dashboard-suite .surface-card {
        border-radius: 22px;
    }

    .employee-dashboard-suite .hero-eyebrow {
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

    .employee-dashboard-suite .hero-title {
        font-size: clamp(2rem, 3vw, 2.7rem);
        line-height: 1.05;
        letter-spacing: -0.04em;
        font-weight: 800;
        margin: 1rem 0 .45rem;
        color: #0f172a;
    }

    .employee-dashboard-suite .hero-subtitle,
    .employee-dashboard-suite .empty-note {
        color: #64748b;
    }

    .employee-dashboard-suite .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
    }

    .employee-dashboard-suite .empty-icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 1rem;
        border-radius: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(15, 118, 110, 0.12), rgba(37, 99, 235, 0.14));
        color: #1d4ed8;
        font-size: 1.8rem;
    }
</style>
@endsection

@section('content')
<div class="page-content employee-dashboard-suite">
    <div class="container-fluid">
        <div class="hero-shell">
            <div class="card-body p-4 p-lg-5">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <span class="hero-eyebrow">Employee Workspace</span>
                        <h1 class="hero-title">Employee Dashboard</h1>
                        <p class="hero-subtitle mb-0">A lighter, cleaner home for employee-facing work so the role experience still feels part of the same refreshed platform.</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="d-flex justify-content-lg-end">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Employee</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card surface-card">
            <div class="card-body empty-state">
                <div class="empty-icon">
                    <i class="ri-dashboard-line"></i>
                </div>
                <h4 class="mb-2">Employee home is ready for the refreshed UI</h4>
                <p class="empty-note mb-0">This role dashboard now matches the modern shell and is ready for employee-focused widgets, attendance shortcuts, and personal work summaries.</p>
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
