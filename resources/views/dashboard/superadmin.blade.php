@extends('layouts.app')

@section('page-css')
<style>
    .superadmin-suite {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
    }

    .superadmin-suite .hero-shell,
    .superadmin-suite .stat-shell {
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 26px;
        background:
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
            radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
            #ffffff;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
    }

    .superadmin-suite .hero-eyebrow {
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

    .superadmin-suite .metric-label {
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: .55rem;
    }

    .superadmin-suite .surface-card {
        background: rgba(255, 255, 255, 0.88);
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 22px;
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
    }

    .superadmin-suite .section-title {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.02em;
    }

    .superadmin-suite .section-subtitle {
        color: #64748b;
        font-size: .84rem;
    }

    .superadmin-suite .mini-panel {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 1rem;
        background: #f8fafc;
        height: 100%;
    }
</style>
@endsection

@section('content')
<div class="page-content superadmin-suite">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-8">
                                <span class="hero-eyebrow">Platform Control</span>
                                <h1 class="mb-3">Superadmin Dashboard</h1>
                                <p class="text-muted mb-0">Monitor tenant growth, subscription health, revenue trend, and platform stability from one polished control surface.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end gap-2 flex-wrap">
                                    <a href="{{ route('setting.razorpay.index') }}" class="btn btn-sm btn-primary">Razorpay Settings</a>
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Superadmin</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6 col-xl-3">
                <div class="card stat-shell"><div class="card-body">
                    <div class="metric-label">Companies</div>
                    <h3 class="mb-0">{{ number_format($saas_kpis['tenants_total'] ?? 0) }}</h3>
                </div></div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card stat-shell"><div class="card-body">
                    <div class="metric-label">Active Companies</div>
                    <h3 class="mb-0">{{ number_format($saas_kpis['tenants_active'] ?? 0) }}</h3>
                </div></div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card stat-shell"><div class="card-body">
                    <div class="metric-label">Active Users</div>
                    <h3 class="mb-0">{{ number_format($saas_kpis['subs_active'] ?? 0) }}</h3>
                </div></div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card stat-shell"><div class="card-body">
                    <div class="metric-label">Revenue</div>
                    <h3 class="mb-0">&#8377;{{ number_format($saas_kpis['estimated_mrr'] ?? 0, 2) }}</h3>
                </div></div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-9">
                <div class="card surface-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1 section-title">Application Snapshot</h5>
                            <div class="section-subtitle">Platform health across tenant connectivity, schema, and subscription status.</div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="mini-panel">
                                    <div class="text-muted small">DB Reachable</div>
                                    <div class="h4 mb-0">{{ $tenant_health_summary['db_ok'] ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mini-panel">
                                    <div class="text-muted small">Schema Healthy</div>
                                    <div class="h4 mb-0">{{ $tenant_health_summary['schema_ok'] ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mini-panel">
                                    <div class="text-muted small">Valid Subscription</div>
                                    <div class="h4 mb-0">{{ $tenant_health_summary['subscription_ok'] ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mini-panel border-danger-subtle">
                                    <div class="text-muted small">WA Limit Breached</div>
                                    <div class="h4 mb-0 text-danger">{{ $tenant_health_summary['whatsapp_limit_breached'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3">
                <div class="card surface-card h-100">
                    <div class="card-header">
                        <div>
                            <h5 class="card-title mb-1 section-title">Quick Actions</h5>
                            <div class="section-subtitle">Common platform management shortcuts.</div>
                        </div>
                    </div>
                    <div class="card-body d-grid gap-2">
                        <a href="{{ route('setting.razorpay.index') }}" class="btn btn-outline-primary">Razorpay Settings</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-8">
                <div class="card surface-card">
                    <div class="card-header">
                        <div>
                            <h5 class="card-title mb-1 section-title">6-Month SaaS Trend</h5>
                            <div class="section-subtitle">Revenue and tenant growth trend for the last six months.</div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="saasTrendChart" style="min-height: 320px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card surface-card">
                    <div class="card-header">
                        <div>
                            <h5 class="card-title mb-1 section-title">Plan Mix</h5>
                            <div class="section-subtitle">Current distribution of subscribed plan types.</div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="planMixChart" style="min-height: 320px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-5">
                <div class="card surface-card">
                    <div class="card-header">
                        <div>
                            <h5 class="card-title mb-1 section-title">Expiring in 15 Days</h5>
                            <div class="section-subtitle">Subscriptions that need attention soon.</div>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr><th>Tenant</th><th>Plan</th><th>End Date</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                @forelse($expiring_subscriptions as $sub)
                                    <tr>
                                        <td>{{ $sub->tenant->name ?? '-' }}</td>
                                        <td>{{ $sub->plan->name ?? '-' }}</td>
                                        <td>{{ optional($sub->ends_at)->format('d M Y') }}</td>
                                        <td><span class="badge bg-warning text-dark">{{ strtoupper($sub->status) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">No expiring subscriptions.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xl-7">
                <div class="card surface-card">
                    <div class="card-header">
                        <div>
                            <h5 class="card-title mb-1 section-title">Recent Tenants</h5>
                            <div class="section-subtitle">Newest tenant activity and current subscription state.</div>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Tenant</th>
                                    <th>Plan</th>
                                    <th>Users</th>
                                    <th>WA Used</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_tenants as $tenant)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $tenant->name }}</div>
                                            <small class="text-muted">{{ $tenant->slug }}</small>
                                        </td>
                                        <td>{{ $tenant->plan ?? '-' }}</td>
                                        <td>{{ $tenant->users_count }}</td>
                                        <td>{{ $tenant->whatsapp_used }}</td>
                                        <td>
                                            @if($tenant->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">No tenants found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const trend = new ApexCharts(document.querySelector("#saasTrendChart"), {
        chart: { type: 'line', height: 320, toolbar: { show: false } },
        stroke: { curve: 'smooth', width: 3 },
        series: [
            { name: 'Revenue', data: @json($revenue_trend_values) },
            { name: 'New Tenants', data: @json($tenant_growth_values) }
        ],
        xaxis: { categories: @json($trend_labels) }
    });
    trend.render();

    const mix = new ApexCharts(document.querySelector("#planMixChart"), {
        chart: { type: 'donut', height: 320 },
        labels: @json($plan_mix_labels),
        series: @json($plan_mix_values),
        legend: { position: 'bottom' }
    });
    mix.render();
});
</script>
@endsection
