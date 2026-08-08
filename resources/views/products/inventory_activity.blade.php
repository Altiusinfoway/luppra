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
                            <span class="summary-label">Total Logs</span>
                            <h3 class="mb-1">{{ number_format($summary['total_logs'] ?? 0) }}</h3>
                            <p class="text-muted mb-0">All recorded inventory movements.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="summary-card h-100">
                        <div class="card-body">
                            <span class="summary-label">Added Entries</span>
                            <h3 class="mb-1 text-success">{{ number_format($summary['added_logs'] ?? 0) }}</h3>
                            <p class="text-muted mb-0">Stock increase events across products.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="summary-card h-100">
                        <div class="card-body">
                            <span class="summary-label">Deducted Entries</span>
                            <h3 class="mb-1 text-danger">{{ number_format($summary['deducted_logs'] ?? 0) }}</h3>
                            <p class="text-muted mb-0">Stock reduction events across products.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="summary-card h-100">
                        <div class="card-body">
                            <span class="summary-label">Net Change</span>
                            <h3 class="mb-1 {{ ($summary['net_change'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format((float) ($summary['net_change'] ?? 0), 2) }}
                            </h3>
                            <p class="text-muted mb-0">Overall net inventory movement.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="shell-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
                        <div>
                            <h5 class="mb-1">All Inventory Logs</h5>
                            <p class="text-muted mb-0">Search by product, SKU, user, or activity message.</p>
                        </div>
                        <form method="GET" action="{{ route('products.inventory_activity') }}" class="d-flex gap-2 flex-wrap">
                            <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Search product, SKU, user..." style="min-width:260px;">
                            <button type="submit" class="btn btn-primary btn-sm">Search</button>
                            @if($search !== '')
                                <a href="{{ route('products.inventory_activity') }}" class="btn btn-light btn-sm">Clear</a>
                            @endif
                        </form>
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
                        <div class="mt-3">
                            {{ $stockActivities->onEachSide(1)->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
