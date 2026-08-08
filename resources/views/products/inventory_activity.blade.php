@extends('layouts.app')

@section('page-css')
    <style>
        .inventory-activity-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .inventory-activity-suite .hero-card,
        .inventory-activity-suite .summary-card,
        .inventory-activity-suite .shell-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 24px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.06);
        }

        .inventory-activity-suite .hero-card {
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
                linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        .inventory-activity-suite .eyebrow {
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

        .inventory-activity-suite .summary-label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .inventory-activity-suite .activity-table thead th {
            background: #f8fafc;
            color: #475569;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            vertical-align: middle;
        }

        .inventory-activity-suite .activity-table td {
            vertical-align: top;
        }

        .inventory-activity-suite .filter-shell {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 1rem;
        }

        .inventory-activity-suite .filter-label {
            display: block;
            margin-bottom: 0.35rem;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .inventory-activity-suite .inventory-pagination .pagination {
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 0;
        }

        .inventory-activity-suite .inventory-pagination .page-item .page-link {
            min-width: 40px;
            height: 40px;
            border: 1px solid #dbe4f0;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #475569;
            background: #fff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
        }

        .inventory-activity-suite .inventory-pagination .page-item.active .page-link {
            color: #fff;
            background: #2563eb;
            border-color: #2563eb;
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.2);
        }

        .inventory-activity-suite .inventory-pagination .page-item.disabled .page-link {
            color: #94a3b8;
            background: #f8fafc;
            border-color: #e2e8f0;
            box-shadow: none;
        }

        @media (max-width: 767.98px) {
            .inventory-activity-suite .inventory-pagination .pagination {
                justify-content: center;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content inventory-activity-suite">
        <div class="container-fluid">
            <div class="hero-card mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-8">
                            <span class="eyebrow">Inventory Activity</span>
                            <h2 class="mt-3 mb-2">Global Inventory Activity</h2>
                            <p class="text-muted mb-0">Track which product stock was added or deducted, who updated it, and the exact stock movement from one screen.</p>
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex justify-content-lg-end">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
                                    <li class="breadcrumb-item active">Inventory Activity</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="summary-card h-100">
                        <div class="card-body">
                            <span class="summary-label">Filtered Logs</span>
                            <h3 class="mb-1">{{ number_format($summary['total_logs'] ?? 0) }}</h3>
                            <p class="text-muted mb-0">Inventory movements for the selected filter.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="summary-card h-100">
                        <div class="card-body">
                            <span class="summary-label">Added Quantity</span>
                            <h3 class="mb-1 text-success">{{ number_format((float) ($summary['added_qty'] ?? 0), 2) }}</h3>
                            <p class="text-muted mb-0">{{ number_format($summary['added_logs'] ?? 0) }} stock increase entries.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="summary-card h-100">
                        <div class="card-body">
                            <span class="summary-label">Deducted Quantity</span>
                            <h3 class="mb-1 text-danger">{{ number_format((float) ($summary['deducted_qty'] ?? 0), 2) }}</h3>
                            <p class="text-muted mb-0">{{ number_format($summary['deducted_logs'] ?? 0) }} stock reduction entries.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="summary-card h-100">
                        <div class="card-body">
                            <span class="summary-label">Affected Products</span>
                            <h3 class="mb-1">{{ number_format($summary['affected_products'] ?? 0) }}</h3>
                            <p class="text-muted mb-0">Unique products touched in this result set.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-4">
                    <div class="summary-card h-100">
                        <div class="card-body">
                            <span class="summary-label">Today</span>
                            <h3 class="mb-1">{{ number_format((float) ($todayStats['net_change'] ?? 0), 2) }}</h3>
                            <p class="text-muted mb-0">
                                {{ number_format($todayStats['total_logs'] ?? 0) }} logs |
                                +{{ number_format((float) ($todayStats['added_qty'] ?? 0), 2) }} /
                                -{{ number_format((float) ($todayStats['deducted_qty'] ?? 0), 2) }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4">
                    <div class="summary-card h-100">
                        <div class="card-body">
                            <span class="summary-label">This Month</span>
                            <h3 class="mb-1">{{ number_format((float) ($monthStats['net_change'] ?? 0), 2) }}</h3>
                            <p class="text-muted mb-0">
                                {{ number_format($monthStats['total_logs'] ?? 0) }} logs |
                                +{{ number_format((float) ($monthStats['added_qty'] ?? 0), 2) }} /
                                -{{ number_format((float) ($monthStats['deducted_qty'] ?? 0), 2) }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4">
                    <div class="summary-card h-100">
                        <div class="card-body">
                            <span class="summary-label">Filtered Net Change</span>
                            <h3 class="mb-1 {{ ($summary['net_change'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format((float) ($summary['net_change'] ?? 0), 2) }}
                            </h3>
                            <p class="text-muted mb-0">Net movement for the currently selected filters.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="shell-card">
                <div class="card-body p-4">
                    <div class="filter-shell mb-4">
                        <form method="GET" action="{{ route('products.inventory_activity') }}">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6 col-xl-3">
                                    <label class="filter-label" for="inventory-search">Search</label>
                                    <input type="text" class="form-control" id="inventory-search" name="search" value="{{ $search }}" placeholder="Product, SKU, user, message">
                                </div>
                                <div class="col-md-6 col-xl-2">
                                    <label class="filter-label" for="inventory-period">Period</label>
                                    <select class="form-select" id="inventory-period" name="period">
                                        <option value="all" {{ $period === 'all' ? 'selected' : '' }}>All Time</option>
                                        <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                                        <option value="this_week" {{ $period === 'this_week' ? 'selected' : '' }}>This Week</option>
                                        <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>This Month</option>
                                        <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Range</option>
                                    </select>
                                </div>
                                <div class="col-md-6 col-xl-2">
                                    <label class="filter-label" for="inventory-movement">Movement</label>
                                    <select class="form-select" id="inventory-movement" name="movement">
                                        <option value="all" {{ $movement === 'all' ? 'selected' : '' }}>All</option>
                                        <option value="added" {{ $movement === 'added' ? 'selected' : '' }}>Added</option>
                                        <option value="deducted" {{ $movement === 'deducted' ? 'selected' : '' }}>Deducted</option>
                                    </select>
                                </div>
                                <div class="col-md-6 col-xl-2">
                                    <label class="filter-label" for="inventory-date-from">Date From</label>
                                    <input type="date" class="form-control" id="inventory-date-from" name="date_from" value="{{ $dateFrom }}">
                                </div>
                                <div class="col-md-6 col-xl-2">
                                    <label class="filter-label" for="inventory-date-to">Date To</label>
                                    <input type="date" class="form-control" id="inventory-date-to" name="date_to" value="{{ $dateTo }}">
                                </div>
                                <div class="col-md-6 col-xl-1 d-grid">
                                    <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                                </div>
                                <div class="col-md-6 col-xl-12 d-flex justify-content-end">
                                    <a href="{{ route('products.inventory_activity') }}" class="btn btn-light btn-sm">Reset Filters</a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
                        <div>
                            <h5 class="mb-1">All Inventory Logs</h5>
                            <p class="text-muted mb-0">Use filters to review daily, monthly, or custom inventory movement.</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle activity-table mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Movement</th>
                                    <th>Change Qty</th>
                                    <th>Before</th>
                                    <th>After</th>
                                    <th>Updated By</th>
                                    <th>Time</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($stockActivities as $activity)
                                    @php
                                        $direction = $activity->inventory_direction ?? 'neutral';
                                        $badgeClass = $direction === 'added'
                                            ? 'bg-success-subtle text-success'
                                            : ($direction === 'deducted' ? 'bg-danger-subtle text-danger' : 'bg-light text-muted');
                                        $directionLabel = $direction === 'added'
                                            ? 'Added'
                                            : ($direction === 'deducted' ? 'Deducted' : 'No Change');
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ optional($activity->product)->name ?: 'Unknown Product' }}</div>
                                            <div class="small text-muted">SKU: {{ optional($activity->product)->sku_code ?: '-' }}</div>
                                        </td>
                                        <td><span class="badge {{ $badgeClass }}">{{ $directionLabel }}</span></td>
                                        <td>{{ number_format((float) ($activity->inventory_delta ?? 0), 2) }}</td>
                                        <td>{{ $activity->inventory_before !== null ? number_format((float) $activity->inventory_before, 2) : '-' }}</td>
                                        <td>{{ $activity->inventory_after !== null ? number_format((float) $activity->inventory_after, 2) : '-' }}</td>
                                        <td>{{ optional($activity->created_user)->name ?: 'System' }}</td>
                                        <td>{{ optional($activity->date_time)->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</td>
                                        <td class="text-muted">{{ $activity->message }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No inventory activity found yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($stockActivities->hasPages())
                        <div class="mt-3 inventory-pagination">
                            {{ $stockActivities->onEachSide(1)->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
