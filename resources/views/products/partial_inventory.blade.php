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

        .inventory-col {
            min-width: 140px;
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
    </style>
@endsection

@section('content')
    @php
        $displayProducts = $products->values();
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
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-8">
                            <h4 class="mb-1">Search Products</h4>
                            <p class="text-muted mb-0">Find a product by name, master SKU, marketplace SKU, platform, or account name.</p>
                        </div>
                        <div class="col-lg-4">
                            <input type="search" class="form-control" id="partial-inventory-search" placeholder="Search product or SKU">
                        </div>
                    </div>
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
                                        <th class="inventory-col">Main Inventory</th>
                                        @forelse ($accountColumns as $accountColumn)
                                            <th class="account-col">
                                                <div>{{ $accountColumn['label'] }}</div>
                                                <div class="small text-muted mt-1" style="text-transform:none; letter-spacing:0;">Partial, reserved, flag</div>
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
                                        @endphp

                                        <tr data-partial-search="{{ $productSearch }}">
                                            <td class="sticky-product-col">
                                                <div class="fw-semibold">{{ $product->name }}</div>
                                                <div class="text-muted small">Master SKU: {{ $product->sku_code }}</div>
                                                <div class="text-muted small">GST: {{ $product?->getGstSlabMaster?->rate ?? 0 }}%</div>
                                                <div class="small text-primary mt-2">{{ number_format($product->marketplaceListings->count()) }} linked listing(s)</div>
                                            </td>
                                            <td class="inventory-col">
                                                <div class="fw-semibold">{{ number_format($masterStock, 2) }}</div>
                                                <div class="small text-muted">Main stock</div>
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
                                                            @foreach ($accountListings as $listing)
                                                                @php
                                                                    $listingAllocated = (float) ($listing->allocated_stock ?? 0);
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
                                                                        </div>
                                                                        <span class="status-pill {{ $flagClass }}">{{ $flagLabel }}</span>
                                                                    </div>

                                                                    <form action="{{ route('products.marketplace.listings.quick_update', [$product->id, $listing->id]) }}" method="POST">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <div class="row g-2">
                                                                            <div class="col-4">
                                                                                <label class="mini-label" for="allocated-stock-{{ $listing->id }}">Partial</label>
                                                                                <input id="allocated-stock-{{ $listing->id }}" type="number" min="0" step="1" name="allocated_stock" class="form-control form-control-sm" value="{{ (int) ($listing->allocated_stock ?? 0) }}">
                                                                            </div>
                                                                            <div class="col-4">
                                                                                <label class="mini-label" for="reserved-stock-{{ $listing->id }}">Reserved</label>
                                                                                <input id="reserved-stock-{{ $listing->id }}" type="number" min="0" step="1" name="reserved_stock" class="form-control form-control-sm" value="{{ (int) ($listing->reserved_stock ?? 0) }}">
                                                                            </div>
                                                                            <div class="col-4">
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
                                            <td colspan="{{ 2 + max($accountColumns->count(), 1) }}" class="text-center text-muted py-4">
                                                No products are available right now.
                                            </td>
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
@endsection

@section('scripts')
    <script>
        (function () {
            const searchInput = document.getElementById('partial-inventory-search');
            if (!searchInput) {
                return;
            }

            const cards = Array.from(document.querySelectorAll('[data-partial-search]'));

            searchInput.addEventListener('input', function () {
                const query = (searchInput.value || '').trim().toLowerCase();

                cards.forEach(function (card) {
                    const haystack = card.getAttribute('data-partial-search') || '';
                    card.style.display = query === '' || haystack.includes(query) ? '' : 'none';
                });
            });
        })();
    </script>
@endsection
