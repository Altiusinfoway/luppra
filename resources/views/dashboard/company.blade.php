@extends('layouts.app')

@section('page-css')
<style>
    .dashboard-suite {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
    }
    .dashboard-suite .hero-shell {
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 28px;
        background:
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
            radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
            linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
        margin-bottom: 1.25rem;
    }
    .dashboard-suite .hero-eyebrow {
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
    .dashboard-suite .hero-title {
        font-size: clamp(2rem, 3vw, 2.7rem);
        line-height: 1.05;
        letter-spacing: -0.04em;
        font-weight: 800;
        margin: 1rem 0 .45rem;
        color: #0f172a;
    }
    .dashboard-suite .hero-subtitle {
        color: #64748b;
        max-width: 720px;
        font-size: .98rem;
    }
    .dashboard-suite .hero-action-btn {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border-radius: 14px;
        font-weight: 700;
        padding: .7rem 1rem;
        border: 1px solid #dbeafe;
        background: rgba(255, 255, 255, 0.92);
        color: #0f172a;
        transition: all .18s ease-in-out;
    }
    .dashboard-suite .hero-action-btn:hover {
        transform: translateY(-1px);
        background: #ffffff;
        color: #0f172a;
    }
    .dashboard-suite .hero-action-primary {
        background: linear-gradient(135deg, #0f766e 0%, #2563eb 100%);
        border-color: transparent;
        color: #ffffff;
    }
    .dashboard-suite .hero-action-primary:hover {
        color: #ffffff;
    }
    .dashboard-suite .admin-card {
        background: rgba(255, 255, 255, 0.88);
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 22px;
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
    }
    .dashboard-suite .kpi-box {
        padding: 1.1rem 1.1rem 1rem;
        height: 100%;
    }
    .dashboard-suite .kpi-title {
        color: #64748b;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: .08em;
        font-weight: 800;
        margin-bottom: .45rem;
    }
    .dashboard-suite .kpi-value {
        font-size: 1.8rem;
        line-height: 1.1;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.03em;
    }
    .dashboard-suite .kpi-sub {
        color: #64748b;
        font-size: .84rem;
    }
    .dashboard-suite .metric-chip {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: .85rem 1rem;
        background: #f8fafc;
    }
    .dashboard-suite .stage-row,
    .dashboard-suite .source-row {
        margin-bottom: .9rem;
    }
    .dashboard-suite .stage-row:last-child,
    .dashboard-suite .source-row:last-child {
        margin-bottom: 0;
    }
    .dashboard-suite .progress {
        height: 9px;
        background: #edf2f7;
        border-radius: 999px;
    }
    .dashboard-suite .table td,
    .dashboard-suite .table th {
        vertical-align: middle;
    }
    .dashboard-suite .discussion-note {
        max-width: 320px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    @media (max-width: 767px) {
        .dashboard-suite .discussion-note {
            max-width: 180px;
        }
    }
</style>
@endsection

@section('content')
@php
    $quoteConversion = $total_lead_count > 0 ? round(($quote_total_count / $total_lead_count) * 100, 2) : 0;
    $orderConversion = $quote_total_count > 0 ? round(($order_total_count / $quote_total_count) * 100, 2) : 0;
    $leadToOrder = $total_lead_count > 0 ? round(($order_total_count / $total_lead_count) * 100, 2) : 0;

    $monthlyMomentum = $lead_cur_month > 0 ? round(($order_month_count / $lead_cur_month) * 100, 2) : 0;
    $inventoryOverview = $inventory_overview ?? [];
    $categoryBreakdown = $inventoryOverview['category_breakdown'] ?? collect();
    $inventoryCategoryOptions = $inventory_category_options ?? collect();
    $selectedInventoryCategoryIds = collect($selected_inventory_category_ids ?? [])->map(fn ($value) => (int) $value)->all();
    $hasSelectedInventoryCategories = count($selectedInventoryCategoryIds) > 0;
@endphp
<div class="page-content dashboard-suite">
    <div class="hero-shell">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="hero-eyebrow">Operations Dashboard</span>
                    <h1 class="hero-title">Admin Sales Dashboard</h1>
                    <div class="hero-subtitle">{{ now()->format('d M Y') }} | Business overview, conversion health, and team sales movement in one place.</div>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex justify-content-lg-end flex-wrap gap-2">
                        <a href="{{ route('leads.create') }}" class="hero-action-btn hero-action-primary">
                            <i class="ri-add-circle-line"></i>
                            <span>New Lead</span>
                        </a>
                        <a href="{{ route('quotes.create') }}" class="hero-action-btn">
                            <i class="ri-file-list-3-line"></i>
                            <span>New Quote</span>
                        </a>
                        <a href="{{ route('orders.index') }}" class="hero-action-btn">
                            <i class="ri-shopping-bag-3-line"></i>
                            <span>Orders</span>
                        </a>
                    </div>
                </div>
            </div>
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
        <div class="col-12">
            <div class="admin-card p-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6 col-xl-4">
                        <div class="kpi-title mb-2">Category Wise Filter</div>
                        <div class="d-flex gap-2">
                            <select class="form-select" id="dashboard-category-filter" multiple size="6">
                                @foreach($inventoryCategoryOptions as $categoryId => $categoryName)
                                    <option value="{{ $categoryId }}" {{ in_array((int) $categoryId, $selectedInventoryCategoryIds, true) ? 'selected' : '' }}>
                                        {{ $categoryName }}
                                    </option>
                                @endforeach
                            </select>
                            @if($hasSelectedInventoryCategories)
                                <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm">Reset</a>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-8">
                        <div class="text-muted small">
                            Filter the product-focused dashboard by multiple categories or subcategories to update available stock, active product counts, category inventory, and product trend on this screen.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="admin-card kpi-box">
                <div class="kpi-title">Available Inventory</div>
                <div class="kpi-value">{{ number_format((float) ($inventoryOverview['total_available_stock'] ?? 0), 0) }}</div>
                <div class="kpi-sub">Total stock available {{ $hasSelectedInventoryCategories ? 'for the selected categories.' : 'across all products.' }}</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="admin-card kpi-box">
                <div class="kpi-title">Total Products</div>
                <div class="kpi-value">{{ number_format((int) ($inventoryOverview['total_products'] ?? 0)) }}</div>
                <div class="kpi-sub">{{ $hasSelectedInventoryCategories ? 'Master products in the selected categories.' : 'All master products currently in catalog.' }}</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="admin-card kpi-box">
                <div class="kpi-title">Active Products</div>
                <div class="kpi-value">{{ number_format((int) ($inventoryOverview['active_products'] ?? 0)) }}</div>
                <div class="kpi-sub">Products currently active for operations.</div>
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
                    <small class="text-muted">{{ $hasSelectedInventoryCategories ? 'Current year | category filtered' : 'Current year' }}</small>
                </div>
                <div id="line_chart_basic" style="min-height:320px;"></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Category Wise Inventory</h5>
                    <span class="text-muted small">Stock grouped by product category</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Category</th>
                                <th>Products</th>
                                <th>Active Products</th>
                                <th class="text-end">Available Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categoryBreakdown as $category)
                                <tr>
                                    <td>
                                        <a href="{{ route('products.index', ['status' => 'all', 'category_id' => $category->category_id]) }}" class="text-decoration-none fw-semibold">
                                            {{ $category->category_name }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('products.index', ['status' => 'all', 'category_id' => $category->category_id]) }}" class="text-decoration-none">
                                            {{ number_format((int) ($category->product_count ?? 0)) }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('products.index', ['status' => 'active', 'category_id' => $category->category_id]) }}" class="text-decoration-none">
                                            {{ number_format((int) ($category->active_product_count ?? 0)) }}
                                        </a>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('products.index', ['status' => 'all', 'category_id' => $category->category_id]) }}" class="text-decoration-none">
                                            {{ number_format((float) ($category->stock_qty ?? 0), 0) }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No product inventory found yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
        const categoryFilter = document.getElementById('dashboard-category-filter');
        if (categoryFilter) {
            categoryFilter.addEventListener('change', function () {
                const url = new URL(@json(route('dashboard')), window.location.origin);
                url.searchParams.delete('inventory_category_ids[]');
                Array.from(this.selectedOptions).forEach(function (option) {
                    if (option.value) {
                        url.searchParams.append('inventory_category_ids[]', option.value);
                    }
                });
                window.location.href = url.toString();
            });
        }

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
