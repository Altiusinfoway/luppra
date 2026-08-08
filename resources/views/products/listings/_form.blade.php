<div class="row g-3">
    <div class="col-12">
        <div class="border rounded-4 p-3 bg-light-subtle" style="border-color:#dbeafe !important; background:linear-gradient(135deg, #f8fbff 0%, #eff6ff 100%) !important;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h6 class="mb-1">Add Marketplace Account</h6>
                    <p class="text-muted mb-0">If the needed Amazon or Flipkart account is missing, create it here and then select it below.</p>
                </div>
                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#quickMarketplaceAccountForm" aria-expanded="false" aria-controls="quickMarketplaceAccountForm">
                    Add Account
                </button>
            </div>

            <div class="collapse" id="quickMarketplaceAccountForm">
                <form action="{{ route('products.marketplace.accounts.store') }}" method="POST" class="row g-3">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ url()->full() }}">

                    <div class="col-md-4">
                        <label class="form-label">Platform</label>
                        <input type="text" class="form-control" name="platform" list="platform-suggestions" placeholder="Amazon, Flipkart, Meesho..." required>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label">Account Name</label>
                        <input type="text" class="form-control" name="name" placeholder="Example: Amazon Account 1" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label d-block">Status</label>
                        <div class="form-check form-switch pt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="quick-account-active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="quick-account-active">Active</label>
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary btn-sm">Save Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Platform</label>
        <input type="text" class="form-control" name="platform" id="listing-platform" list="platform-suggestions" value="{{ old('platform', $listing->platform) }}" placeholder="Amazon, Flipkart, Meesho..." required>
        @error('platform')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-md-6">
        <div class="d-flex justify-content-between align-items-center gap-2">
            <label class="form-label mb-0">Marketplace Account</label>
            <a href="{{ route('products.marketplace.accounts.index') }}" class="small text-decoration-none">Manage Accounts</a>
        </div>
        <select class="form-select" name="marketplace_account_id" id="listing-account" required data-selected-account="{{ old('marketplace_account_id', $listing->marketplace_account_id) }}">
            <option value="">Select Marketplace Account</option>
            @foreach ($marketplaceAccounts as $account)
                <option value="{{ $account->id }}" data-platform="{{ $account->platform }}" data-account-name="{{ $account->name }}" {{ (string) old('marketplace_account_id', $listing->marketplace_account_id) === (string) $account->id ? 'selected' : '' }}>
                    {{ ucfirst($account->platform) }} / {{ $account->name }}
                </option>
            @endforeach
        </select>
        @if ($marketplaceAccounts->isEmpty())
            <small class="text-muted d-block mt-1">No active marketplace accounts found yet. Create one above first.</small>
        @endif
        @error('marketplace_account_id')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Platform SKU</label>
        <input type="text" class="form-control" name="platform_sku" value="{{ old('platform_sku', $listing->platform_sku) }}" placeholder="AMZ-SKU-001" required>
        @error('platform_sku')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <input type="hidden" name="account_name" id="listing-account-name" value="{{ old('account_name', $listing->account_name ?? '') }}">

    <div class="col-md-6">
        <label class="form-label">Marketplace Item ID</label>
        <input type="text" class="form-control" name="marketplace_item_id" value="{{ old('marketplace_item_id', $listing->marketplace_item_id) }}" placeholder="ASIN / FSN / Listing ID">
        @error('marketplace_item_id')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Status</label>
        <select class="form-select" name="listing_status" required>
            @foreach ($listingStatuses as $statusKey => $statusLabel)
                <option value="{{ $statusKey }}" {{ old('listing_status', $listing->listing_status ?? 'active') === $statusKey ? 'selected' : '' }}>
                    {{ $statusLabel }}
                </option>
            @endforeach
        </select>
        @error('listing_status')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Listing Title</label>
        <input type="text" class="form-control" name="listing_title" value="{{ old('listing_title', $listing->listing_title) }}" placeholder="Marketplace listing title" required>
        @error('listing_title')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Pack Size</label>
        <input type="text" class="form-control" name="pack_size" value="{{ old('pack_size', $listing->pack_size) }}" placeholder="1 unit / 2 pack">
        @error('pack_size')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Fulfillment Type</label>
        <select class="form-select" name="fulfillment_type">
            <option value="">Select Fulfillment</option>
            @foreach ($fulfillmentTypes as $fulfillmentKey => $fulfillmentLabel)
                <option value="{{ $fulfillmentKey }}" {{ old('fulfillment_type', $listing->fulfillment_type) === $fulfillmentKey ? 'selected' : '' }}>
                    {{ $fulfillmentLabel }}
                </option>
            @endforeach
        </select>
        @error('fulfillment_type')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Reserved Stock</label>
        <input type="number" min="0" class="form-control" name="reserved_stock" value="{{ old('reserved_stock', $listing->reserved_stock ?? 0) }}">
        @error('reserved_stock')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Selling Price</label>
        <input type="number" step="0.01" min="0" class="form-control" name="selling_price" value="{{ old('selling_price', $listing->selling_price) }}">
        @error('selling_price')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">MRP</label>
        <input type="number" step="0.01" min="0" class="form-control" name="mrp" value="{{ old('mrp', $listing->mrp) }}">
        @error('mrp')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Base Price Override</label>
        <input type="number" step="0.01" min="0" class="form-control" name="base_price" value="{{ old('base_price', $listing->base_price) }}" placeholder="Optional per-listing cost">
        @error('base_price')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Allocated Stock</label>
        <input type="number" min="0" class="form-control" name="allocated_stock" value="{{ old('allocated_stock', $listing->allocated_stock) }}">
        @error('allocated_stock')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-12">
        <div class="border rounded-4 p-3 bg-light-subtle" style="border-color:#dbeafe !important; background:linear-gradient(135deg, #f8fbff 0%, #eff6ff 100%) !important;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h6 class="mb-1">Marketplace Analytics Sync</h6>
                    <p class="text-muted mb-0">These fields can be filled by Amazon or Flipkart API sync and will drive the listing analytics dashboard.</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">API Orders</label>
                    <input type="number" min="0" class="form-control" name="external_orders_count" value="{{ old('external_orders_count', $listing->external_orders_count ?? 0) }}">
                    @error('external_orders_count')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">API Sold Qty</label>
                    <input type="number" step="0.01" min="0" class="form-control" name="external_sold_qty" value="{{ old('external_sold_qty', $listing->external_sold_qty ?? 0) }}">
                    @error('external_sold_qty')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">API Revenue</label>
                    <input type="number" step="0.01" min="0" class="form-control" name="external_revenue" value="{{ old('external_revenue', $listing->external_revenue ?? 0) }}">
                    @error('external_revenue')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">Last Synced At</label>
                    <input type="datetime-local" class="form-control" name="external_last_synced_at" value="{{ old('external_last_synced_at', !empty($listing->external_last_synced_at) ? $listing->external_last_synced_at->format('Y-m-d\\TH:i') : '') }}">
                    @error('external_last_synced_at')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Sync Note</label>
                    <input type="text" class="form-control" name="external_sync_note" value="{{ old('external_sync_note', $listing->external_sync_note) }}" placeholder="Optional note from sync job or operator">
                    @error('external_sync_note')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

<datalist id="platform-suggestions">
    @foreach (($platformSuggestions ?? collect()) as $platformOption)
        <option value="{{ $platformOption }}">{{ ucwords($platformOption) }}</option>
    @endforeach
</datalist>

<script>
    (function () {
        const platformSelect = document.getElementById('listing-platform');
        const accountSelect = document.getElementById('listing-account');
        const accountNameInput = document.getElementById('listing-account-name');

        if (!platformSelect || !accountSelect || !accountNameInput) {
            return;
        }

        const getSelectedOption = function () {
            return accountSelect.options[accountSelect.selectedIndex] || null;
        };

        const syncAccountMeta = function () {
            const selectedOption = getSelectedOption();
            const selectedPlatform = selectedOption && selectedOption.value
                ? (selectedOption.dataset.platform || '')
                : '';

            if (selectedPlatform) {
                platformSelect.value = selectedPlatform;
            }

            accountNameInput.value = selectedOption && selectedOption.value
                ? (selectedOption.dataset.accountName || '')
                : '';
        };

        const syncAccounts = function () {
            const platform = platformSelect.value;
            const currentValue = accountSelect.value || accountSelect.dataset.selectedAccount || '';

            Array.from(accountSelect.options).forEach(function (option, index) {
                if (index === 0) {
                    option.hidden = false;
                    return;
                }

                const optionPlatform = option.dataset.platform || '';
                const visible = !platform || optionPlatform === platform;
                option.hidden = !visible;

                if (!visible && option.selected) {
                    option.selected = false;
                }
            });

            if (currentValue) {
                const matchingOption = Array.from(accountSelect.options).find(function (option, index) {
                    return index > 0 && !option.hidden && option.value === currentValue;
                });

                if (matchingOption) {
                    matchingOption.selected = true;
                }
            }

            if (accountSelect.selectedIndex <= 0) {
                accountSelect.value = '';
            }

            syncAccountMeta();
        };

        platformSelect.addEventListener('change', syncAccounts);
        accountSelect.addEventListener('change', function () {
            accountSelect.dataset.selectedAccount = accountSelect.value || '';
            syncAccountMeta();
        });

        syncAccounts();
    })();
</script>
