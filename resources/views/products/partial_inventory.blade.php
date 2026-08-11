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

        .partial-table {
            min-width: 1180px;
        }

        .partial-table thead th {
            vertical-align: top;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            color: #475569;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .partial-table td {
            vertical-align: top;
            border-color: #e2e8f0;
        }

        .partial-table tbody tr:hover > td,
        .partial-table tbody tr:focus-within > td {
            background: #f8fbff;
        }

        .sticky-product-col {
            min-width: 250px;
        }

        .product-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .quick-listing-card {
            margin-top: 12px;
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

        .account-col {
            min-width: 300px;
        }

        .account-cell {
            padding: 10px !important;
        }

        .listing-editor {
            border: 1px solid #dbeafe;
            border-radius: 14px;
            padding: 10px;
            background: #fff;
        }

        .listing-editor + .listing-editor {
            margin-top: 10px;
        }

        .listing-code {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
        }

        .listing-title {
            margin-top: 2px;
            font-size: 11px;
            color: #64748b;
            line-height: 1.35;
        }

        .mini-label {
            display: block;
            margin-bottom: 4px;
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .listing-editor .form-control,
        .listing-editor .form-select {
            min-height: 32px;
            padding-top: .3rem;
            padding-bottom: .3rem;
            font-size: 12px;
        }

        .partial-adjust-row {
            display: flex;
            gap: 8px;
            align-items: end;
            margin-top: 8px;
        }

        .partial-adjust-row .form-control {
            min-width: 84px;
        }

        .mini-save-btn {
            width: 32px;
            height: 32px;
            padding: 0;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .empty-cell {
            font-size: 12px;
            color: #94a3b8;
            padding-top: 8px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
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

        .updated-meta {
            margin-top: 6px;
            font-size: 11px;
            color: #64748b;
        }

        .inventory-meta {
            margin-top: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .inventory-chip {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 700;
        }

        .account-split-summary {
            margin-bottom: 10px;
            padding: 8px 10px;
            border-radius: 12px;
            background: #f8fafc;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
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
        }
    </style>
@endsection

@section('content')
    @php
        $displayProducts = collect($products->items());
        $platformOptions = collect($platformSuggestions ?? collect())->filter()->values();
    @endphp

    <div class="page-content partial-inventory-page">
        <div class="container-fluid">
            <div class="partial-hero mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-8">
                            <span class="partial-pill">Partial Inventory</span>
                            <h2 class="mt-3 mb-2">Editable Partial Inventory</h2>
                            <p class="text-muted mb-0">Manage partial stock and listing flags account-wise for every linked marketplace listing.</p>
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex justify-content-lg-end align-items-center gap-3 flex-wrap">
                                <div class="text-end">
                                    <div class="fw-semibold">{{ number_format($displayProducts->count()) }} products</div>
                                    <div class="small text-muted">{{ number_format($accountColumns->count()) }} account columns</div>
                                </div>
                                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">Back To Products</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="partial-card mb-4">
                <div class="card-body p-4">
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

            <div class="d-grid gap-4">
                <div class="partial-card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table partial-table align-middle mb-0">
                                <thead>
                                     <tr>
                                         <th class="sticky-product-col">Product</th>
                                         @forelse ($accountColumns as $accountColumn)
                                             <th class="account-col">
                                                 <div>{{ $accountColumn['label'] }}</div>
                                                 <div class="small text-muted mt-1" style="text-transform:none; letter-spacing:0;">Split stock and flag</div>
                                             </th>
                                         @empty
                                             <th class="account-col">Marketplace Listings</th>
                                        @endforelse
                                    </tr>
                                </thead>
                                <tbody>
                                     @forelse ($displayProducts as $product)
                                         @php
                                             $masterStock = (float) ($product->stock_qty ?? 0);
                                             $productSearch = strtolower(trim($product->name . ' ' . $product->sku_code . ' ' . $product->marketplaceListings->pluck('platform_sku')->implode(' ') . ' ' . $product->marketplaceListings->pluck('account_name')->implode(' ') . ' ' . $product->marketplaceListings->pluck('platform')->implode(' ')));
                                            $listingGroups = $product->marketplaceListings->groupBy(function ($listing) {
                                                $platform = strtolower(trim((string) ($listing->platform ?? '')));
                                                 $accountName = trim((string) ($listing->account_name ?? '')) ?: 'Primary Account';

                                                 return $platform . '::' . $accountName;
                                             });
                                             $productGroupKeys = $accountColumns
                                                 ->filter(fn ($column) => $listingGroups->has($column['key']))
                                                 ->pluck('key')
                                                 ->values();
                                             $productAccountCount = $productGroupKeys->count();
                                             $useDefaultSplit = $productAccountCount > 0
                                                 && $product->marketplaceListings->every(fn ($listing) => (int) ($listing->allocated_stock ?? 0) <= 0);
                                             $masterStockUnits = max((int) round($masterStock), 0);
                                             $defaultAccountAllocations = $productGroupKeys->mapWithKeys(function ($key, $index) use ($masterStockUnits, $productAccountCount) {
                                                 $baseAllocation = $productAccountCount > 0 ? intdiv($masterStockUnits, $productAccountCount) : 0;
                                                 $remainder = $productAccountCount > 0 ? $masterStockUnits % $productAccountCount : 0;

                                                 return [$key => $baseAllocation + ($index < $remainder ? 1 : 0)];
                                             });
                                         @endphp

                                         <tr data-partial-search="{{ $productSearch }}">
                                             <td class="sticky-product-col">
                                                <div class="fw-semibold">{{ $product->name }}</div>
                                                 <div class="text-muted small">Master SKU: {{ $product->sku_code }}</div>
                                                 <div class="text-muted small">GST: {{ $product?->getGstSlabMaster?->rate ?? 0 }}%</div>
                                                 <div class="small text-primary mt-2">{{ number_format($product->marketplaceListings->count()) }} linked listing(s)</div>
                                                <div class="inventory-meta">
                                                    <span class="inventory-chip">Inventory: {{ number_format($masterStock, 0) }}</span>
                                                    @if($useDefaultSplit && $productAccountCount > 0)
                                                        <span class="inventory-chip">Default split across {{ $productAccountCount }} account{{ $productAccountCount > 1 ? 's' : '' }}</span>
                                                    @endif
                                                </div>
                                                 <div class="updated-meta">Updated: {{ optional($product->updated_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') ?: '-' }}</div>
                                                <div class="product-actions">
                                                    <a href="{{ route('products.marketplace', $product->id) }}" class="btn btn-outline-primary btn-sm">
                                                        Manage Marketplace
                                                    </a>
                                                    <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="collapse" data-bs-target="#quick-listing-{{ $product->id }}" aria-expanded="false" aria-controls="quick-listing-{{ $product->id }}">
                                                        Add Listing
                                                    </button>
                                                </div>
                                                <div class="collapse" id="quick-listing-{{ $product->id }}">
                                                    <div class="quick-listing-card">
                                                        <div class="fw-semibold mb-2">Add Marketplace Listing</div>
                                                        <form action="{{ route('products.marketplace.listings.store', $product->id) }}" method="POST" class="row g-2 quick-listing-form">
                                                            @csrf
                                                            <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                                                            <input type="hidden" name="listing_title" value="{{ $product->name }}">

                                                            <div class="col-12">
                                                                <label class="mini-label">Platform</label>
                                                                <input type="text" name="platform" class="form-control form-control-sm js-inline-platform" list="platform-suggestions" placeholder="Amazon, Flipkart, Meesho..." required>
                                                            </div>

                                                            <div class="col-12">
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

                                                            <div class="col-12">
                                                                <label class="mini-label">Platform SKU</label>
                                                                <input type="text" name="platform_sku" class="form-control form-control-sm" placeholder="AMZ-SKU-001" required>
                                                            </div>

                                                            <div class="col-6">
                                                                <label class="mini-label">Partial Stock</label>
                                                                <input type="number" min="0" name="allocated_stock" class="form-control form-control-sm" value="0">
                                                            </div>

                                                            <div class="col-6">
                                                                <label class="mini-label">Reserved</label>
                                                                <input type="number" min="0" name="reserved_stock" class="form-control form-control-sm" value="0">
                                                            </div>

                                                            <div class="col-6">
                                                                <label class="mini-label">Status</label>
                                                                <select name="listing_status" class="form-select form-select-sm" required>
                                                                    @foreach ($listingStatuses as $statusKey => $statusLabel)
                                                                        <option value="{{ $statusKey }}" {{ $statusKey === 'active' ? 'selected' : '' }}>
                                                                            {{ $statusLabel }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div class="col-6">
                                                                <label class="mini-label">Fulfillment</label>
                                                                <select name="fulfillment_type" class="form-select form-select-sm">
                                                                    <option value="">Select</option>
                                                                    @foreach ($fulfillmentTypes as $fulfillmentKey => $fulfillmentLabel)
                                                                        <option value="{{ $fulfillmentKey }}">{{ $fulfillmentLabel }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div class="col-12 d-flex justify-content-end">
                                                                <button type="submit" class="btn btn-primary btn-sm">Save Listing</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                 </div>
                                             </td>

                                             @if($accountColumns->isEmpty())
                                                 <td class="account-col">
                                                     <div class="text-muted small">No marketplace accounts created yet.</div>
                                                </td>
                                            @else
                                                @foreach ($accountColumns as $accountColumn)
                                                    @php
                                                        $accountListings = $listingGroups->get($accountColumn['key'], collect());
                                                    @endphp
                                                     <td class="account-col account-cell">
                                                         @if($accountListings->isEmpty())
                                                             <div class="empty-cell">No listing</div>
                                                         @else
                                                            <div class="account-split-summary">
                                                                Partial for this account:
                                                                {{ number_format($useDefaultSplit ? (int) ($defaultAccountAllocations[$accountColumn['key']] ?? 0) : (int) $accountListings->sum(fn ($listing) => (int) ($listing->allocated_stock ?? 0)), 0) }}
                                                            </div>
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
                                                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                                                        <div>
                                                                            <div class="listing-code">{{ $listing->platform_sku }}</div>
                                                                            @if(!empty($listing->listing_title))
                                                                                <div class="listing-title">{{ $listing->listing_title }}</div>
                                                                            @endif
                                                                            <div class="updated-meta">Updated: {{ optional($listing->updated_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') ?: '-' }}</div>
                                                                        </div>
                                                                        <span class="status-pill {{ $flagClass }}">{{ $flagLabel }}</span>
                                                                    </div>

                                                                    <form action="{{ route('products.marketplace.listings.quick_update', [$product->id, $listing->id]) }}" method="POST">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <div class="row g-2">
                                                                             <div class="col-6">
                                                                                 <label class="mini-label" for="allocated-stock-{{ $listing->id }}">Partial</label>
                                                                                 <input id="allocated-stock-{{ $listing->id }}" type="number" min="0" step="1" name="allocated_stock" class="form-control form-control-sm" value="{{ (int) $listingAllocated }}">
                                                                                 <div class="partial-adjust-row">
                                                                                     <input type="number" min="1" step="1" class="form-control form-control-sm js-partial-step" value="1" data-target="allocated-stock-{{ $listing->id }}" placeholder="Qty">
                                                                                     <button type="button" class="btn btn-outline-primary btn-sm js-add-partial-btn" data-target="allocated-stock-{{ $listing->id }}">
                                                                                        Add Partial
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-6">
                                                                                <label class="mini-label" for="listing-status-{{ $listing->id }}">Flag</label>
                                                                                <select id="listing-status-{{ $listing->id }}" name="listing_status" class="form-select form-select-sm">
                                                                                    @foreach ($listingStatuses as $statusKey => $statusLabel)
                                                                                        <option value="{{ $statusKey }}" {{ ($listing->listing_status ?? 'active') === $statusKey ? 'selected' : '' }}>
                                                                                            {{ $statusLabel }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-12 d-flex justify-content-end pt-1">
                                                                                <button type="submit" class="btn btn-primary btn-sm mini-save-btn" title="Update listing">
                                                                                    <i class="ri-check-line"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </td>
                                                @endforeach
                                            @endif
                                        </tr>
                                     @empty
                                         <tr>
                                             <td colspan="{{ 1 + max($accountColumns->count(), 1) }}" class="text-center text-muted py-4">
                                                 No products are available right now.
                                             </td>
                                         </tr>
                                    @endforelse
                                </tbody>
                            </table>
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
