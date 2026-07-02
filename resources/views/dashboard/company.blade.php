@extends('layouts.app')

@section('content')
@php
    $quoteConversion = $total_lead_count > 0 ? round(($quote_total_count / $total_lead_count) * 100, 2) : 0;
    $orderConversion = $quote_total_count > 0 ? round(($order_total_count / $quote_total_count) * 100, 2) : 0;
    $leadToOrder = $total_lead_count > 0 ? round(($order_total_count / $total_lead_count) * 100, 2) : 0;

    $monthlyMomentum = $lead_cur_month > 0 ? round(($order_month_count / $lead_cur_month) * 100, 2) : 0;
@endphp

<style>
    .admin-hero {
        background: linear-gradient(115deg, #14532d, #198754);
        color: #fff;
        border-radius: 14px;
        padding: 1.1rem 1.3rem;
        margin-bottom: 1rem;
    }
    .admin-card {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(16, 24, 40, 0.05);
    }
    .kpi-box {
        padding: 1rem;
        height: 100%;
    }
    .kpi-title {
        color: #6b7280;
        text-transform: uppercase;
        font-size: 0.76rem;
        letter-spacing: .04em;
        margin-bottom: .45rem;
    }
    .kpi-value {
        font-size: 1.55rem;
        line-height: 1.2;
        font-weight: 700;
        color: #111827;
    }
    .kpi-sub {
        color: #6b7280;
        font-size: .82rem;
    }
    .metric-chip {
        border: 1px solid #dbe2e8;
        border-radius: 10px;
        padding: .65rem .8rem;
        background: #fbfcfd;
    }
    .stage-row,
    .source-row {
        margin-bottom: .9rem;
    }
    .stage-row:last-child,
    .source-row:last-child {
        margin-bottom: 0;
    }
    .progress {
        height: 8px;
        background: #edf1f5;
    }
    .table td,
    .table th {
        vertical-align: middle;
    }
    .discussion-note {
        max-width: 320px;
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
    @media (max-width: 767px) {
        .discussion-note {
            max-width: 180px;
        }
    }
</style>

<div class="page-content">
    <div class="admin-hero d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h4 class="mb-1 text-white">Admin Sales Dashboard</h4>
            <div class="opacity-75">{{ now()->format('d M Y') }} | Business overview and pipeline health</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('leads.create') }}" class="hero-action-btn hero-action-primary">
                <i class="ri-add-circle-line"></i>
                <span>New Lead</span>
            </a>
            <a href="{{ route('quotes.create') }}" class="hero-action-btn hero-action-outline">
                <i class="ri-file-list-3-line"></i>
                <span>New Quote</span>
            </a>
            <a href="{{ route('orders.index') }}" class="hero-action-btn hero-action-outline">
                <i class="ri-shopping-bag-3-line"></i>
                <span>Orders</span>
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="admin-card kpi-box">
                <div class="kpi-title">Total Leads</div>
                <div class="kpi-value">{{ number_format($total_lead_count) }}</div>
                <div class="kpi-sub">{{ number_format($lead_cur_month) }} this month | {{ number_format($today_lead_count) }} today</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="admin-card kpi-box">
                <div class="kpi-title">Follow-Ups</div>
                <div class="kpi-value">{{ number_format($lead_cur_month_follow) }}</div>
                <div class="kpi-sub">{{ number_format($lead_follow_today_count) }} scheduled today</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="admin-card kpi-box">
                <div class="kpi-title">Quotations</div>
                <div class="kpi-value">{{ number_format($quote_total_count) }}</div>
                <div class="kpi-sub">{{ number_format($quote_month_count) }} this month | {{ number_format($quote_today_count) }} today</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="admin-card kpi-box">
                <div class="kpi-title">Orders</div>
                <div class="kpi-value">{{ number_format($order_total_count) }}</div>
                <div class="kpi-sub">{{ number_format($order_month_count) }} this month | {{ number_format($order_today_count) }} today</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-xl-4">
            <div class="admin-card p-3 h-100">
                <h5 class="mb-3">Conversion Snapshot</h5>
                <div class="d-grid gap-2">
                    <div class="metric-chip d-flex justify-content-between">
                        <span>Lead to Quote</span>
                        <strong>{{ number_format($quoteConversion, 1) }}%</strong>
                    </div>
                    <div class="metric-chip d-flex justify-content-between">
                        <span>Quote to Order</span>
                        <strong>{{ number_format($orderConversion, 1) }}%</strong>
                    </div>
                    <div class="metric-chip d-flex justify-content-between">
                        <span>Lead to Order</span>
                        <strong>{{ number_format($leadToOrder, 1) }}%</strong>
                    </div>
                    <div class="metric-chip d-flex justify-content-between">
                        <span>Month Lead to Order</span>
                        <strong>{{ number_format($monthlyMomentum, 1) }}%</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">Product Sales Trend (Monthly)</h5>
                    <small class="text-muted">Current year</small>
                </div>
                <div id="line_chart_basic" style="min-height:320px;"></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-xl-6">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Team Sales Performance (Top 5)</h5>
                    <a href="{{ route('employees.index') }}" class="btn btn-sm btn-outline-success">View Team</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Sales Person</th>
                                <th>Leads</th>
                                <th>Total Value</th>
                                <th class="text-end">Map</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($top_lead_list as $ld)
                                <tr>
                                    <td>{{ $ld->name }}</td>
                                    <td>{{ number_format($ld->lead_count) }}</td>
                                    <td>&#8377;{{ number_format($ld->total_value, 0) }}</td>
                                    <td class="text-end">
                                        @if (!empty($ld->user_id) && !empty($ld->has_location_history))
                    
                                            <a href="{{ route('employees.map_index', $ld->user_id) }}" class="btn btn-sm btn-outline-primary" title="View Locations">
                                                <i class="ri-map-pin-line"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No team performance data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="admin-card p-3 h-100">
                <h5 class="mb-3">Lead Stage Distribution</h5>
                @foreach ($lead_stage_list as $stage)
                    @php
                        $count = $counts[$stage->id] ?? 0;
                        $percent = $totalLeads > 0 ? round(($count / $totalLeads) * 100, 1) : 0;
                        $displayPercent = $percent > 0 && $percent < 1 ? 1 : $percent;
                    @endphp
                    <div class="stage-row">
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $stage->name }}</span>
                            <span class="text-muted">{{ $count }} ({{ $percent }}%)</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: {{ $displayPercent }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-xl-6">
            <div class="admin-card p-3 h-100">
                <h5 class="mb-3">Lead Source Distribution</h5>
                @php
                    $totalLeadCountForSource = \App\Models\Lead::count();
                @endphp
                @foreach ($lead_source_list as $list)
                    @php
                        $leadSourceCount = \App\Models\Lead::whereRaw('FIND_IN_SET(?, sources)', [$list->id])->count();
                        $percent = $totalLeadCountForSource > 0 ? round(($leadSourceCount / $totalLeadCountForSource) * 100, 1) : 0;
                        $displayPercent = $percent > 0 && $percent < 1 ? 1 : $percent;
                    @endphp
                    <div class="source-row">
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $list->name }}</span>
                            <span class="text-muted">{{ $leadSourceCount }} ({{ $percent }}%)</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-info" style="width: {{ $displayPercent }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Upcoming Follow-Ups (Top 20)</h5>
                    <a href="{{ route('leads.list') }}" class="btn btn-sm btn-outline-success">Open Leads</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>Next Date</th>
                                <th>Priority</th>
                                <th>Follow Up By</th>
                                <th>Discussion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lead_cur_month_follow_all_list as $item)
                                @php
                                    $nextDate = \Carbon\Carbon::parse($item->next_date);
                                    $statusText = $nextDate->isToday() ? 'Today' : ($nextDate->isPast() ? 'Overdue' : 'Upcoming');
                                    $statusClass = $nextDate->isToday() ? 'warning' : ($nextDate->isPast() ? 'danger' : 'success');
                                @endphp
                                <tr>
                                    <td>{{ $item->getLeadDetail->name ?? 'N/A' }}</td>
                                    <td>{{ $nextDate->format('d M Y') }}</td>
                                    <td><span class="badge bg-{{ $statusClass }}-subtle text-{{ $statusClass }}">{{ $statusText }}</span></td>
                                    <td>{{ $item->getUser->name ?? 'N/A' }}</td>
                                    <td class="discussion-note">{{ \Illuminate\Support\Str::words($item->chat ?? '', 10, '...') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No follow-up data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
        var options = {
            chart: {
                type: 'line',
                height: 320,
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            series: @json($chartSeries),
            stroke: {
                curve: 'smooth',
                width: 3
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
            },
            yaxis: {
                title: { text: 'Total Value' }
            },
            colors: ['#0d6efd', '#198754', '#dc3545', '#ffc107', '#6610f2', '#20c997', '#6f42c1', '#fd7e14'],
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val;
                    }
                }
            },
            legend: {
                formatter: function (seriesName) {
                    return seriesName.length > 28 ? seriesName.substring(0, 28) + '...' : seriesName;
                }
            }
        };

        var chart = new ApexCharts(document.querySelector('#line_chart_basic'), options);
        chart.render();
    });
</script>
@endsection
