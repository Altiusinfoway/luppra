@extends('layouts.app')

@section('page-css')
    <style>
        .partial-inventory-page {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.92) 0%, rgba(241, 245, 249, 0.55) 100%);
        }

        .partial-hero,
        .partial-card {
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.06);
        }

        .partial-hero {
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(14, 116, 144, 0.12), transparent 32%),
                #fff;
        }

        .partial-pill {
            display: inline-flex;
            align-items: center;
            padding: 7px 12px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .hero-title {
            font-size: clamp(28px, 3vw, 38px);
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -.03em;
        }

        .hero-copy {
            max-width: 640px;
            font-size: 14px;
            color: #52637a;
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            width: 100%;
            max-width: 280px;
        }

        .hero-stat-card {
            padding: 14px 16px;
            border: 1px solid rgba(191, 219, 254, 0.85);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
        }

        .hero-stat-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .hero-stat-value {
            margin-top: 5px;
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }

        .hero-stat-caption {
            margin-top: 4px;
            font-size: 11px;
            color: #64748b;
        }

        .filters-card {
            padding: 18px 20px;
            border: 1px solid #dbe5f1;
            border-radius: 20px;
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.06), transparent 24%),
                #fff;
        }

        .filters-topline {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 16px;
        }

        .filters-title {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
        }

        .filters-subtitle {
            margin-top: 4px;
            font-size: 12px;
            color: #64748b;
        }

        .product-stack {
            display: grid;
            gap: 18px;
            padding: 20px;
        }

        .inventory-product-card {
            border: 1px solid #dbe5f1;
            border-radius: 22px;
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.06), transparent 26%),
                #fff;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
            overflow: hidden;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .inventory-product-card:hover {
            transform: translateY(-2px);
            border-color: #cbdaf0;
            box-shadow: 0 18px 34px rgba(15, 23, 42, 0.08);
        }

        .inventory-product-card.is-empty-marketplace .inventory-product-header {
            border-bottom: 0;
            grid-template-columns: minmax(0, 1fr);
        }

        .inventory-product-card.is-empty-marketplace .inventory-product-side {
            gap: 8px;
        }

        .inventory-product-header {
            display: grid;
            grid-template-columns: minmax(260px, 320px) minmax(0, 1fr);
            gap: 18px;
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
        }

        .product-panel {
            display: grid;
            gap: 10px;
        }

        .product-panel-main {
            display: grid;
            gap: 10px;
        }

        .product-topline {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
        }

        .product-name {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.3;
        }

        .product-sku-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            background: #eef4ff;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .03em;
        }

        .updated-meta {
            margin-top: 4px;
            font-size: 12px;
            color: #64748b;
        }

        .product-kpis {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .product-kpi {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #334155;
            font-size: 10px;
            font-weight: 700;
        }

        .product-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 2px;
        }

        .product-actions .btn {
            min-width: 116px;
            border-radius: 12px;
        }

        .inventory-product-side {
            display: grid;
            gap: 12px;
            align-content: start;
        }

        .inventory-section-label {
            font-size: 11px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .compact-summary-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .compact-summary-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 10px;
            border: 1px solid #dbe5f1;
            border-radius: 999px;
            background: #f8fbff;
            color: #334155;
            font-size: 11px;
            font-weight: 700;
        }

        .compact-summary-chip strong {
            color: #0f172a;
            font-size: 12px;
        }

        .account-overview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
        }

        .account-overview-card {
            padding: 12px 14px;
            border: 1px solid #dbeafe;
            border-radius: 16px;
            background: linear-gradient(135deg, #f8fbff 0%, #eff6ff 100%);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .account-overview-card:hover {
            transform: translateY(-1px);
            border-color: #bfdbfe;
            box-shadow: 0 10px 22px rgba(59, 130, 246, 0.10);
        }

        .account-overview-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
        }

        .account-overview-value {
            margin-top: 4px;
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        .inventory-accounts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 280px));
            gap: 16px;
            padding: 20px;
            justify-content: flex-start;
            align-items: start;
        }

        .inventory-accounts-shell {
            display: grid;
            gap: 16px;
            padding: 20px;
        }

        .empty-marketplace-callout {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 16px 18px;
            border: 1px dashed #cbd5e1;
            border-radius: 18px;
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.96) 0%, rgba(255, 255, 255, 0.98) 100%);
        }

        .empty-marketplace-copy strong {
            display: block;
            margin-bottom: 4px;
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
        }

        .empty-marketplace-copy span {
            display: block;
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
        }

        .inventory-accounts-shell .inventory-accounts-grid {
            padding: 0;
        }

        .inventory-accounts-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            padding-bottom: 2px;
        }

        .inventory-accounts-title {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
        }

        .inventory-accounts-subtitle {
            margin-top: 4px;
            font-size: 12px;
            color: #64748b;
        }

        .inventory-accounts-badge {
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 999px;
            background: #eef4ff;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .account-panel {
            border: 1px solid #dbe5f1;
            border-radius: 18px;
            background: #f8fbff;
            overflow: hidden;
            min-width: 0;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .account-panel:hover {
            transform: translateY(-2px);
            border-color: #cbdaf0;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.07);
        }

        .account-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            padding: 14px 16px;
            border-bottom: 1px solid #dbe5f1;
            background: #fff;
        }

        .account-panel-title {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.35;
        }

        .account-panel-subtitle {
            margin-top: 3px;
            font-size: 11px;
            color: #64748b;
        }

        .account-panel-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }

        .account-panel-chip {
            display: inline-flex;
            align-items: center;
            padding: 5px 8px;
            border-radius: 999px;
            background: #f8fafc;
            color: #475569;
            font-size: 10px;
            font-weight: 700;
        }

        .account-panel-body {
            padding: 14px;
            min-width: 0;
        }

        .quick-listing-card {
            padding: 12px;
            border: 1px solid #dbeafe;
            border-radius: 16px;
            background: linear-gradient(135deg, #f8fbff 0%, #eff6ff 100%);
        }

        .quick-listing-card .form-control,
        .quick-listing-card .form-select {
            min-height: 34px;
            font-size: 12px;
        }

        .quick-listing-card .mini-label {
            margin-bottom: 5px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: minmax(240px, 2fr) repeat(3, minmax(140px, 1fr)) auto;
            gap: 12px;
            align-items: end;
        }

        .filters-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .listing-modal .modal-content {
            border: 1px solid #dbeafe;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18);
        }

        .listing-modal .modal-header {
            background: linear-gradient(135deg, #eff6ff 0%, #f8fbff 100%);
            border-bottom: 1px solid #dbeafe;
        }

        .listing-modal .modal-body {
            background: #fbfdff;
        }

        .listing-editor {
            border: 1px solid #e2e8f0;
            border-left: 3px solid #bfdbfe;
            border-radius: 14px;
            padding: 12px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            min-width: 0;
            transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
        }

        .listing-editor:hover {
            transform: translateY(-1px);
            border-color: #cbdaf0;
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.05);
        }

        .listing-editor + .listing-editor {
            margin-top: 10px;
        }

        .listing-editor form {
            display: grid;
            gap: 10px;
        }

        .listing-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8px;
            min-width: 0;
        }

        .listing-code {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
            word-break: break-word;
        }

        .listing-title {
            margin-top: 2px;
            font-size: 11px;
            color: #64748b;
            line-height: 1.35;
            word-break: break-word;
        }

        .listing-id-line {
            margin-top: 5px;
            font-size: 10px;
            color: #64748b;
            line-height: 1.35;
            word-break: break-word;
        }

        .listing-form-row {
            display: grid;
            gap: 8px;
        }

        .listing-form-row.is-actions {
            grid-template-columns: minmax(84px, 96px) minmax(112px, 132px);
            align-items: end;
        }

        .listing-save-row {
            display: flex;
            justify-content: flex-end;
        }

        .listing-field-card {
            padding: 8px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.96);
            min-width: 0;
        }

        .mini-label {
            display: block;
            margin-bottom: 4px;
            font-size: 9px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .listing-editor .form-control,
        .listing-editor .form-select {
            min-height: 36px;
            padding-top: .3rem;
            padding-bottom: .3rem;
            font-size: 11px;
        }

        .partial-adjust-row {
            display: flex;
            gap: 8px;
            align-items: end;
            min-width: 0;
        }

        .partial-adjust-row .form-control {
            min-width: 0;
        }

        .listing-actions {
            display: flex;
            gap: 6px;
            align-items: end;
            justify-content: flex-end;
            min-width: 0;
        }

        .mini-add-btn {
            min-width: 44px;
            min-height: 36px;
            border-radius: 10px;
            flex: 0 0 44px;
        }

        .mini-save-btn {
            min-width: 112px;
            min-height: 36px;
            border-radius: 10px;
            width: auto;
        }

        .empty-state {
            padding: 24px;
            border: 1px dashed #cbd5e1;
            border-radius: 18px;
            background: #f8fafc;
            text-align: center;
            color: #64748b;
        }

        .empty-state strong {
            display: block;
            margin-bottom: 6px;
            color: #0f172a;
            font-size: 15px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-pill.is-working {
            background: #dcfce7;
            color: #166534;
        }

        .status-pill.is-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .status-pill.is-paused {
            background: #fee2e2;
            color: #991b1b;
        }

        .inventory-meta {
            margin-top: 2px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .inventory-chip {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 10px;
            font-weight: 700;
        }

        .account-split-summary {
            padding: 7px 10px;
            border-radius: 999px;
            background: #eef2ff;
            color: #334155;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .02em;
            white-space: nowrap;
        }

        .pagination-shell {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            border-top: 1px solid #e2e8f0;
        }

        @media (max-width: 1199.98px) {
            .filters-grid {
                grid-template-columns: 1fr 1fr;
            }

            .inventory-product-header {
                grid-template-columns: 1fr;
            }

            .hero-stats {
                max-width: none;
            }
        }

        @media (max-width: 767.98px) {
            .filters-grid {
                grid-template-columns: 1fr;
            }

            .filters-actions,
            .pagination-shell {
                justify-content: flex-start;
                align-items: stretch;
                flex-direction: column;
            }

            .listing-actions {
                justify-content: flex-end;
            }

            .product-topline {
                flex-direction: column;
                align-items: flex-start;
            }

            .product-stack,
            .inventory-product-header,
            .inventory-accounts-grid,
            .inventory-accounts-shell {
                padding: 14px;
            }

            .inventory-accounts-shell .inventory-accounts-grid {
                padding: 0;
            }

            .empty-marketplace-callout {
                flex-direction: column;
                align-items: flex-start;
            }

            .filters-card {
                padding: 14px;
            }

            .filters-topline,
            .hero-stats {
                grid-template-columns: 1fr;
                flex-direction: column;
                align-items: stretch;
            }

            .listing-top,
            .listing-form-row.is-actions {
                grid-template-columns: 1fr;
                flex-direction: column;
                align-items: stretch;
            }

            .listing-save-row,
            .listing-actions {
                justify-content: stretch;
            }

            .mini-save-btn {
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $displayProducts = collect($products->items());
        $platformOptions = collect($platformSuggestions ?? collect())->filter()->values();
        $visibleAccountCount = $displayProducts
            ->flatMap(function ($product) {
                return $product->marketplaceListings->map(function ($listing) {
                    $platform = strtolower(trim((string) ($listing->platform ?? '')));
                    $accountName = trim((string) ($listing->account_name ?? '')) ?: 'Primary Account';

                    return $platform . '::' . $accountName;
                });
            })
            ->filter()
            ->unique()
            ->count();
    @endphp

    <div class="page-content partial-inventory-page">
        <div class="container-fluid">
            <div class="partial-hero mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-8">
                            <span class="partial-pill">Partial Inventory</span>
                            <h2 class="hero-title mt-3 mb-2">Editable Partial Inventory</h2>
                            <p class="hero-copy mb-0">Manage partial stock, listing health, and marketplace identifiers account-wise for every linked product without the old stretched table view.</p>
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex justify-content-lg-end align-items-lg-end align-items-start gap-3 flex-column flex-lg-row">
                                <div class="hero-stats">
                                    <div class="hero-stat-card">
                                        <div class="hero-stat-label">Products</div>
                                        <div class="hero-stat-value">{{ number_format($displayProducts->count()) }}</div>
                                        <div class="hero-stat-caption">Visible in this view</div>
                                    </div>
                                    <div class="hero-stat-card">
                                        <div class="hero-stat-label">Accounts</div>
                                        <div class="hero-stat-value">{{ number_format($visibleAccountCount) }}</div>
                                        <div class="hero-stat-caption">Linked in this view</div>
                                    </div>
                                </div>
                                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">Back To Products</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="partial-card mb-4">
                <div class="card-body p-4">
                    <div class="filters-card">
                        <div class="filters-topline">
                            <div>
                                <div class="filters-title">Refine This View</div>
                                <div class="filters-subtitle">Filter products by keyword, date range, and page size to focus on the inventory you need to update.</div>
                            </div>
                        </div>
                        <form method="GET" action="{{ route('products.partial_inventory') }}" class="filters-grid">
                            <div>
                                <label class="form-label">Search</label>
                                <input type="search" class="form-control" name="search" value="{{ $search ?? '' }}" placeholder="Product, SKU, platform, or account">
                            </div>
                            <div>
                                <label class="form-label">Date From</label>
                                <input type="date" class="form-control" name="date_from" value="{{ $dateFrom ?? '' }}">
                            </div>
                            <div>
                                <label class="form-label">Date To</label>
                                <input type="date" class="form-control" name="date_to" value="{{ $dateTo ?? '' }}">
                            </div>
                            <div>
                                <label class="form-label">Rows</label>
                                <select name="per_page" class="form-select">
                                    @foreach (($perPageOptions ?? [100]) as $rowsOption)
                                        <option value="{{ $rowsOption }}" {{ (int) ($perPage ?? 100) === (int) $rowsOption ? 'selected' : '' }}>
                                            {{ $rowsOption }} rows
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="filters-actions">
                                <button type="submit" class="btn btn-primary">Apply</button>
                                <a href="{{ route('products.partial_inventory') }}" class="btn btn-light">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-4">
                <div class="partial-card">
                    <div class="card-body p-0">
                        <div class="product-stack">
                            @forelse ($displayProducts as $product)
                                @php
                                    $masterStock = (float) ($product->stock_qty ?? 0);
                                    $listingGroups = $product->marketplaceListings->groupBy(function ($listing) {
                                        $platform = strtolower(trim((string) ($listing->platform ?? '')));
                                        $accountName = trim((string) ($listing->account_name ?? '')) ?: 'Primary Account';

                                        return $platform . '::' . $accountName;
                                    });
                                    $productColumns = $accountColumns
                                        ->filter(fn ($column) => $listingGroups->has($column['key']))
                                        ->values();
                                    $productListingCount = $product->marketplaceListings->count();
                                    $productAccountCount = $productColumns->count();
                                    $useDefaultSplit = $productAccountCount > 0
                                        && $product->marketplaceListings->every(fn ($listing) => (int) ($listing->allocated_stock ?? 0) <= 0);
                                    $masterStockUnits = max((int) round($masterStock), 0);
                                    $defaultAccountAllocations = $productColumns->mapWithKeys(function ($column, $index) use ($masterStockUnits, $productAccountCount) {
                                        $baseAllocation = $productAccountCount > 0 ? intdiv($masterStockUnits, $productAccountCount) : 0;
                                        $remainder = $productAccountCount > 0 ? $masterStockUnits % $productAccountCount : 0;

                                        return [$column['key'] => $baseAllocation + ($index < $remainder ? 1 : 0)];
                                    });
                                @endphp

                                <div class="inventory-product-card {{ $productColumns->isEmpty() ? 'is-empty-marketplace' : '' }}">
                                    <div class="inventory-product-header">
                                        <div class="product-panel">
                                            <div class="product-topline">
                                                <div class="product-panel-main">
                                                    <span class="product-sku-badge">{{ $product->sku_code }}</span>
                                                    <div class="product-name">{{ $product->name }}</div>
                                                    <div class="updated-meta">{{ number_format($productAccountCount) }} linked account{{ $productAccountCount === 1 ? '' : 's' }} across {{ number_format($productListingCount) }} listing{{ $productListingCount === 1 ? '' : 's' }}.</div>
                                                </div>
                                                <div class="product-kpis">
                                                    <span class="product-kpi">{{ number_format($productListingCount) }} listing{{ $productListingCount === 1 ? '' : 's' }}</span>
                                                </div>
                                            </div>
                                            <div class="inventory-meta">
                                                <span class="inventory-chip">Inventory: {{ number_format($masterStock, 0) }}</span>
                                                <span class="inventory-chip">HSN: {{ $product->hsn_code ?: '-' }}</span>
                                                <span class="inventory-chip">GST: {{ $product?->getGstSlabMaster?->rate ?? 0 }}%</span>
                                                @if($useDefaultSplit && $productAccountCount > 0)
                                                    <span class="inventory-chip">Auto split: {{ $productAccountCount }}</span>
                                                @endif
                                            </div>
                                            <div class="product-actions">
                                                <a href="{{ route('products.marketplace', $product->id) }}" class="btn btn-outline-primary btn-sm">
                                                    Manage
                                                </a>
                                                <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#quick-listing-modal-{{ $product->id }}">
                                                    Add Listing
                                                </button>
                                            </div>
                                        </div>

                                        <div class="inventory-product-side">
                                            @if($productColumns->isEmpty())
                                                <div class="compact-summary-row">
                                                    <span class="compact-summary-chip"><strong>{{ number_format($productAccountCount) }}</strong> linked accounts</span>
                                                    <span class="compact-summary-chip"><strong>{{ number_format((int) $product->marketplaceListings->sum(fn ($listing) => (int) ($listing->allocated_stock ?? 0))) }}</strong> total partial</span>
                                                    <span class="compact-summary-chip"><strong>{{ number_format($product->marketplaceListings->where('listing_status', 'active')->count()) }}</strong> active listings</span>
                                                </div>
                                            @else
                                                <div class="inventory-section-label">Quick Summary</div>
                                                <div class="account-overview-grid">
                                                    <div class="account-overview-card">
                                                        <div class="account-overview-label">Linked Accounts</div>
                                                        <div class="account-overview-value">{{ number_format($productAccountCount) }}</div>
                                                    </div>
                                                    <div class="account-overview-card">
                                                        <div class="account-overview-label">Total Partial</div>
                                                        <div class="account-overview-value">{{ number_format((int) $product->marketplaceListings->sum(fn ($listing) => (int) ($listing->allocated_stock ?? 0))) }}</div>
                                                    </div>
                                                    <div class="account-overview-card">
                                                        <div class="account-overview-label">Active Listings</div>
                                                        <div class="account-overview-value">{{ number_format($product->marketplaceListings->where('listing_status', 'active')->count()) }}</div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="inventory-accounts-shell">
                                        <div class="inventory-accounts-head">
                                            <div>
                                                <div class="inventory-accounts-title">Marketplace Accounts</div>
                                                <div class="inventory-accounts-subtitle">
                                                    {{ $productColumns->isEmpty()
                                                        ? 'No linked accounts or listings are available for this product yet.'
                                                        : 'Each account has its own listing cards, partial stock, identifiers, and status controls.' }}
                                                </div>
                                            </div>
                                            <div class="inventory-accounts-badge">
                                                {{ number_format($productAccountCount) }} linked account{{ $productAccountCount === 1 ? '' : 's' }}
                                            </div>
                                        </div>
                                        <div class="inventory-accounts-grid">
                                            @if($productColumns->isEmpty())
                                                <div class="empty-marketplace-callout">
                                                    <div class="empty-marketplace-copy">
                                                        <strong>No marketplace listings for this product yet.</strong>
                                                        <span>Add the first listing to start managing partial inventory, identifiers, and account-wise stock splits for this product.</span>
                                                    </div>
                                                    <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#quick-listing-modal-{{ $product->id }}">
                                                        Add First Listing
                                                    </button>
                                                </div>
                                            @else
                                                @foreach ($productColumns as $accountColumn)
                                                    @php
                                                        $accountListings = $listingGroups->get($accountColumn['key'], collect());
                                                        $accountTotal = $useDefaultSplit
                                                            ? (int) ($defaultAccountAllocations[$accountColumn['key']] ?? 0)
                                                            : (int) $accountListings->sum(fn ($listing) => (int) ($listing->allocated_stock ?? 0));
                                                        $accountLabelParts = preg_split('/\s*\/\s*/', (string) $accountColumn['label'], 2);
                                                        $accountPlatformLabel = $accountLabelParts[0] ?? $accountColumn['label'];
                                                        $accountNameLabel = $accountLabelParts[1] ?? $accountColumn['label'];
                                                    @endphp
                                                    <div class="account-panel">
                                                        <div class="account-panel-header">
                                                            <div>
                                                                <div class="account-panel-title">{{ $accountNameLabel }}</div>
                                                                <div class="account-panel-subtitle">{{ number_format($accountListings->count()) }} listing{{ $accountListings->count() === 1 ? '' : 's' }} linked for this account</div>
                                                                <div class="account-panel-meta">
                                                                    <span class="account-panel-chip">{{ $accountPlatformLabel }}</span>
                                                                    <span class="account-panel-chip">{{ number_format($accountListings->where('listing_status', 'active')->count()) }} active</span>
                                                                </div>
                                                            </div>
                                                            <div class="account-split-summary">Qty: {{ number_format($accountTotal) }}</div>
                                                        </div>
                                                        <div class="account-panel-body">
                                                            @foreach ($accountListings as $listingIndex => $listing)
                                                                @php
                                                                    $accountDefaultTotal = (int) ($defaultAccountAllocations[$accountColumn['key']] ?? 0);
                                                                    $listingCountInAccount = max($accountListings->count(), 1);
                                                                    $listingDefaultBase = intdiv($accountDefaultTotal, $listingCountInAccount);
                                                                    $listingDefaultRemainder = $accountDefaultTotal % $listingCountInAccount;
                                                                    $listingAllocated = $useDefaultSplit
                                                                        ? $listingDefaultBase + ($listingIndex < $listingDefaultRemainder ? 1 : 0)
                                                                        : (float) ($listing->allocated_stock ?? 0);
                                                                    $isActive = strtolower((string) ($listing->listing_status ?? '')) === 'active';
                                                                    $flagClass = $isActive
                                                                        ? ($listingAllocated > 0 ? 'is-working' : 'is-warning')
                                                                        : 'is-paused';
                                                                    $flagLabel = $isActive
                                                                        ? ($listingAllocated > 0 ? 'Working Properly' : 'Needs Stock')
                                                                        : 'Listing Not Working';
                                                                @endphp

                                                                <div class="listing-editor">
                                                                    <form action="{{ route('products.marketplace.listings.quick_update', [$product->id, $listing->id]) }}" method="POST">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <div class="listing-top">
                                                                            <div>
                                                                                <div class="listing-code">{{ $listing->platform_sku }}</div>
                                                                                @if(!empty($listing->listing_title))
                                                                                    <div class="listing-title">{{ $listing->listing_title }}</div>
                                                                                @endif
                                                                                @if(!empty($listing->asin) || !empty($listing->fsn) || !empty($listing->marketplace_item_id))
                                                                                    <div class="listing-id-line">
                                                                                        @if(!empty($listing->asin))
                                                                                            ASIN: {{ $listing->asin }}
                                                                                        @endif
                                                                                        @if(!empty($listing->asin) && (!empty($listing->fsn) || !empty($listing->marketplace_item_id)))
                                                                                            |
                                                                                        @endif
                                                                                        @if(!empty($listing->fsn))
                                                                                            FSN: {{ $listing->fsn }}
                                                                                        @endif
                                                                                        @if(!empty($listing->fsn) && !empty($listing->marketplace_item_id))
                                                                                            |
                                                                                        @endif
                                                                                        @if(!empty($listing->marketplace_item_id))
                                                                                            ASKU: {{ $listing->marketplace_item_id }}
                                                                                        @endif
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                            <span class="status-pill {{ $flagClass }}">{{ $flagLabel }}</span>
                                                                        </div>
                                                                        <div class="listing-form-row is-actions">
                                                                            <div class="listing-field-card">
                                                                                <label class="mini-label" for="allocated-stock-{{ $listing->id }}">Partial</label>
                                                                                <input id="allocated-stock-{{ $listing->id }}" type="number" min="0" step="1" name="allocated_stock" class="form-control form-control-sm" value="{{ (int) $listingAllocated }}">
                                                                            </div>
                                                                            <div class="listing-field-card">
                                                                                <label class="mini-label">Add</label>
                                                                                <div class="partial-adjust-row">
                                                                                    <input type="number" min="1" step="1" class="form-control form-control-sm js-partial-step" value="1" data-target="allocated-stock-{{ $listing->id }}" placeholder="Qty">
                                                                                    <button type="button" class="btn btn-outline-primary btn-sm mini-add-btn js-add-partial-btn" data-target="allocated-stock-{{ $listing->id }}">
                                                                                        +
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                            <input type="hidden" name="listing_status" value="{{ $listing->listing_status ?? 'active' }}">
                                                                        </div>
                                                                        <div class="listing-save-row">
                                                                            <div class="listing-actions">
                                                                                <button type="submit" class="btn btn-primary btn-sm mini-save-btn">
                                                                                    Save
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <strong>No products are available right now.</strong>
                                    Partial inventory will appear here once products and marketplace listings are added.
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @if ($products->hasPages())
                        <div class="pagination-shell">
                            <div class="text-muted small">
                                Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products
                            </div>
                            <div>
                                {{ $products->onEachSide(1)->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @foreach ($displayProducts as $product)
            <div class="modal fade listing-modal" id="quick-listing-modal-{{ $product->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title mb-1">Add Marketplace Listing</h5>
                                <div class="small text-muted">{{ $product->name }} | Master SKU: {{ $product->sku_code }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="quick-listing-card">
                                <form action="{{ route('products.marketplace.listings.store', $product->id) }}" method="POST" class="row g-3 quick-listing-form">
                                    @csrf
                                    <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                                    <input type="hidden" name="listing_title" value="{{ $product->name }}">

                                    <div class="col-md-6">
                                        <label class="mini-label">Platform</label>
                                        <input type="text" name="platform" class="form-control form-control-sm js-inline-platform" list="platform-suggestions" placeholder="Amazon, Flipkart, Meesho..." required>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center gap-2">
                                            <label class="mini-label mb-0">Marketplace Account</label>
                                            <a href="{{ route('products.marketplace.accounts.index') }}" class="small text-decoration-none">Accounts</a>
                                        </div>
                                        <select name="marketplace_account_id" class="form-select form-select-sm js-inline-account" required>
                                            <option value="">Select account</option>
                                            @foreach ($marketplaceAccounts as $account)
                                                <option value="{{ $account->id }}" data-platform="{{ $account->platform }}" data-account-name="{{ $account->name }}">
                                                    {{ ucfirst($account->platform) }} / {{ $account->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="mini-label">Platform SKU</label>
                                        <input type="text" name="platform_sku" class="form-control form-control-sm" placeholder="AMZ-SKU-001" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="mini-label">ASKU / Item ID</label>
                                        <input type="text" name="marketplace_item_id" class="form-control form-control-sm" placeholder="ASIN / FSN / ASKU / Listing ID">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="mini-label">ASIN</label>
                                        <input type="text" name="asin" class="form-control form-control-sm" placeholder="Amazon ASIN">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="mini-label">FSN</label>
                                        <input type="text" name="fsn" class="form-control form-control-sm" placeholder="Flipkart FSN">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="mini-label">Partial Stock</label>
                                        <input type="number" min="0" name="allocated_stock" class="form-control form-control-sm" value="0">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="mini-label">Reserved</label>
                                        <input type="number" min="0" name="reserved_stock" class="form-control form-control-sm" value="0">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="mini-label">Status</label>
                                        <select name="listing_status" class="form-select form-select-sm" required>
                                            @foreach ($listingStatuses as $statusKey => $statusLabel)
                                                <option value="{{ $statusKey }}" {{ $statusKey === 'active' ? 'selected' : '' }}>
                                                    {{ $statusLabel }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="mini-label">Fulfillment</label>
                                        <select name="fulfillment_type" class="form-select form-select-sm">
                                            <option value="">Select</option>
                                            @foreach ($fulfillmentTypes as $fulfillmentKey => $fulfillmentLabel)
                                                <option value="{{ $fulfillmentKey }}">{{ $fulfillmentLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary btn-sm">Save Listing</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@section('scripts')
    <datalist id="platform-suggestions">
        @foreach ($platformOptions as $platformOption)
            <option value="{{ $platformOption }}">{{ ucwords($platformOption) }}</option>
        @endforeach
    </datalist>

    <script>
        (function () {
            document.querySelectorAll('.js-add-partial-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    const targetId = button.getAttribute('data-target');
                    const targetInput = document.getElementById(targetId);
                    if (!targetInput) {
                        return;
                    }

                    const wrapper = button.closest('.partial-adjust-row');
                    const stepInput = wrapper ? wrapper.querySelector('.js-partial-step') : null;
                    const currentValue = parseInt(targetInput.value || '0', 10) || 0;
                    const addValue = Math.max(parseInt(stepInput ? stepInput.value || '1' : '1', 10) || 1, 1);

                    targetInput.value = currentValue + addValue;
                    targetInput.dispatchEvent(new Event('input', { bubbles: true }));
                });
            });

            document.querySelectorAll('.quick-listing-form').forEach(function (form) {
                const platformInput = form.querySelector('.js-inline-platform');
                const accountSelect = form.querySelector('.js-inline-account');

                if (!platformInput || !accountSelect) {
                    return;
                }

                const syncAccounts = function () {
                    const platform = (platformInput.value || '').trim().toLowerCase();

                    Array.from(accountSelect.options).forEach(function (option, index) {
                        if (index === 0) {
                            option.hidden = false;
                            return;
                        }

                        const optionPlatform = (option.dataset.platform || '').trim().toLowerCase();
                        const visible = !platform || optionPlatform === platform;
                        option.hidden = !visible;

                        if (!visible && option.selected) {
                            option.selected = false;
                        }
                    });

                    if (accountSelect.selectedIndex <= 0) {
                        accountSelect.value = '';
                    }
                };

                platformInput.addEventListener('input', syncAccounts);
                platformInput.addEventListener('change', syncAccounts);
                accountSelect.addEventListener('change', function () {
                    const selectedOption = accountSelect.options[accountSelect.selectedIndex];
                    if (selectedOption && selectedOption.value) {
                        platformInput.value = selectedOption.dataset.platform || platformInput.value;
                        syncAccounts();
                    }
                });

                syncAccounts();
            });
        })();
    </script>
@endsection
