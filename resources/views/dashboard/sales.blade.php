@extends('layouts.app')

@section('content')
@php
    $todayKpis = $today_kpis ?? [];
    $target = $target_summary ?? [];
    $charts = $sales_charts ?? [];
    $quotation = $quotation_summary ?? [];
    $collection = $collection_summary ?? [];
    $customers = $customer_insights ?? [];
    $activity = $activity_tracker ?? [];
    $inventory = $inventory_summary ?? [];
    $advanced = $advanced_kpis ?? [];

    $achievementPercent = (float) ($target['achievement_percent'] ?? 0);
    $achievementBarClass = $achievementPercent >= 75 ? 'bg-success' : ($achievementPercent >= 45 ? 'bg-warning' : 'bg-danger');

    $collectionPct = (float) ($collection['collection_target_pct'] ?? 0);
    $collectionBarClass = $collectionPct >= 80 ? 'bg-success' : ($collectionPct >= 50 ? 'bg-warning' : 'bg-danger');
@endphp

<style>
    .sales-hero {
        background: linear-gradient(120deg, #0f5132, #198754);
        color: #fff;
        border-radius: 14px;
        padding: 1.1rem 1.2rem;
        margin-bottom: 1rem;
    }
    .sales-card {
        border: 1px solid #e6ebf1;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .05);
        height: 100%;
    }
    .section-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: .75rem;
    }
    .kpi-title {
        font-size: .78rem;
        text-transform: uppercase;
        color: #6b7280;
        letter-spacing: .04em;
        margin-bottom: .35rem;
    }
    .kpi-value {
        font-size: 1.45rem;
        font-weight: 700;
        color: #111827;
        line-height: 1.2;
    }
    .kpi-sub {
        color: #6b7280;
        font-size: .82rem;
    }
    .mini-stat {
        border: 1px solid #e6ebf1;
        border-radius: 10px;
        padding: .75rem;
        background: #fbfcfd;
    }
    .progress {
        height: 9px;
        background: #edf1f5;
    }
    .table td,
    .table th {
        vertical-align: middle;
    }
    .discussion-note {
        max-width: 260px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .hero-action-btn {
        border-radius: 999px;
        font-weight: 600;
        letter-spacing: .01em;
        padding: .4rem .85rem;
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        transition: all .18s ease-in-out;
    }
    .hero-action-primary {
        background: #ffffff;
        color: #0f5132;
        border: 1px solid #ffffff;
        box-shadow: 0 4px 14px rgba(0, 0, 0, .12);
    }
    .hero-action-primary:hover {
        background: #f4fff8;
        color: #0a3622;
        transform: translateY(-1px);
    }
    .hero-action-outline {
        background: transparent;
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, .75);
    }
    .hero-action-outline:hover {
        background: rgba(255, 255, 255, .12);
        color: #ffffff;
    }
</style>

<div class="page-content">
    <div class="sales-hero d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h4 class="mb-1 text-white">Sales Person Dashboard</h4>
            <div class="opacity-75">{{ now()->format('d M Y') }} | Assigned pipeline and performance snapshot</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="javascript:void(0);" class="hero-action-btn hero-action-primary add-btn"
               data-size="lg" data-url="{{ route('leads.create') }}"
               data-ajax-popup="true" data-bs-original-title="{{ __('Add Lead') }}">
                <i class="ri-add-circle-line"></i>
                <span>New Lead</span>
            </a>
            <a href="{{ route('quotes.create') }}" class="hero-action-btn hero-action-outline">
                <i class="ri-file-list-3-line"></i>
                <span>New Quote</span>
            </a>
            <a href="{{ route('leads.list') }}" class="hero-action-btn hero-action-outline">
                <i class="ri-user-search-line"></i>
                <span>My Leads</span>
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-6 col-xl-2">
            <div class="sales-card p-3">
                <div class="kpi-title">Today Sales</div>
                <div class="kpi-value">&#8377;{{ number_format((float) ($todayKpis['today_sales_amount'] ?? 0), 0) }}</div>
                <div class="kpi-sub">Booked today</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
            <div class="sales-card p-3">
                <div class="kpi-title">New Leads</div>
                <div class="kpi-value">{{ number_format((int) ($todayKpis['new_leads_today'] ?? 0)) }}</div>
                <div class="kpi-sub">Added in leads today</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
            <div class="sales-card p-3">
                <div class="kpi-title">Today Follow-Ups</div>
                <div class="kpi-value">{{ number_format((int) ($todayKpis['today_followups'] ?? 0)) }}</div>
                <div class="kpi-sub">Scheduled today</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="sales-card p-3">
                <div class="kpi-title">Pending Quotations</div>
                <div class="kpi-value">{{ number_format((int) ($todayKpis['pending_quotations'] ?? 0)) }}</div>
                <div class="kpi-sub">Draft/Pending approval</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="sales-card p-3">
                <div class="kpi-title">Collections Today</div>
                <div class="kpi-value">&#8377;{{ number_format((float) ($todayKpis['collections_today'] ?? 0), 0) }}</div>
                <div class="kpi-sub">Received today</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-xl-5">
            <div class="sales-card p-3">
                <div class="section-title">Target vs Achievement</div>
                <div class="row g-2 mb-3">
                    <div class="col-12 col-md-4">
                        <div class="mini-stat">
                            <div class="kpi-title mb-1">Monthly Target</div>
                            <div class="fw-semibold">&#8377;{{ number_format((float) ($target['monthly_target'] ?? 0), 0) }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="mini-stat">
                            <div class="kpi-title mb-1">Achieved</div>
                            <div class="fw-semibold text-success">&#8377;{{ number_format((float) ($target['achieved_amount'] ?? 0), 0) }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="mini-stat">
                            <div class="kpi-title mb-1">Remaining</div>
                            <div class="fw-semibold text-danger">&#8377;{{ number_format((float) ($target['remaining_target'] ?? 0), 0) }}</div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <small class="text-muted">Achievement</small>
                    <small class="fw-semibold">{{ number_format($achievementPercent, 1) }}%</small>
                </div>
                <div class="progress mb-2">
                    <div class="progress-bar {{ $achievementBarClass }}" style="width: {{ min($achievementPercent, 100) }}%"></div>
                </div>
                <small class="text-muted">Required daily run-rate: &#8377;{{ number_format((float) ($target['required_daily_run_rate'] ?? 0), 0) }}</small>
                <div id="target-performance-chart" class="mt-3" style="height: 170px;"></div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="sales-card p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="section-title mb-0">Monthly Sales Trend</div>
                    <small class="text-muted">Current year</small>
                </div>
                <div id="sales-trend-chart" style="height: 300px;"></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-xl-4">
            <div class="sales-card p-3">
                <div class="section-title">Product-wise Sales</div>
                <div id="product-wise-chart" style="height: 280px;"></div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="sales-card p-3">
                <div class="section-title">Month-over-Month Growth %</div>
                <div id="mom-growth-chart" style="height: 280px;"></div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="sales-card p-3">
                <div class="section-title">Quotation Funnel</div>
                <div class="row g-2">
                    {{-- <div class="col-6"><div class="mini-stat"><div class="kpi-title">Draft</div><div class="fw-semibold">{{ $quotation['draft'] ?? 0 }}</div></div></div> --}}
                    <div class="col-6"><div class="mini-stat"><div class="kpi-title">Pending</div><div class="fw-semibold">{{ $quotation['pending'] ?? 0 }}</div></div></div>
                    {{-- <div class="col-6"><div class="mini-stat"><div class="kpi-title">Sent</div><div class="fw-semibold">{{ $quotation['sent'] ?? 0 }}</div></div></div> --}}
                    <div class="col-6"><div class="mini-stat"><div class="kpi-title">Approved</div><div class="fw-semibold text-success">{{ $quotation['approved'] ?? 0 }}</div></div></div>
                    {{-- <div class="col-6"><div class="mini-stat"><div class="kpi-title">Rejected</div><div class="fw-semibold text-danger">{{ $quotation['rejected'] ?? 0 }}</div></div></div> --}}
                    <div class="col-6"><div class="mini-stat"><div class="kpi-title">Quote to Order %</div><div class="fw-semibold">{{ number_format((float) ($quotation['conversion_rate'] ?? 0), 1) }}%</div></div></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-xl-6">
            <div class="sales-card p-3">
                <div class="section-title">Collection & Outstanding Summary</div>
                <div class="row g-2 mb-2">
                    <div class="col-6"><div class="mini-stat"><div class="kpi-title">Total Outstanding</div><div class="fw-semibold">&#8377;{{ number_format((float) ($collection['total_outstanding'] ?? 0), 0) }}</div></div></div>
                    <div class="col-6"><div class="mini-stat border-danger"><div class="kpi-title text-danger">Overdue Amount</div><div class="fw-semibold text-danger">&#8377;{{ number_format((float) ($collection['overdue_amount'] ?? 0), 0) }}</div></div></div>
                    <div class="col-6"><div class="mini-stat"><div class="kpi-title">Upcoming Due (7D)</div><div class="fw-semibold">&#8377;{{ number_format((float) ($collection['upcoming_due'] ?? 0), 0) }}</div></div></div>
                    <div class="col-6"><div class="mini-stat"><div class="kpi-title">Collection This Month</div><div class="fw-semibold text-success">&#8377;{{ number_format((float) ($collection['collection_this_month'] ?? 0), 0) }}</div></div></div>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <small class="text-muted">Collection Target vs Achievement</small>
                    <small class="fw-semibold">{{ number_format((float) ($collection['collection_target_pct'] ?? 0), 1) }}%</small>
                </div>
                <div class="progress mb-1">
                    <div class="progress-bar {{ $collectionBarClass }}" style="width: {{ min((float) ($collection['collection_target_pct'] ?? 0), 100) }}%"></div>
                </div>
                <small class="text-muted">Target: &#8377;{{ number_format((float) ($collection['collection_target'] ?? 0), 0) }}</small>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="sales-card p-3">
                <div class="section-title">Customer Insights</div>
                <div class="row g-2 mb-2">
                    <div class="col-4"><div class="mini-stat"><div class="kpi-title">Assigned</div><div class="fw-semibold">{{ $customers['total_assigned_customers'] ?? 0 }}</div></div></div>
                    <div class="col-4"><div class="mini-stat"><div class="kpi-title">Active</div><div class="fw-semibold text-success">{{ $customers['active_customers'] ?? 0 }}</div></div></div>
                    <div class="col-4"><div class="mini-stat"><div class="kpi-title">Inactive (90D)</div><div class="fw-semibold text-danger">{{ $customers['inactive_customers'] ?? 0 }}</div></div></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Top Customer</th>
                                <th>Orders</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($customers['top_customers'] ?? []) as $cust)
                                <tr>
                                    <td>{{ $cust->customer_name }}</td>
                                    <td>{{ $cust->total_orders }}</td>
                                    <td class="text-end">&#8377;{{ number_format((float) $cust->total_revenue, 0) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">No customer revenue data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-xl-8">
            <div class="sales-card p-3">
                <div class="section-title">Follow-up & Activity Tracker</div>
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-2"><div class="mini-stat"><div class="kpi-title">Today FU</div><div class="fw-semibold">{{ $activity['today_followups'] ?? 0 }}</div></div></div>
                    <div class="col-6 col-md-2"><div class="mini-stat"><div class="kpi-title">Overdue FU</div><div class="fw-semibold text-danger">{{ $activity['overdue_followups'] ?? 0 }}</div></div></div>
                    <div class="col-6 col-md-2"><div class="mini-stat"><div class="kpi-title">Meetings</div><div class="fw-semibold">{{ $activity['meetings_scheduled'] ?? 0 }}</div></div></div>
                    <div class="col-6 col-md-2"><div class="mini-stat"><div class="kpi-title">Calls</div><div class="fw-semibold">{{ $activity['calls_logged'] ?? 0 }}</div></div></div>
                    <div class="col-6 col-md-2"><div class="mini-stat"><div class="kpi-title">Visits</div><div class="fw-semibold">{{ $activity['visits_logged'] ?? 0 }}</div></div></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>Next Date</th>
                                <th>Status</th>
                                <th>Discussion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lead_cur_month_follow_all_list as $item)
                                @php
                                    $nextDate = \Carbon\Carbon::parse($item->next_date);
                                    $statusText = $nextDate->isToday() ? 'Today' : ($nextDate->isPast() ? 'Overdue' : 'Upcoming');
                                    $statusClass = $nextDate->isToday() ? 'warning' : ($nextDate->isPast() ? 'danger' : 'success');
                                @endphp
                                <tr>
                                    <td>{{ $item->getLeadDetail->name ?? 'N/A' }}</td>
                                    <td>{{ $nextDate->format('d M Y') }}</td>
                                    <td><span class="badge bg-{{ $statusClass }}-subtle text-{{ $statusClass }}">{{ $statusText }}</span></td>
                                    <td class="discussion-note">{{ \Illuminate\Support\Str::words($item->chat ?? '', 12, '...') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No follow-up records.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="sales-card p-3">
                <div class="section-title">Advanced KPI</div>
                <div class="d-grid gap-2">
                    <div class="mini-stat d-flex justify-content-between"><span>AOV</span><strong>&#8377;{{ number_format((float) ($advanced['avg_order_value'] ?? 0), 0) }}</strong></div>
                    <div class="mini-stat d-flex justify-content-between"><span>Repeat Customer Rate</span><strong>{{ number_format((float) ($advanced['repeat_customer_rate'] ?? 0), 1) }}%</strong></div>
                    <div class="mini-stat d-flex justify-content-between"><span>Lead Conversion Rate</span><strong>{{ number_format((float) ($advanced['lead_conversion_rate'] ?? 0), 1) }}%</strong></div>
                    <div class="mini-stat d-flex justify-content-between"><span>Avg Collection Days</span><strong>{{ number_format((float) ($advanced['avg_collection_days'] ?? 0), 1) }}</strong></div>
                    <div class="mini-stat d-flex justify-content-between"><span>Dispatch Delay %</span><strong>{{ number_format((float) ($advanced['dispatch_delay_pct'] ?? 0), 1) }}%</strong></div>
                    <div class="mini-stat d-flex justify-content-between"><span>Sales Growth %</span><strong class="{{ ((float) ($advanced['sales_growth_pct'] ?? 0)) < 0 ? 'text-danger' : 'text-success' }}">{{ number_format((float) ($advanced['sales_growth_pct'] ?? 0), 1) }}%</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12">
            {{-- <div class="sales-card p-3">
                <div class="section-title">Inventory Awareness</div>
                <div class="row g-3">
                    <div class="col-12 col-lg-4">
                        <h6 class="mb-2">Fast-moving Products</h6>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light"><tr><th>Product</th><th>Sold</th><th>Stock</th></tr></thead>
                                <tbody>
                                    @forelse(($inventory['fast_moving_products'] ?? []) as $row)
                                        <tr>
                                            <td>{{ $row->product_name }}</td>
                                            <td>{{ $row->sold_qty }}</td>
                                            <td>{{ $row->available_qty }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-muted text-center py-3">No fast-moving data.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <h6 class="mb-2">Low Stock Alerts</h6>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light"><tr><th>Product</th><th>Available</th><th>Min</th></tr></thead>
                                <tbody>
                                    @forelse(($inventory['low_stock_alerts'] ?? []) as $row)
                                        <tr>
                                            <td>{{ $row->product_name }}</td>
                                            <td><span class="badge bg-danger-subtle text-danger">{{ $row->available_qty }}</span></td>
                                            <td>{{ $row->min_qty }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-muted text-center py-3">No low-stock alerts.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <h6 class="mb-2">Backordered Products</h6>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light"><tr><th>Product</th><th>Demand</th><th>Stock</th></tr></thead>
                                <tbody>
                                    @forelse(($inventory['backordered_products'] ?? []) as $row)
                                        <tr>
                                            <td>{{ $row->product_name }}</td>
                                            <td>{{ $row->demand_qty }}</td>
                                            <td><span class="badge bg-warning-subtle text-warning">{{ $row->available_qty }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-muted text-center py-3">No backordered items.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const trendLabels = @json($charts['trend_labels'] ?? []);
        const trendValues = @json($charts['trend_values'] ?? []);
        const productLabels = @json($charts['product_labels'] ?? []);
        const productValues = @json($charts['product_values'] ?? []);
        const growthLabels = @json($charts['growth_labels'] ?? []);
        const growthValues = @json($charts['growth_values'] ?? []);
        const performanceLabels = @json($target['monthly_performance_labels'] ?? []);
        const performanceValues = @json($target['monthly_performance_values'] ?? []);

        const trendEl = document.querySelector('#sales-trend-chart');
        if (trendEl) {
            new ApexCharts(trendEl, {
                chart: { type: 'line', height: 300, toolbar: { show: false } },
                series: [{ name: 'Sales', data: trendValues }],
                xaxis: { categories: trendLabels },
                stroke: { curve: 'smooth', width: 3 },
                colors: ['#198754'],
                yaxis: { labels: { formatter: (val) => 'Rs ' + Number(val).toLocaleString() } }
            }).render();
        }

        const productEl = document.querySelector('#product-wise-chart');
        if (productEl) {
            new ApexCharts(productEl, {
                chart: { type: 'bar', height: 280, toolbar: { show: false } },
                series: [{ name: 'Revenue', data: productValues }],
                xaxis: { categories: productLabels },
                plotOptions: { bar: { borderRadius: 4, horizontal: true } },
                colors: ['#0d6efd']
            }).render();
        }

        const growthEl = document.querySelector('#mom-growth-chart');
        if (growthEl) {
            new ApexCharts(growthEl, {
                chart: { type: 'bar', height: 280, toolbar: { show: false } },
                series: [{ name: 'Growth %', data: growthValues }],
                xaxis: { categories: growthLabels },
                colors: ['#fd7e14'],
                dataLabels: { enabled: true, formatter: (val) => val + '%' }
            }).render();
        }

        const targetEl = document.querySelector('#target-performance-chart');
        if (targetEl) {
            new ApexCharts(targetEl, {
                chart: { type: 'area', height: 170, sparkline: { enabled: true } },
                series: [{ name: 'Monthly Performance', data: performanceValues }],
                xaxis: { categories: performanceLabels },
                stroke: { curve: 'smooth', width: 2 },
                colors: ['#20c997'],
                fill: { opacity: 0.2 }
            }).render();
        }
    });
</script>
@endsection
