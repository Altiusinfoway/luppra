@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Superadmin Dashboard</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('setting.razorpay.index') }}" class="btn btn-sm btn-outline-success">Razorpay Settings</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6 col-xl-3">
                <div class="card"><div class="card-body">
                    <p class="text-muted mb-1">Companies</p>
                    <h3 class="mb-0">{{ number_format($saas_kpis['tenants_total'] ?? 0) }}</h3>
                </div></div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card"><div class="card-body">
                    <p class="text-muted mb-1">Active Companies</p>
                    <h3 class="mb-0">{{ number_format($saas_kpis['tenants_active'] ?? 0) }}</h3>
                </div></div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card"><div class="card-body">
                    <p class="text-muted mb-1">Active Users</p>
                    <h3 class="mb-0">{{ number_format($saas_kpis['subs_active'] ?? 0) }}</h3>
                </div></div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card"><div class="card-body">
                    <p class="text-muted mb-1">Revenue</p>
                    <h3 class="mb-0">&#8377;{{ number_format($saas_kpis['estimated_mrr'] ?? 0, 2) }}</h3>
                </div></div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-9">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Application Snapshot</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted small">DB Reachable</div>
                                    <div class="h4 mb-0">{{ $tenant_health_summary['db_ok'] ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted small">Schema Healthy</div>
                                    <div class="h4 mb-0">{{ $tenant_health_summary['schema_ok'] ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted small">Valid Subscription</div>
                                    <div class="h4 mb-0">{{ $tenant_health_summary['subscription_ok'] ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3 h-100 border-danger-subtle">
                                    <div class="text-muted small">WA Limit Breached</div>
                                    <div class="h4 mb-0 text-danger">{{ $tenant_health_summary['whatsapp_limit_breached'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3">
                <div class="card h-100">
                    <div class="card-header"><h5 class="card-title mb-0">Quick Actions</h5></div>
                    <div class="card-body d-grid gap-2">
                        <a href="{{ route('setting.razorpay.index') }}" class="btn btn-outline-primary">Razorpay Settings</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">6-Month SaaS Trend</h5></div>
                    <div class="card-body">
                        <div id="saasTrendChart" style="min-height: 320px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Plan Mix</h5></div>
                    <div class="card-body">
                        <div id="planMixChart" style="min-height: 320px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-5">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Expiring in 15 Days</h5></div>
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
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Recent Tenants</h5></div>
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
