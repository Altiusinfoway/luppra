@extends('layouts.app')

@section('content')
    <style>
        .star {
            display: none;
        }

        .star+label {
            font-size: 24px;
            color: #ccc;
            cursor: pointer;
        }

        .star:checked~label {
            color: #ffc700;
        }

        .star+label:hover,
        .star+label:hover~label {
            color: #deb217;
        }

        .big-checkbox {
            transform: scale(1.6);
            margin-top: 4px;
            cursor: pointer;
        }
    </style>
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Customer</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customer </a></li>
                                <li class="breadcrumb-item active">Edit</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end page title -->
            <div class="row">
                <!-- Varying Modal Content -->
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title  mb-0">Customer Edit</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('customers.update', $customer->id) }}" enctype="multipart/form-data"
                                method="post" id="editcustomerForm">
                                @csrf

                                <div class="row">

                                    <!-- ---------------------- Customer Detail ---------------------- -->
                                    <div class="col-md-12 mb-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="mb-0">Customer Details</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-lg-6 border-end">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label" for="name">Name <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="text" name="name"
                                                                    class="form-control form-control-sm" id="name"
                                                                    placeholder="Enter name"
                                                                    value="{{ $customer['name'] }}">
                                                                <span class="text-danger" id="error-name"></span>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label" for="email">Email </label>
                                                                <input type="email" name="email"
                                                                    class="form-control form-control-sm" id="email"
                                                                    placeholder="Enter Email"
                                                                    value="{{ $customer['email'] }}">
                                                                <span class="text-danger" id="error-email"></span>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label>Company Name <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="text" name="company_name"
                                                                    class="form-control form-control-sm"
                                                                    placeholder="Enter Company Name"
                                                                    value="{{ $customer['company_name'] }}">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label>Company GST No</label>
                                                                <input type="text" name="gst_no"
                                                                    class="form-control form-control-sm"
                                                                    placeholder="Enter Company GST No"
                                                                    value="{{ $customer['gst_no'] }}">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label>Company Adhar No</label>
                                                                <input type="text" name="company_adhar_no"
                                                                    class="form-control form-control-sm"
                                                                    placeholder="Enter Company Adhar No"
                                                                    value="{{ $customer['company_adhar_no'] }}">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label>Company Udhyam No</label>
                                                                <input type="text" name="company_udhyam_no"
                                                                    class="form-control form-control-sm"
                                                                    placeholder="Enter Company Udhyam No"
                                                                    value="{{ $customer['company_udhyam_no'] }}">
                                                            </div>
                                                            <div class="col-md-12">
                                                                <label for="lead_type_id" class="form-label">Lead Type </label>
                                                                <select name="lead_type_id"
                                                                    class="form-control form-control-sm">
                                                                    <option value="">Select Lead Type</option>
                                                                    @foreach ($lead_type_list as $id => $name)
                                                                        <option value="{{ $id }}"
                                                                            @if (isset($customer) && $customer->lead_type_id == $id) selected @endif>
                                                                            {{ $name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <span class="text-danger" id="error-lead_type_id"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- ---------------------- Phone Numbers ---------------------- -->
                                                    <div class="col-lg-6">
                                                        <h6 class="mb-3">Phone Numbers</h6>
                                                        <div class="row py-3 border-bottom">
                                                            <div class="text-end">
                                                                <button type="button" id="add_more_phone"
                                                                    class="btn btn-sm btn-primary">Add Phone</button>
                                                            </div>
                                                        </div>

                                                        <div id="phone-container">
                                                            @if ($cust_phone_list->count())
                                                                @foreach ($cust_phone_list as $k => $phone)
                                                                    <input type="hidden"
                                                                        name="phones[{{ $k }}][id]"
                                                                        value="{{ $phone->id }}">
                                                                    <div
                                                                        class="row align-items-end phone-row border-bottom py-3">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Phone <span
                                                                                    class="text-danger">*</span></label>
                                                                            <input type="tel"
                                                                                name="phones[{{ $k }}][phone]"
                                                                                class="form-control form-control-sm"
                                                                                value="{{ $phone->phone }}"
                                                                                placeholder="Enter Phone">
                                                                            <span class="text-danger error-message"></span>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label class="form-label d-block">Phone
                                                                                Type</label>
                                                                            <div class="form-check form-check-inline">
                                                                                <input class="form-check-input"
                                                                                    type="radio"
                                                                                    name="phones[{{ $k }}][phone_type]"
                                                                                    value="primary"
                                                                                    {{ $phone->is_primary ? 'checked' : '' }}>
                                                                                <label
                                                                                    class="form-check-label">Primary</label>
                                                                            </div>
                                                                            <div class="form-check form-check-inline">
                                                                                <input class="form-check-input"
                                                                                    type="radio"
                                                                                    name="phones[{{ $k }}][phone_type]"
                                                                                    value="secondary"
                                                                                    {{ $phone->is_secondary ? 'checked' : '' }}>
                                                                                <label
                                                                                    class="form-check-label">Secondary</label>
                                                                            </div>
                                                                            <span class="text-danger error-message"
                                                                                id="error-phones-{{ $k }}-phone"></span>
                                                                        </div>
                                                                        <div class="col-md-2">
                                                                            <label
                                                                                class="form-label d-block">WhatsApp</label>
                                                                            <div class="form-check">
                                                                                <input class="form-check-input"
                                                                                    type="checkbox"
                                                                                    name="phones[{{ $k }}][is_whatsapp]"
                                                                                    value="1"
                                                                                    {{ $phone->is_whatsapp ? 'checked' : '' }}>
                                                                                <label class="form-check-label">Yes</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-2 d-flex">
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-danger remove-phone">Remove</button>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @else
                                                                <input type="hidden" name="phones[0][id]">
                                                                <div class="row align-items-end phone-row mb-3">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Phone <span
                                                                                class="text-danger">*</span></label>
                                                                        <input type="tel" name="phones[0][phone]"
                                                                            class="form-control form-control-sm"
                                                                            placeholder="Enter Phone">
                                                                        <span class="text-danger error-message"></span>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label d-block">Phone
                                                                            Type</label>
                                                                        <div class="form-check form-check-inline">
                                                                            <input class="form-check-input" type="radio"
                                                                                name="phones[0][phone_type]"
                                                                                value="primary">
                                                                            <label class="form-check-label">Primary</label>
                                                                        </div>
                                                                        <div class="form-check form-check-inline">
                                                                            <input class="form-check-input" type="radio"
                                                                                name="phones[0][phone_type]"
                                                                                value="secondary">
                                                                            <label
                                                                                class="form-check-label">Secondary</label>
                                                                        </div>
                                                                        <span class="text-danger error-message"
                                                                            id="error-phones-0-phone"></span>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <label class="form-label d-block">WhatsApp</label>
                                                                        <div class="form-check">
                                                                            <input class="form-check-input"
                                                                                type="checkbox"
                                                                                name="phones[0][is_whatsapp]"
                                                                                value="1">
                                                                            <label class="form-check-label">Yes</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ---------------------- Address Detail ---------------------- -->
                                    <div class="col-md-12 mb-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="mb-0">Address Details</h5>
                                            </div>
                                            <div class="card-body">
                                                <div id="company-container">
                                                    @php
                                                        $billing =
                                                            $address_list[0] ??
                                                            (object) [
                                                                'country' => '',
                                                                'state' => '',
                                                                'city' => '',
                                                                'zipcode' => '',
                                                                'address_line_1' => '',
                                                                'address_line_2' => '',
                                                            ];
                                                        $shipping =
                                                            $address_list[1] ??
                                                            (object) [
                                                                'country' => '',
                                                                'state' => '',
                                                                'city' => '',
                                                                'zipcode' => '',
                                                                'address_line_1' => '',
                                                                'address_line_2' => '',
                                                            ];
                                                    @endphp

                                                    <div class="row mt-3 company-block">
                                                        <input type="hidden" name="companies[0][address_id]"
                                                            value="">

                                                        <!-- Billing Address -->
                                                        <div class="col-md-6 border-end">
                                                            <h6 class="text-center">Billing Address</h6>
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label>Country</label>
                                                                    <select name="companies[0][billing_country]"
                                                                        class="form-control form-control-sm country">
                                                                        <option value="">Select Country</option>
                                                                        @foreach ($country_list as $id => $name)
                                                                            <option value="{{ $id }}"
                                                                                {{ ($billing->get_country->id ?? '') == $id ? 'selected' : '' }}>
                                                                                {{ $name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label>State</label>
                                                                    <select name="companies[0][billing_state]"
                                                                        class="form-control form-control-sm state"
                                                                        data-selected="{{ $billing->state }}">
                                                                        <option value="">Select State</option>
                                                                        @if (!empty($billing->state))
                                                                            <option value="{{ $billing->state }}"
                                                                                {{ ($billing->get_state->id ?? '') == $billing->state ? 'selected' : '' }}>
                                                                                {{ $billing->get_state->name }}</option>
                                                                        @endif
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label>City</label>
                                                                    <select name="companies[0][billing_city]"
                                                                        class="form-control form-control-sm city">
                                                                        <option value="">Select City</option>
                                                                        @if (!empty($billing->city))
                                                                            <option value="{{ $billing->city }}"
                                                                                {{ ($billing->get_city->id ?? '') == $billing->city ? 'selected' : '' }}>
                                                                                {{ $billing->get_city->name }}</option>
                                                                        @endif
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label>Zipcode</label>
                                                                    <input type="text"
                                                                        name="companies[0][billing_zipcode]"
                                                                        class="form-control form-control-sm"
                                                                        value="{{ $billing->zipcode ?? '' }}">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label>Address Line 1</label>
                                                                    <textarea name="companies[0][billing_address_line_1]" class="form-control form-control-sm" rows="5">{{ $billing->address_line_1 ?? '' }}</textarea>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label>Address Line 2</label>
                                                                    <textarea name="companies[0][billing_address_line_2]" class="form-control form-control-sm" rows="5">{{ $billing->address_line_2 ?? '' }}</textarea>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Shipping Address -->
                                                        <div class="col-md-6 shipping-block" id="shipping_block_0">
                                                            <h6 class="text-center">Shipping Address</h6>
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label>Country</label>
                                                                    <select name="companies[0][shipping_country]"
                                                                        class="form-control form-control-sm country">
                                                                        <option value="">Select Country</option>
                                                                        @foreach ($country_list as $id => $name)
                                                                            <option value="{{ $id }}"
                                                                                {{ ($shipping->get_country->id ?? '') == $id ? 'selected' : '' }}>
                                                                                {{ $name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label>State</label>
                                                                    <select name="companies[0][shipping_state]"
                                                                        class="form-control form-control-sm state">
                                                                        <option value="">Select State</option>
                                                                        @if (!empty($shipping->state))
                                                                            <option value="{{ $shipping->state }}"
                                                                                {{ ($shipping->get_state->id ?? '') == $shipping->state ? 'selected' : '' }}>
                                                                                {{ $shipping->get_state->name ?? '' }}
                                                                            </option>
                                                                        @endif
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label>City</label>
                                                                    <select name="companies[0][shipping_city]"
                                                                        class="form-control form-control-sm city">
                                                                        <option value="">Select City</option>
                                                                        @if (!empty($shipping->city))
                                                                            <option value="{{ $shipping->city }}"
                                                                                {{ ($shipping->get_city->id ?? '') == $shipping->city ? 'selected' : '' }}>
                                                                                {{ $shipping->get_city->name }}</option>
                                                                        @endif
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label>Zipcode</label>
                                                                    <input type="text"
                                                                        name="companies[0][shipping_zipcode]"
                                                                        class="form-control form-control-sm"
                                                                        value="{{ $shipping->zipcode ?? '' }}">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label>Address Line 1</label>
                                                                    <textarea name="companies[0][shipping_address_line_1]" class="form-control form-control-sm" rows="5">{{ $shipping->address_line_1 ?? '' }}</textarea>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label>Address Line 2</label>
                                                                    <textarea name="companies[0][shipping_address_line_2]" class="form-control form-control-sm" rows="5">{{ $shipping->address_line_2 ?? '' }}</textarea>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>

                                                <!-- Submit Button -->
                                                <div class="d-flex align-items-start gap-3 mt-3">
                                                    <button type="submit"
                                                        class="btn btn-sm btn-success btn-label ms-auto">
                                                        <i
                                                            class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>Submit
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </form>


                        </div>
                    </div>
                </div><!--end col-->
            </div><!--end row-->

        </div>
        <!-- container-fluid -->
    </div>
@endsection

@section('page-script')
    <script>
        $(document).ready(function () {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    /* ================= LOAD STATES ================= */
    function loadStates($country, $state, $city, selectedStateId = null, selectedCityId = null) {

        $state.prop('disabled', true).html('<option value="">Loading...</option>');
        $city.prop('disabled', true).html('<option value="">Select City</option>');

        if (!$country.val()) {
            $state.prop('disabled', false).html('<option value="">Select State</option>');
            return;
        }

        $.post("{{ route('get.states') }}", {
            country_id: $country.val()
        }, function (res) {

            $state.html('<option value="">Select State</option>');

            $.each(res.states, function (id, name) {
                $state.append(`<option value="${id}">${name}</option>`);
            });

            $state.prop('disabled', false);

            if (selectedStateId) {
                $state.val(String(selectedStateId)).trigger('change');

                // IMPORTANT: wait before loading cities
                setTimeout(function () {
                    loadCities($state, $city, selectedCityId);
                }, 100);
            }
        });
    }

    /* ================= LOAD CITIES ================= */
    function loadCities($state, $city, selectedCityId = null) {

        $city.prop('disabled', true).html('<option value="">Loading...</option>');

        if (!$state.val()) {
            $city.prop('disabled', false).html('<option value="">Select City</option>');
            return;
        }

        $.post("{{ route('get.cities') }}", {
            state_id: $state.val()
        }, function (res) {

            $city.html('<option value="">Select City</option>');

            $.each(res.cities, function (id, name) {
                $city.append(`<option value="${id}">${name}</option>`);
            });

            $city.prop('disabled', false);

            if (selectedCityId) {
                $city.val(String(selectedCityId));
            }
        });
    }

    /* ================= INITIAL PAGE LOAD (EDIT MODE) ================= */
    $('.company-block').each(function () {

        let $block = $(this);

        /* ---------- BILLING ---------- */
        let $billingCountry = $block.find('select[name*="[billing_country]"]');
        let $billingState   = $block.find('select[name*="[billing_state]"]');
        let $billingCity    = $block.find('select[name*="[billing_city]"]');

        let billingStateId = $billingState.val();
        let billingCityId  = $billingCity.val();

        if ($billingCountry.val()) {
            loadStates(
                $billingCountry,
                $billingState,
                $billingCity,
                billingStateId,
                billingCityId
            );
        }

        /* ---------- SHIPPING ---------- */
        let $shippingCountry = $block.find('select[name*="[shipping_country]"]');
        let $shippingState   = $block.find('select[name*="[shipping_state]"]');
        let $shippingCity    = $block.find('select[name*="[shipping_city]"]');

        let shippingStateId = $shippingState.val();
        let shippingCityId  = $shippingCity.val();

        if ($shippingCountry.val()) {
            loadStates(
                $shippingCountry,
                $shippingState,
                $shippingCity,
                shippingStateId,
                shippingCityId
            );
        }
    });

    /* ================= CHANGE EVENTS ================= */

    $(document).on('change', 'select[name*="[billing_country]"]', function () {
        let $block = $(this).closest('.company-block');
        loadStates(
            $(this),
            $block.find('select[name*="[billing_state]"]'),
            $block.find('select[name*="[billing_city]"]')
        );
    });

    $(document).on('change', 'select[name*="[billing_state]"]', function () {
        let $block = $(this).closest('.company-block');
        loadCities(
            $(this),
            $block.find('select[name*="[billing_city]"]')
        );
    });

    $(document).on('change', 'select[name*="[shipping_country]"]', function () {
        let $block = $(this).closest('.company-block');
        loadStates(
            $(this),
            $block.find('select[name*="[shipping_state]"]'),
            $block.find('select[name*="[shipping_city]"]')
        );
    });

    $(document).on('change', 'select[name*="[shipping_state]"]', function () {
        let $block = $(this).closest('.company-block');
        loadCities(
            $(this),
            $block.find('select[name*="[shipping_city]"]')
        );
    });

});
        // $(function() {
        //     $.ajaxSetup({
        //         headers: {
        //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //         }
        //     });

        //     // -------- Load States ----------
        //     function loadStates($country, $state, $city, selectedStateId = null, selectedCityId = null) {
        //         $state.prop('disabled', true).html('<option>Loading...</option>');
        //         $city.prop('disabled', true).html('<option value="">Select City</option>');

        //         if (!$country.val()) {
        //             $state.prop('disabled', false).html('<option value="">Select State</option>');
        //             $city.prop('disabled', false).html('<option value="">Select City</option>');
        //             return;
        //         }

        //         $.post("{{ route('get.states') }}", {
        //                 country_id: $country.val()
        //             })
        //             .done(function(res) {
        //                 $state.html('<option value="">Select State</option>');
        //                 $.each(res.states, function(id, name) {
        //                     let sel = (String(id) === String(selectedStateId)) ? ' selected' : '';
        //                     $state.append('<option value="' + id + '"' + sel + '>' + name +
        //                         '</option>');
        //                 });
        //                 $state.prop('disabled', false);

        //                 if (selectedStateId) {
        //                     loadCities($state, $city, selectedCityId);
        //                 }
        //             })
        //             .fail(function() {
        //                 $state.prop('disabled', false).html('<option value="">Error loading states</option>');
        //             });
        //     }

        //     // -------- Load Cities ----------
        //     function loadCities($state, $city, selectedCityId = null) {
        //         $city.prop('disabled', true).html('<option>Loading...</option>');

        //         if (!$state.val()) {
        //             $city.prop('disabled', false).html('<option value="">Select City</option>');
        //             return;
        //         }

        //         $.post("{{ route('get.cities') }}", {
        //                 state_id: $state.val()
        //             })
        //             .done(function(res) {
        //                 $city.html('<option value="">Select City</option>');
        //                 $.each(res.cities, function(id, name) {
        //                     let sel = (String(id) === String(selectedCityId)) ? ' selected' : '';
        //                     $city.append('<option value="' + id + '"' + sel + '>' + name + '</option>');
        //                 });
        //                 $city.prop('disabled', false);
        //             })
        //             .fail(function() {
        //                 $city.prop('disabled', false).html('<option value="">Error loading cities</option>');
        //             });
        //     }

        //     // -------- Event Handlers for Dynamic & Existing Blocks --------
        //     $(document).on('change', 'select[name*="[billing_country]"]', function() {
        //         let $block = $(this).closest('.company-block');
        //         let $state = $block.find('select[name*="[billing_state]"]');
        //         let $city = $block.find('select[name*="[billing_city]"]');

        //         loadStates($(this), $state, $city);
        //     });

        //     $(document).on('change', 'select[name*="[billing_state]"]', function() {
        //         let $block = $(this).closest('.company-block');
        //         let $city = $block.find('select[name*="[billing_city]"]');

        //         loadCities($(this), $city);
        //     });

        //     $(document).on('change', 'select[name*="[shipping_country]"]', function() {
        //         let $block = $(this).closest('.company-block');
        //         let $state = $block.find('select[name*="[shipping_state]"]');
        //         let $city = $block.find('select[name*="[shipping_city]"]');

        //         loadStates($(this), $state, $city);
        //     });

        //     $(document).on('change', 'select[name*="[shipping_state]"]', function() {
        //         let $block = $(this).closest('.company-block');
        //         let $city = $block.find('select[name*="[shipping_city]"]');

        //         loadCities($(this), $city);
        //     });
        // });
    </script>
    <script>
        $(document).ready(function() {

            let companyIndex = $("#company-container .company-block").length;

            // Add Company
            $(document).on("click", ".add_company", function() {
                companyIndex++;

                let newCompany = `
                <div class="row mt-3 company-block border rounded p-3">
                    <input type="hidden" name="companies[${companyIndex}][comp_id]" value="">

                    <div class="row">
                        <div class="col-md-2">
                            <label>Company Name</label>
                            <input type="text" name="companies[${companyIndex}][comp_name]" class="form-control form-control-sm ">
                        </div>
                        <div class="col-md-2">
                            <label>Email</label>
                            <input type="text" name="companies[${companyIndex}][comp_email]" class="form-control form-control-sm ">
                        </div>
                        <div class="col-md-2">
                            <label>Phone</label>
                            <input type="text" name="companies[${companyIndex}][comp_phone]" class="form-control form-control-sm ">
                        </div>
                        <div class="col-md-2">
                            <label>GST No</label>
                            <input type="text" name="companies[${companyIndex}][comp_gst_no]" class="form-control form-control-sm ">
                        </div>
                        <div class="col-md-2">
                            <label>Adhar No</label>
                            <input type="text" name="companies[${companyIndex}][comp_adhar_no]" class="form-control form-control-sm ">
                        </div>
                        <div class="col-md-2">
                            <label>Udhyam No</label>
                            <input type="text" name="companies[${companyIndex}][comp_udhyam_no]" class="form-control form-control-sm ">
                        </div>
                    </div>

                    <!-- Billing Address -->
                    <div class="col-md-6 mt-3">
                        <h5 class="text-center">Billing Address</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <label>Country</label>
                                <select name="companies[${companyIndex}][billing_country]" class="form-control form-control-sm  country">
                                    <option value="">Select Country</option>
                                    @foreach ($country_list as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>State</label>
                                <select name="companies[${companyIndex}][billing_state]" class="form-control form-control-sm  state">
                                    <option value="">Select State</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>City</label>
                                <select name="companies[${companyIndex}][billing_city]" class="form-control form-control-sm  city">
                                    <option value="">Select City</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Zipcode</label>
                                <input type="text" name="companies[${companyIndex}][billing_zipcode]" class="form-control form-control-sm ">
                            </div>
                            <div class="col-md-1">
                                <label class="me-2">Same</label>
                                 <input type="checkbox"
                               name="companies[${companyIndex}][is_same_adr]"
                               value="1"
                               class="form-check-input big-checkbox same-address"
                               data-target="shipping_block_${companyIndex}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Address Line 1</label>
                                <textarea  name="companies[${companyIndex}][billing_address_line_1]" class="form-control form-control-sm " ></textarea>
                            </div>
                            <div class="col-md-6">
                                <label>Address Line 2</label>
                                <textarea name="companies[${companyIndex}][billing_address_line_2]" class="form-control form-control-sm " ></textarea>
                            </div>
                        </div>

                    </div>

                    <!-- Shipping Address -->
                    <div class="col-md-6 mt-3 shipping-block" id="shipping_block_${companyIndex}">
                        <h5 class="text-center">Shipping Address</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <label>Country</label>
                                <select name="companies[${companyIndex}][shipping_country]" class="form-control form-control-sm  country">
                                    <option value="">Select Country</option>
                                    @foreach ($country_list as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>State</label>
                                <select name="companies[${companyIndex}][shipping_state]" class="form-control form-control-sm  state">
                                    <option value="">Select State</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>City</label>
                                <select name="companies[${companyIndex}][shipping_city]" class="form-control form-control-sm  city">
                                    <option value="">Select City</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Zipcode</label>
                                <input type="text" name="companies[${companyIndex}][shipping_zipcode]" class="form-control form-control-sm ">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Address Line 1</label>
                                <textarea  name="companies[${companyIndex}][shipping_address_line_1]" class="form-control form-control-sm " ></textarea>
                            </div>
                            <div class="col-md-6">
                                <label>Address Line 2</label>
                                <textarea name="companies[${companyIndex}][shipping_address_line_2]" class="form-control form-control-sm " ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Remove Btn -->
                    <div class="d-flex justify-content-end mt-3">
                        <button type="button" class="btn btn-danger remove-company">Remove</button>
                    </div>
                </div>`;

                $("#company-container").append(newCompany);
            });

            // Remove Company
            $(document).on("click", ".remove-company", function() {
                if ($(".company-block").length > 1) {
                    $(this).closest(".company-block").remove();
                } else {
                    alert('At least one company is required.');
                }
            });

            // Hide/Show Shipping Address
            function toggleShipping($checkbox) {
                let targetId = $checkbox.data("target");
                let $target = $("#" + targetId);
                if ($checkbox.is(":checked")) {
                    $target.hide();
                } else {
                    $target.show();
                }
            }

            $(document).on("change", ".same-address", function() {
                toggleShipping($(this));
            });

            // Run once on page load
            $(".same-address").each(function() {
                toggleShipping($(this));
            });

        });
    </script>

    <script>
        $(document).ready(function() {

            $(document).on('change', 'input[name^="phones"][type="radio"][value="primary"]', function() {
                $('input[name^="phones"][type="radio"][value="primary"]').not(this).prop('checked', false);
            });

            let phoneIndex = $("#phone-container .phone-row").length - 1;

            $(document).on("click", "#add_more_phone", function() {
                phoneIndex++;

                let newPhone = `
        <div class="row align-items-end phone-row mb-3">
            <input type="hidden" name="phones[${phoneIndex}][id]">
            <div class="col-md-4">
                <label class="form-label">Phone <span class="text-danger">*</span></label>
                <input type="tel" name="phones[${phoneIndex}][phone]" class="form-control form-control-sm " placeholder="Enter Phone">
                <span class="text-danger error-message" id="error-phones-${phoneIndex}-phone"></span>
            </div>

            <div class="col-md-4">
                <label class="form-label d-block">Phone Type</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="phones[${phoneIndex}][phone_type]" value="primary">
                    <label class="form-check-label">Primary</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="phones[${phoneIndex}][phone_type]" value="secondary">
                    <label class="form-check-label">Secondary</label>
                </div>
                <span class="text-danger error-message" id="error-phones-${phoneIndex}-phone_type"></span>
            </div>

            <div class="col-md-2">
                <label class="form-label d-block">WhatsApp</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="phones[${phoneIndex}][is_whatsapp]" value="1">
                    <label class="form-check-label">Yes</label>
                </div>
            </div>

            <div class="col-md-2 d-flex">
                <button type="button" class="btn btn-danger btn-sm remove-phone">Remove</button>
            </div>
        </div>`;

                $("#phone-container").append(newPhone);
            });

            // Reindex phone rows
            function reindexPhones() {
                $('#phone-container .phone-row').each(function(i) {
                    $(this).find('input, select').each(function() {
                        let name = $(this).attr('name');
                        if (name) {
                            name = name.replace(/phones\[\d+\]/, 'phones[' + i + ']');
                            $(this).attr('name', name);
                        }
                    });
                    $(this).find('.error-message').each(function() {
                        let id = $(this).attr('id');
                        if (id) {
                            id = id.replace(/phones-\d+-/, 'phones-' + i + '-');
                            $(this).attr('id', id);
                        }
                    });
                });
                phoneIndex = $('#phone-container .phone-row').length - 1;
            }

            // Remove Phone
            $(document).on("click", ".remove-phone", function() {
                if ($("#phone-container .phone-row").length > 1) {
                    $(this).closest(".phone-row").remove();
                    reindexPhones();
                } else {
                    alert('At least one phone is required.');
                }
            });
        });
    </script>

    <script>
        $('#editcustomerForm').on('submit', function(e) {
            e.preventDefault();

            let $form = $(this);
            let $submitBtn = $form.find('button[type="submit"]');

            // Disable submit button immediately
            $submitBtn.prop('disabled', true);

            // Clear previous errors
            $('.text-danger').text('');
            $('.error-message').text('');

            let isValid = true;

            // Validate phone numbers
            $('#phone-container .phone-row').each(function(index) {
                let phoneInput = $(this).find('input[name^="phones"][name$="[phone]"]');
                let phoneVal = phoneInput.val().trim();
                let errorSpan = $(this).find('.error-message').first();

                if (phoneVal === '') {
                    errorSpan.text('Phone is required.');
                    isValid = false;
                } else if (!/^\d{10}$/.test(phoneVal)) {
                    errorSpan.text('Phone must be 10 digits.');
                    isValid = false;
                }

                // Check if phone type is selected
                let phoneTypeSelected = $(this).find('input[name^="phones"][name$="[phone_type]"]:checked')
                    .length > 0;
                if (!phoneTypeSelected) {
                    $(this).find('.error-message').last().text('Please select phone type.');
                    isValid = false;
                }
            });

            if (!isValid) {
                show_toastr('error', 'Please fix phone errors before submitting.');
                // Keep the button disabled until page refresh or user reopens the form
                return;
            }

            // Validation passed, proceed with AJAX
            let formData = new FormData($form[0]);

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                success: function(res) {
                    if (res.error) {
                        show_toastr('error', res.message);
                        $submitBtn.prop('disabled', false); // Re-enable if server returns error
                    }

                    if (res.success) {
                        show_toastr('success', res.success || 'Customer Updated successfully');
                        setTimeout(function() {
                            window.location.href = res.redirect_url ||
                                "{{ route('customers.index') }}";
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    $submitBtn.prop('disabled', false); // Re-enable on AJAX error
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, messages) {
                            let message = messages[0];
                            if ($("#error-" + key).length) $("#error-" + key).text(message);
                            show_toastr("error", message);
                        });
                    } else {
                        show_toastr("error", "Something went wrong.");
                    }
                }
            });
        });
    </script>

    {{-- <script>
        $('#editcustomerForm').on('submit', function(e) {
            e.preventDefault();

            // Clear previous errors
            $('.text-danger').text('');
            $('.error-message').text('');

            let isValid = true;

            // Validate phone numbers
            $('#phone-container .phone-row').each(function(index) {
                let phoneInput = $(this).find('input[name^="phones"][name$="[phone]"]');
                let phoneVal = phoneInput.val().trim();
                let errorSpan = $(this).find('.error-message').first();

                if (phoneVal === '') {
                    errorSpan.text('Phone is required.');
                    isValid = false;
                } else if (!/^\d{10}$/.test(phoneVal)) {
                    errorSpan.text('Phone must be 10 digits.');
                    isValid = false;
                }

                // Check if phone type is selected
                let phoneTypeSelected = $(this).find('input[name^="phones"][name$="[phone_type]"]:checked')
                    .length > 0;
                if (!phoneTypeSelected) {
                    $(this).find('.error-message').last().text('Please select phone type.');
                    isValid = false;
                }
            });

            if (!isValid) {
                show_toastr('error', 'Please fix phone errors before submitting.');
                return;
            }

            let form = $(this)[0];
            let formData = new FormData(form);

            $.ajax({
                url: $(form).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                success: function(res) {
                    if (res.error) {
                        show_toastr('error', res.message);
                    }

                    if (res.success) {
                        show_toastr('success', res.success || 'Customer Updated successfully');
                        setTimeout(function() {
                            window.location.href = res.redirect_url ||
                                "{{ route('customers.index') }}";
                        }, 1500);
                    }


                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, messages) {
                            let message = messages[0];

                            if (key.startsWith('phones')) {
                                let formattedKey = key.replace(/\./g, '-');
                                let $errorSpan = $('#error-' + formattedKey);
                                if ($errorSpan.length) $errorSpan.text(message);
                                show_toastr('error', message);
                            } else if (key.startsWith("companies")) {
                                let parts = key.split(".");
                                let companyIndex = parts[1];
                                let fieldName = parts.slice(2).join("_");
                                let errorClass = ".error-companies_" + companyIndex + "_" +
                                    fieldName;
                                $(errorClass).text(message);
                                show_toastr("error", message);
                            } else {
                                if ($("#error-" + key).length) $("#error-" + key).text(message);
                                show_toastr("error", message);
                            }
                        });
                    } else {
                        show_toastr("error", "Something went wrong.");
                    }
                }
            });
        });
    </script> --}}
@endsection
