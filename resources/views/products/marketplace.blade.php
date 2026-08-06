@extends('layouts.app')

@section('page-css')
    <style>
        .marketplace-dashboard {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.7) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .marketplace-dashboard .hero-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 28px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
                linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            color: #0f172a;
            overflow: visible;
            position: relative;
            box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
        }

        .marketplace-dashboard .hero-card::after {
            content: '';
            position: absolute;
            inset: auto -60px -80px auto;
            width: 220px;
            height: 220px;
            background: rgba(37, 99, 235, 0.08);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        .marketplace-dashboard .hero-card .card-body {
            position: relative;
            z-index: 1;
            overflow: visible;
        }

        .marketplace-dashboard .stat-card,
        .marketplace-dashboard .listing-card,
        .marketplace-dashboard .report-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 20px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .marketplace-dashboard .stat-label {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #64748b;
        }

        .marketplace-dashboard .listing-card {
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .marketplace-dashboard .listing-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
        }

        .marketplace-dashboard .platform-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .marketplace-dashboard .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .marketplace-dashboard .info-box {
            border-radius: 16px;
            padding: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .marketplace-dashboard .info-box .label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #64748b;
            margin-bottom: 4px;
        }

        .marketplace-dashboard .table td,
        .marketplace-dashboard .table th {
            vertical-align: middle;
        }

        .marketplace-dashboard .hero-eyebrow {
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

        .marketplace-dashboard .empty-state {
            border: 1px dashed #bfdbfe;
            border-radius: 24px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
            padding: 42px 24px;
        }

        .marketplace-dashboard .empty-state-icon {
            width: 76px;
            height: 76px;
            margin: 0 auto 16px;
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
            color: #2563eb;
            font-size: 2rem;
            box-shadow: 0 16px 30px rgba(37, 99, 235, 0.14);
        }

        .marketplace-dashboard .status-banner {
            border: 1px solid #dce4ee;
            border-radius: 18px;
            padding: 1rem 1.15rem;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        }

        .marketplace-dashboard .status-banner.status-warning {
            background: linear-gradient(135deg, #fffbeb 0%, #fff7d6 100%);
            border-color: #fde68a;
            color: #92400e;
        }

        .marketplace-dashboard .status-note {
            border: 1px solid #dbe4f0;
            border-radius: 16px;
            padding: .9rem 1rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
            color: #475569;
        }

        .marketplace-dashboard .status-note .note-label {
            display: block;
            margin-bottom: .3rem;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .marketplace-dashboard .status-note.status-info {
            border-color: #bfdbfe;
            background: linear-gradient(180deg, #eff6ff 0%, #f8fbff 100%);
            color: #1e3a8a;
        }

        .marketplace-dashboard .status-note.status-neutral {
            border-color: #dbe4f0;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            color: #475569;
        }

        .marketplace-dashboard .table-shell {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            overflow: hidden;
            background: #fff;
        }

        .marketplace-dashboard .table-shell .table-light th {
            background: #f8fafc;
            color: #475569;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .marketplace-dashboard .dropdown,
        .marketplace-dashboard .btn-group {
            position: relative;
        }

        .marketplace-dashboard .dropdown-menu {
            z-index: 1085;
        }

        @media (max-width: 767.98px) {
            .marketplace-dashboard .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content marketplace-dashboard">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="hero-card mb-4">
                        <div class="card-body p-4 p-lg-5 position-relative">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-7">
                                    <span class="hero-eyebrow">Marketplace Overview</span>
                                    <h2 class="mb-2 mt-3">Marketplace Listings</h2>
                                    <p class="mb-2 text-muted">{{ $product->name }}</p>
                                    <p class="mb-0 text-muted">Master SKU: {{ $product->sku_code }}. Track listing coverage, marketplace sales, base price, and profit performance for this master SKU from one clean dashboard.</p>
                                </div>
                                <div class="col-lg-5">
                                    <div class="d-flex justify-content-lg-end align-items-center gap-2 flex-wrap">
                                        <a class="btn btn-primary btn-sm open-product-modal" href="javascript:void(0);"
                                            data-size="lg" data-url="{{ route('products.edit', $product->id) }}" data-ajax-popup="true">
                                            <i class="ri-pencil-line align-middle me-1"></i>Edit Product
                                        </a>
                                        <div class="dropdown">
                                            <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Listing Menu
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('products.marketplace.listings.create', $product->id) }}">
                                                        <i class="ri-add-line align-middle me-2"></i>Add Listing
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('products.activity', $product->id) }}">
                                                        <i class="ri-history-line align-middle me-2"></i>View Activity
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('products.marketplace.accounts.index') }}">
                                                        <i class="ri-store-3-line align-middle me-2"></i>Manage Accounts
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
                                            <li class="breadcrumb-item active">Marketplace</li>
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
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="stat-label mb-2">Master Stock</div>
                            <h3 class="mb-1">{{ number_format($stats['master_stock']) }}</h3>
                            <p class="text-muted mb-0">Primary source inventory for this SKU.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="stat-label mb-2">Listings</div>
                            <h3 class="mb-1">{{ number_format($stats['total_listings']) }}</h3>
                            <p class="text-muted mb-0">{{ number_format($stats['active_listings']) }} active listings connected.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="stat-label mb-2">Base Price</div>
                            <h3 class="mb-1">
                                @if($stats['base_unit_price'] > 0)
                                    Rs. {{ number_format($stats['base_unit_price'], 2) }}
                                @else
                                    -
                                @endif
                            </h3>
                            <p class="text-muted mb-0">Uses internal product cost for profit calculation.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="stat-label mb-2">Marketplace Sold</div>
                            <h3 class="mb-1">{{ number_format($stats['marketplace_sold_qty'], 2) }}</h3>
                            <p class="text-muted mb-0">Qty sold from marketplace-linked orders.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="stat-label mb-2">Marketplace Revenue</div>
                            <h3 class="mb-1">Rs. {{ number_format($stats['marketplace_revenue'], 2) }}</h3>
                            <p class="text-muted mb-0">Gross sales tied to marketplace listings.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="stat-label mb-2">Estimated Cost</div>
                            <h3 class="mb-1">Rs. {{ number_format($stats['marketplace_estimated_cost'], 2) }}</h3>
                            <p class="text-muted mb-0">Base price multiplied by sold quantity.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="stat-label mb-2">Estimated Profit</div>
                            <h3 class="mb-1 {{ $stats['marketplace_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                Rs. {{ number_format($stats['marketplace_profit'], 2) }}
                            </h3>
                            <p class="text-muted mb-0">Revenue minus estimated cost.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="stat-label mb-2">Profit Margin</div>
                            <h3 class="mb-1">{{ number_format($stats['marketplace_margin_percent'], 2) }}%</h3>
                            <p class="text-muted mb-0">Estimated profit percentage on marketplace sales.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-xl-8">
                    <div class="card report-card h-100">
                        <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="card-title mb-1">Listings</h5>
                                <p class="text-muted mb-0">Read-only marketplace listing view for this product.</p>
                            </div>
                        </div>
                        <div class="card-body">
                            @if(empty($marketplaceEnabled))
                                <div class="status-banner status-warning mb-0">Marketplace listing support is currently unavailable on this database.</div>
                            @else
                                <div class="row g-3">
                                    @forelse ($product->marketplaceListings as $listing)
                                        <div class="col-lg-6">
                                            <div class="card listing-card h-100 mb-0">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                                        <div>
                                                            <span class="platform-chip">{{ $listing->platform }}</span>
                                                            <h5 class="mt-3 mb-1">{{ $listing->listing_title }}</h5>
                                                            <div class="text-muted small">{{ $listing->account_name ?: 'Primary Account' }}</div>
                                                            <div class="text-muted">{{ $listing->platform_sku }}</div>
                                                        </div>
                                                        <span class="badge bg-light text-dark">{{ ucfirst($listing->listing_status) }}</span>
                                                    </div>

                                                    <div class="info-grid mb-3">
                                                        <div class="info-box">
                                                            <span class="label">Selling Price</span>
                                                            <strong>Rs. {{ number_format((float) $listing->selling_price, 2) }}</strong>
                                                        </div>
                                                        <div class="info-box">
                                                            <span class="label">MRP</span>
                                                            <strong>Rs. {{ number_format((float) $listing->mrp, 2) }}</strong>
                                                        </div>
                                                        <div class="info-box">
                                                            <span class="label">Allocated Stock</span>
                                                            <strong>{{ number_format((int) ($listing->allocated_stock ?? 0)) }}</strong>
                                                        </div>
                                                        <div class="info-box">
                                                            <span class="label">Available Stock</span>
                                                            <strong>{{ number_format((int) $listing->available_stock) }}</strong>
                                                        </div>
                                                    </div>

                                                    @php
                                                        $analytics = $listingAnalytics->firstWhere('listing.id', $listing->id);
                                                    @endphp

                                                    <div class="info-grid mb-3">
                                                        <div class="info-box">
                                                            <span class="label">Sold Qty</span>
                                                            <strong>{{ number_format((float) ($analytics->sold_qty ?? 0), 2) }}</strong>
                                                        </div>
                                                        <div class="info-box">
                                                            <span class="label">Revenue</span>
                                                            <strong>Rs. {{ number_format((float) ($analytics->revenue ?? 0), 2) }}</strong>
                                                        </div>
                                                        <div class="info-box">
                                                            <span class="label">Base Price</span>
                                                            <strong>
                                                                @if(($analytics->base_unit_price ?? 0) > 0)
                                                                    Rs. {{ number_format((float) $analytics->base_unit_price, 2) }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </strong>
                                                        </div>
                                                        <div class="info-box">
                                                            <span class="label">Profit</span>
                                                            <strong class="{{ ($analytics->profit ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                                                Rs. {{ number_format((float) ($analytics->profit ?? 0), 2) }}
                                                            </strong>
                                                        </div>
                                                    </div>

                                                    <div class="row g-2 text-muted small mb-3">
                                                        <div class="col-sm-6">Pack Size: <span class="text-dark">{{ $listing->pack_size ?: '-' }}</span></div>
                                                        <div class="col-sm-6">Account: <span class="text-dark">{{ $listing->account_name ?: 'Primary Account' }}</span></div>
                                                        <div class="col-sm-6">Fulfillment: <span class="text-dark">{{ $listing->fulfillment_type ?: '-' }}</span></div>
                                                        <div class="col-sm-6">Marketplace Item ID: <span class="text-dark">{{ $listing->marketplace_item_id ?: '-' }}</span></div>
                                                        <div class="col-sm-6">Reserved Stock: <span class="text-dark">{{ number_format((int) ($listing->reserved_stock ?? 0)) }}</span></div>
                                                        <div class="col-sm-6">Orders: <span class="text-dark">{{ number_format((int) ($analytics->order_count ?? 0)) }}</span></div>
                                                        <div class="col-sm-6">Margin: <span class="text-dark">{{ isset($analytics->margin_percent) && $analytics->margin_percent !== null ? number_format((float) $analytics->margin_percent, 2) . '%' : '-' }}</span></div>
                                                        <div class="col-sm-6">Analytics Source: <span class="text-dark">{{ strtoupper($analytics->analytics_source ?? 'internal') }}</span></div>
                                                        <div class="col-sm-6">Last API Sync: <span class="text-dark">{{ !empty($analytics->external_last_synced_at) ? $analytics->external_last_synced_at->format('d M Y h:i A') : '-' }}</span></div>
                                                    </div>

                                                    @if(!empty($analytics->external_sync_note))
                                                        <div class="status-note status-neutral mb-0 mt-2 small">
                                                            <span class="note-label">API sync note</span>
                                                            {{ $analytics->external_sync_note }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12">
                                            <div class="text-center empty-state">
                                                <div class="empty-state-icon">
                                                    <i class="ri-store-2-line"></i>
                                                </div>
                                                <h5>No listings added yet</h5>
                                                <p class="text-muted mb-3">Create your first Amazon or Flipkart listing for this master product.</p>
                                                <a href="{{ route('products.marketplace.listings.create', $product->id) }}" class="btn btn-primary">Add Listing</a>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card report-card h-100">
                        <div class="card-header border-0 pb-0">
                            <h5 class="card-title mb-1">Marketplace Snapshot</h5>
                            <p class="text-muted mb-0">Operational summary across connected listings.</p>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <div class="info-box h-100">
                                        <span class="label">Allocated Stock</span>
                                        <strong class="fs-5">{{ number_format($stats['allocated_stock']) }}</strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box h-100">
                                        <span class="label">Reserved Stock</span>
                                        <strong class="fs-5">{{ number_format($stats['reserved_stock']) }}</strong>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="info-box">
                                        <span class="label">Derived Available Listing Stock</span>
                                        <strong class="fs-5">{{ number_format($stats['available_listing_stock']) }}</strong>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="info-box">
                                        <span class="label">API Connected Listings</span>
                                        <strong class="fs-5">{{ number_format($stats['api_connected_listings']) }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive table-shell">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Platform</th>
                                            <th>Listings</th>
                                            <th>Sold Qty</th>
                                            <th>Revenue</th>
                                            <th>Profit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($platformAnalytics as $platformRow)
                                            <tr>
                                                <td>{{ ucfirst($platformRow->platform) }}</td>
                                                <td>{{ $platformRow->listing_count }}</td>
                                                <td>{{ number_format($platformRow->sold_qty, 2) }}</td>
                                                <td>Rs. {{ number_format($platformRow->revenue, 2) }}</td>
                                                <td class="{{ $platformRow->profit >= 0 ? 'text-success' : 'text-danger' }}">Rs. {{ number_format($platformRow->profit, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-3">No marketplace analytics available yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="status-note status-info mt-3 mb-0">
                                <span class="note-label">Analytics logic</span>
                                When Amazon or Flipkart sync metrics are present for a listing, this dashboard uses those synced sales numbers for sold quantity, revenue, cost, profit, and margin. Otherwise it falls back to mapped internal orders.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="card report-card h-100">
                        <div class="card-header border-0 pb-0">
                            <h5 class="card-title mb-1">Recent Quote Usage</h5>
                            <p class="text-muted mb-0">Latest quotes that used this product or its marketplace listings.</p>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive table-shell">
                                <table class="table table-striped align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Quote</th>
                                            <th>Listing</th>
                                            <th>Qty</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentQuotes as $quoteRow)
                                            <tr>
                                                <td>{{ !empty($quoteRow->quote_date) ? \Carbon\Carbon::parse($quoteRow->quote_date)->format('d M Y') : '-' }}</td>
                                                <td>
                                                    <a href="{{ route('quotes.edit', $quoteRow->quote_id) }}">{{ $quoteRow->quote_code ?? ('Quote #' . $quoteRow->quote_id) }}</a>
                                                    <div class="text-muted small">{{ $quoteRow->customer_name ?? 'Customer not set' }}</div>
                                                </td>
                                                <td>
                                                    @if(!empty($quoteRow->platform_sku))
                                                        <div>{{ strtoupper($quoteRow->platform ?? '') }} | {{ $quoteRow->platform_sku }}</div>
                                                        <div class="text-muted small">{{ $quoteRow->account_name ?: 'Primary Account' }}</div>
                                                        <div class="text-muted small">{{ $quoteRow->listing_title }}</div>
                                                    @else
                                                        <span class="text-muted">Master SKU only</span>
                                                    @endif
                                                </td>
                                                <td>{{ number_format((float) $quoteRow->qty, 2) }}</td>
                                                <td>Rs. {{ number_format((float) $quoteRow->total, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-3">No quote activity found for this product yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card report-card h-100">
                        <div class="card-header border-0 pb-0">
                            <h5 class="card-title mb-1">Recent Order Usage</h5>
                            <p class="text-muted mb-0">Recent order lines mapped back to this master SKU.</p>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive table-shell">
                                <table class="table table-striped align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Order</th>
                                            <th>Source</th>
                                            <th>Qty</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentOrders as $orderRow)
                                            <tr>
                                                <td>{{ !empty($orderRow->order_date) ? \Carbon\Carbon::parse($orderRow->order_date)->format('d M Y') : '-' }}</td>
                                                <td>
                                                    <a href="{{ route('orders.view', $orderRow->order_id) }}">{{ $orderRow->order_number ?? ('Order #' . $orderRow->order_id) }}</a>
                                                    <div class="text-muted small">{{ $orderRow->customer_name ?? 'Customer not set' }}</div>
                                                </td>
                                                <td>
                                                    <div>{{ ucfirst($orderRow->order_source_type ?? 'manual') }}</div>
                                                    @if(!empty($orderRow->platform_sku))
                                                        <div class="text-muted small">{{ strtoupper($orderRow->platform ?? '') }} | {{ $orderRow->platform_sku }}</div>
                                                        <div class="text-muted small">{{ $orderRow->account_name ?: 'Primary Account' }}</div>
                                                    @endif
                                                </td>
                                                <td>{{ number_format((float) $orderRow->qty, 2) }}</td>
                                                <td>Rs. {{ number_format((float) $orderRow->total, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-3">No order activity found for this product yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
