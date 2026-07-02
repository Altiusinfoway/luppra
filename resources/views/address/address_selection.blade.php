<style>
    .section-title {
        color: #2c3e50;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #3498db;
        display: flex;
        align-items: center;
    }

    .section-title i {
        margin-right: 10px;
        color: #3498db;
    }

    .address-cards-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .address-card {
        background-color: white;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        border: 2px solid #e0e0e0;
        position: relative;
        cursor: pointer;
    }

    .address-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        border-color: #3498db;
    }

    .address-card.selected {
        border-color: #2ecc71;
        background-color: #f9fdf7;
    }

    .address-card.selected::before {
        content: "\eb7b";
        /* ri-check-line */
        font-family: "remixicon";
        position: absolute;
        top: -12px;
        right: -12px;
        background-color: #2ecc71;
        color: #fff;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        z-index: 2;
    }

    .address-card-radio {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }


    .billing-type {
        background-color: #e74c3c;
    }

    .address-details {
        margin-bottom: 15px;
    }

    .address-line {
        font-size: 0.8rem;
        margin-bottom: 8px;
        color: #2c3e50;
    }

    .address-city,
    .address-state,
    .address-zip,
    .address-country {
        display: inline-block;
        margin-right: 15px;
        color: #555;
        font-size: 0.95rem;
    }

    .address-label {
        font-weight: 600;
        color: #7f8c8d;
    }

    .add-address-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: white;
        border: 2px dashed #bdc3c7;
        border-radius: 10px;
        padding: 25px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        color: #7f8c8d;
        font-weight: 600;
        font-size: 1.1rem;
        grid-column: span 1;
    }

    .add-address-btn:hover {
        border-color: #3498db;
        color: #3498db;
        background-color: #f5f9ff;
    }

    .add-address-btn i {
        font-size: 1.5rem;
        margin-right: 10px;
    }

    .action-buttons {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 40px;
    }


    @media (max-width: 768px) {
        .address-cards-container {
            grid-template-columns: 1fr;
        }

        .action-buttons {
            flex-direction: column;
        }


    }
</style>

@php

    $shipping_addresses = collect([]);
    $billing_addresses = collect([]);

    if (isset($company)) {
        $shipping_addresses = $company?->getShippingAddress()->get() ?? collect([]);

        $billing_addresses = $company?->getBillingAddress()->get() ?? collect([]);
        $fallbackAddress = $company?->getAddress;

        if ($billing_addresses->isEmpty() && $fallbackAddress) {
            $billing_addresses = collect([$fallbackAddress]);
        }
        if ($shipping_addresses->isEmpty() && $fallbackAddress) {
            $shipping_addresses = collect([$fallbackAddress]);
        }
    }

@endphp

<div class="row">
    <div class="card mt-3 mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Billing & Shipping Address</h6>
            {{-- <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
            + Add New Address
            </button> --}}
        </div>

        <div class="card-body">
            <div class="row">

                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h4 class="section-title"><i class=" ri-bill-line"></i> Billing Address</h4>

                    <div class="address-cards-container">

                        @if ($billing_addresses)
                            @foreach ($billing_addresses as $address)
                                <label class="address-card
                                       {{ ($billing_address_id !== null && $address->id == $billing_address_id)

                                    ? 'selected'
                                    : '' }}"

                                    id="lbl-billing-address-{{ $address->id }}">
                                    <input type="radio" name="billing_address" class="address-card-radio"
                                        id="billing-address-{{ $address->id }}"
                                        value="{{ $address->id }}"
                                         {{ ($billing_address_id !== null && $address->id == $billing_address_id)
                                            || ($billing_address_id == null && $loop->first)
                                            ? 'checked'
                                            : '' }}
                                        >

                                    <div class="address-details">
                                        <!--  edit address -------- -->
                                        <a href="javascript:void(0);" class="btn btn-sm btn-primary" data-size="md"
                                            data-url="{{ route('addresses.create', ['type' => 'billing', 'company_id' => $company->id ?? 0, 'id' => $address->id]) }}"
                                            data-ajax-popup="true" data-bs-original-title="{{ __('Edit Address') }}">
                                            <div class="" id="add-billing-address">
                                                <i class="ri-edit-fill"></i>
                                                <span>Edit</span>
                                            </div>
                                        </a>

                                        <div class="address-line mt-2">
                                            {{ $address->address_line_1 ?? '' }}
                                            {{ $address->address_line_2 ?? '' }}
                                        </div>
                                        <div>
                                            <span class="address-label">City:</span>
                                            <span class="address-city">
                                                {{ $address->get_city?->name ?? '' }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="address-label">State:</span>
                                            <span class="address-state">
                                                {{ $address->get_state?->name ?? '' }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="address-label">Zip Code:</span>
                                            <span class="address-zip">
                                                {{ $address->zipcode ?? '' }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="address-label">Country:</span>
                                            <span class="address-country">
                                                {{ $address->get_country?->name ?? '' }}
                                            </span>
                                        </div>
                                    </div>

                                </label>
                            @endforeach
                        @endif

                        <!-- Add New Address Button -->
                        @if ($billing_addresses && count($billing_addresses) == 0)
                            <a href="javascript:void(0);" data-size="md"
                                data-url="{{ route('addresses.create', ['type' => 'billing', 'company_id' => $company->id ?? 0]) }}"
                                data-ajax-popup="true" data-bs-original-title="{{ __('Create Address') }}">
                                <div class="add-address-btn" id="add-billing-address">
                                    <i class="ri-add-circle-line"></i>
                                    <span>Add New Billing Address</span>
                                </div>
                            </a>
                        @endif
                    </div>
                </div>


                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h4 class="section-title"><i class="ri-truck-line"></i> Shipping Address </h4>
                    <div class="address-cards-container">

                        @if ($shipping_addresses->isNotEmpty())
                            @foreach ($shipping_addresses as $address)
                                <label
                                    class="address-card
                                    {{ ($shipping_address_id !== null && $address->id == $shipping_address_id)
                                    ? 'selected'
                                    : '' }}"
                                    id="lbl-shipping-address-{{ $address->id }}">
                                    <input type="radio" name="shipping_address" class="address-card-radio"
                                        id="shipping-address-{{ $address->id }}"
                                        value="{{ $address->id }}"
                                            {{ ($shipping_address_id !== null && $address->id == $shipping_address_id)
                                            || ($shipping_address_id == null && $loop->first)
                                            ? 'checked'
                                            : '' }}
                                            >

                                    <div class="address-details">

                                        <!--  edit address -------- -->
                                        <a href="javascript:void(0);" class="btn btn-sm btn-primary" data-size="md"
                                            data-url="{{ route('addresses.create', ['type' => 'shipping', 'company_id' => $company->id ?? 0, 'id' => $address->id]) }}"
                                            data-ajax-popup="true" data-bs-original-title="{{ __('Edit Address') }}">
                                            <div class="" id="add-billing-address">
                                                <i class="ri-edit-fill"></i>
                                                <span>Edit</span>
                                            </div>
                                        </a>


                                        <div class="address-line mt-2">
                                            {{ $address->address_line_1 ?? '' }}
                                            {{ $address->address_line_2 ?? ''  }}
                                        </div>
                                        <div>
                                            <span class="address-label">City:</span>
                                            <span class="address-city">
                                                {{ $address->get_city?->name ?? '' }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="address-label">State:</span>
                                            <span class="address-state">
                                                {{ $address->get_state?->name ?? '' }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="address-label">Zip Code:</span>
                                            <span class="address-zip">
                                                {{ $address->zipcode ?? '' }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="address-label">Country:</span>
                                            <span class="address-country">
                                                {{ $address->get_country?->name ?? '' }}
                                            </span>
                                        </div>
                                    </div>

                                </label>
                            @endforeach
                        @endif

                        @if ($shipping_addresses && count($shipping_addresses) == 0)
                        <a href="javascript:void(0);" class="add-shipping-address-btn" data-size="md"
                            data-url="{{ route('addresses.create', ['type' => 'shipping', 'company_id' => $company->id ?? 0]) }}"
                            data-ajax-popup="true" data-bs-original-title="{{ __('Create Address') }}">
                            <div class="add-address-btn" id="add-shipping-address">
                                <i class="ri-add-circle-line"></i>
                                <span>Add New Shipping Address</span>
                            </div>
                        </a>
                        @endif

                    </div>
                </div>


            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Select address cards when clicked
        initAddressCardEvents();


        // Set default selections
        @if (isset($data) && isset($data['shipping_address_id']) && $data['shipping_address_id'] != '')

            document.getElementById("shipping-address-{{ $data['shipping_address_id'] }}").click();
        @endif

        @if (isset($data) && isset($data['billing_address_id']) && $data['billing_address_id'] != '')

            document.getElementById("billing-address-{{ $data['billing_address_id'] }}").click();
        @endif

    });
</script>
