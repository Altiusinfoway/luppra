@php
    $marketplaceRows = old('marketplace_listings', isset($product) ? $product->marketplaceListings->map(fn($listing) => [
        'id' => $listing->id,
        'platform' => $listing->platform,
        'account_name' => $listing->account_name,
        'platform_sku' => $listing->platform_sku,
        'marketplace_item_id' => $listing->marketplace_item_id,
        'listing_title' => $listing->listing_title,
        'pack_size' => $listing->pack_size,
        'selling_price' => $listing->selling_price,
        'mrp' => $listing->mrp,
        'listing_status' => $listing->listing_status,
        'fulfillment_type' => $listing->fulfillment_type,
        'allocated_stock' => $listing->allocated_stock,
        'reserved_stock' => $listing->reserved_stock,
    ])->toArray() : []);

    if (empty($marketplaceRows)) {
        $marketplaceRows = [[
            'id' => '',
            'platform' => '',
            'account_name' => 'Primary Account',
            'platform_sku' => '',
            'marketplace_item_id' => '',
            'listing_title' => '',
            'pack_size' => '',
            'selling_price' => '',
            'mrp' => '',
            'listing_status' => 'active',
            'fulfillment_type' => '',
            'allocated_stock' => '',
            'reserved_stock' => 0,
        ]];
    }
@endphp

<style>
    .marketplace-listing-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        background: #fff;
    }
    .marketplace-listing-card + .marketplace-listing-card {
        margin-top: 12px;
    }
</style>

<div class="section-card mt-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="section-title mb-0">Marketplace Listings</div>
        <button type="button" class="btn btn-sm btn-outline-primary" id="add-marketplace-listing">Add Listing</button>
    </div>
    <div id="marketplace-listings-wrapper">
        @foreach ($marketplaceRows as $index => $listing)
            <div class="marketplace-listing-card" data-listing-row>
                <input type="hidden" name="marketplace_listings[{{ $index }}][id]" value="{{ $listing['id'] ?? '' }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Platform</label>
                        <select class="form-select" name="marketplace_listings[{{ $index }}][platform]">
                            <option value="">Select Platform</option>
                            @foreach ($supportedPlatforms as $platformKey => $platformLabel)
                                <option value="{{ $platformKey }}" {{ ($listing['platform'] ?? '') === $platformKey ? 'selected' : '' }}>{{ $platformLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Platform SKU</label>
                        <input type="text" class="form-control" name="marketplace_listings[{{ $index }}][platform_sku]" value="{{ $listing['platform_sku'] ?? '' }}" placeholder="AMZ-SKU-001">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Account</label>
                        <input type="text" class="form-control" name="marketplace_listings[{{ $index }}][account_name]" value="{{ $listing['account_name'] ?? 'Primary Account' }}" placeholder="Flipkart Account 1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Marketplace Item ID</label>
                        <input type="text" class="form-control" name="marketplace_listings[{{ $index }}][marketplace_item_id]" value="{{ $listing['marketplace_item_id'] ?? '' }}" placeholder="ASIN / FSN / Listing ID">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="marketplace_listings[{{ $index }}][listing_status]">
                            @foreach ($listingStatuses as $statusKey => $statusLabel)
                                <option value="{{ $statusKey }}" {{ ($listing['listing_status'] ?? 'active') === $statusKey ? 'selected' : '' }}>{{ $statusLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Listing Title</label>
                        <input type="text" class="form-control" name="marketplace_listings[{{ $index }}][listing_title]" value="{{ $listing['listing_title'] ?? '' }}" placeholder="Marketplace listing title">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Pack Size</label>
                        <input type="text" class="form-control" name="marketplace_listings[{{ $index }}][pack_size]" value="{{ $listing['pack_size'] ?? '' }}" placeholder="1 unit / 2 pack">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fulfillment</label>
                        <select class="form-select" name="marketplace_listings[{{ $index }}][fulfillment_type]">
                            <option value="">Select Fulfillment</option>
                            @foreach ($fulfillmentTypes as $fulfillmentKey => $fulfillmentLabel)
                                <option value="{{ $fulfillmentKey }}" {{ ($listing['fulfillment_type'] ?? '') === $fulfillmentKey ? 'selected' : '' }}>{{ $fulfillmentLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Selling Price</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="marketplace_listings[{{ $index }}][selling_price]" value="{{ $listing['selling_price'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">MRP</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="marketplace_listings[{{ $index }}][mrp]" value="{{ $listing['mrp'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Allocated Stock</label>
                        <input type="number" min="0" class="form-control" name="marketplace_listings[{{ $index }}][allocated_stock]" value="{{ $listing['allocated_stock'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Reserved Stock</label>
                        <input type="number" min="0" class="form-control" name="marketplace_listings[{{ $index }}][reserved_stock]" value="{{ $listing['reserved_stock'] ?? 0 }}">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger w-100 remove-marketplace-listing">Remove</button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<template id="marketplace-listing-template">
    <div class="marketplace-listing-card" data-listing-row>
        <input type="hidden" name="marketplace_listings[__INDEX__][id]" value="">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Platform</label>
                <select class="form-select" name="marketplace_listings[__INDEX__][platform]">
                    <option value="">Select Platform</option>
                    @foreach ($supportedPlatforms as $platformKey => $platformLabel)
                        <option value="{{ $platformKey }}">{{ $platformLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Platform SKU</label>
                <input type="text" class="form-control" name="marketplace_listings[__INDEX__][platform_sku]" placeholder="AMZ-SKU-001">
            </div>
            <div class="col-md-3">
                <label class="form-label">Account</label>
                <input type="text" class="form-control" name="marketplace_listings[__INDEX__][account_name]" value="Primary Account" placeholder="Flipkart Account 1">
            </div>
            <div class="col-md-3">
                <label class="form-label">Marketplace Item ID</label>
                <input type="text" class="form-control" name="marketplace_listings[__INDEX__][marketplace_item_id]" placeholder="ASIN / FSN / Listing ID">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="marketplace_listings[__INDEX__][listing_status]">
                    @foreach ($listingStatuses as $statusKey => $statusLabel)
                        <option value="{{ $statusKey }}" {{ $statusKey === 'active' ? 'selected' : '' }}>{{ $statusLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Listing Title</label>
                <input type="text" class="form-control" name="marketplace_listings[__INDEX__][listing_title]" placeholder="Marketplace listing title">
            </div>
            <div class="col-md-3">
                <label class="form-label">Pack Size</label>
                <input type="text" class="form-control" name="marketplace_listings[__INDEX__][pack_size]" placeholder="1 unit / 2 pack">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fulfillment</label>
                <select class="form-select" name="marketplace_listings[__INDEX__][fulfillment_type]">
                    <option value="">Select Fulfillment</option>
                    @foreach ($fulfillmentTypes as $fulfillmentKey => $fulfillmentLabel)
                        <option value="{{ $fulfillmentKey }}">{{ $fulfillmentLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Selling Price</label>
                <input type="number" step="0.01" min="0" class="form-control" name="marketplace_listings[__INDEX__][selling_price]" value="0">
            </div>
            <div class="col-md-3">
                <label class="form-label">MRP</label>
                <input type="number" step="0.01" min="0" class="form-control" name="marketplace_listings[__INDEX__][mrp]" value="0">
            </div>
            <div class="col-md-3">
                <label class="form-label">Allocated Stock</label>
                <input type="number" min="0" class="form-control" name="marketplace_listings[__INDEX__][allocated_stock]">
            </div>
            <div class="col-md-2">
                <label class="form-label">Reserved Stock</label>
                <input type="number" min="0" class="form-control" name="marketplace_listings[__INDEX__][reserved_stock]" value="0">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger w-100 remove-marketplace-listing">Remove</button>
            </div>
        </div>
    </div>
</template>

<script>
    (function () {
        const wrapper = document.getElementById('marketplace-listings-wrapper');
        const template = document.getElementById('marketplace-listing-template');
        const addBtn = document.getElementById('add-marketplace-listing');
        if (!wrapper || !template || !addBtn) {
            return;
        }

        const nextIndex = () => wrapper.querySelectorAll('[data-listing-row]').length;

        addBtn.addEventListener('click', function () {
            const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex()));
            wrapper.insertAdjacentHTML('beforeend', html);
        });

        wrapper.addEventListener('click', function (event) {
            if (!event.target.classList.contains('remove-marketplace-listing')) {
                return;
            }

            const rows = wrapper.querySelectorAll('[data-listing-row]');
            if (rows.length === 1) {
                rows[0].querySelectorAll('input, select').forEach((field) => {
                    if (field.type === 'hidden') {
                        field.value = '';
                    } else if (field.tagName === 'SELECT') {
                        field.selectedIndex = 0;
                    } else {
                        field.value = '';
                    }
                });
                return;
            }

            event.target.closest('[data-listing-row]')?.remove();
        });
    })();
</script>
