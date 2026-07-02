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

            <!-- PAGE TITLE -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4>Customer</h4>
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('customers.index') }}">Customer</a>
                            </li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Customer Add</h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('customers.store') }}" enctype="multipart/form-data" method="post"
                        id="customerForm">
                        @csrf

                        <!-- ================= CUSTOMER DETAIL ================= -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Customer Detail</h5>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6 border-end">
                                        <div class="row g-3">

                                            <div class="col-md-6">
                                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control form-control-sm"
                                                    value="{{ old('name') }}" id="name">
                                                <span class="text-danger" id="error-name"></span>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Email </label>
                                                <input type="email" name="email" class="form-control form-control-sm"
                                                    value="{{ old('email') }}">
                                                <span class="text-danger" id="error-email"></span>
                                            </div>

                                            <div class="col-md-6">
                                                <label>Company Name <span class="text-danger">*</span></label>
                                                <input type="text" name="company_name"
                                                    class="form-control form-control-sm" id="company_name">
                                            </div>

                                            <div class="col-md-6">
                                                <label>Company GST No</label>
                                                <input type="text" name="gst_no" class="form-control form-control-sm">
                                                <span class="text-danger" id="error-gst_no"></span>
                                            </div>

                                            <div class="col-md-6">
                                                <label>Company Adhar No</label>
                                                <input type="text" name="company_adhar_no"
                                                    class="form-control form-control-sm">
                                            </div>

                                            <div class="col-md-6">
                                                <label>Company Udhyam No</label>
                                                <input type="text" name="company_udhyam_no"
                                                    class="form-control form-control-sm">
                                            </div>

                                            <div class="col-md-12">
                                                <label class="form-label">Lead Type </label>
                                                <select name="lead_type_id" class="form-control form-control-sm">
                                                    <option value="">Select Lead Type</option>
                                                    @foreach ($lead_type_list as $id => $name)
                                                        <option value="{{ $id }}">{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="text-danger" id="error-lead_type_id"></span>
                                            </div>

                                        </div>
                                    </div>

                                    <!-- ================= PHONE DETAIL ================= -->
                                    <div class="col-lg-6">

                                        <div class="row py-3 border-bottom">
                                            <div class="text-end">
                                                <button type="button" id="add_more_phone" class="btn btn-sm btn-primary">
                                                    Add Phone
                                                </button>
                                            </div>
                                        </div>

                                        <div id="phone-container">
                                            <div class="row align-items-end phone-row border-bottom py-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Phone <span
                                                            class="text-danger">*</span></label>
                                                    <input type="tel" name="phones[0][phone]"
                                                        class="form-control form-control-sm">
                                                    <span class="text-danger error-message"></span>
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label d-block">Phone Type</label>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio"
                                                            name="phones[0][phone_type]" value="primary">
                                                        <label class="form-check-label">Primary</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio"
                                                            name="phones[0][phone_type]" value="secondary">
                                                        <label class="form-check-label">Secondary</label>
                                                    </div>
                                                </div>

                                                <div class="col-md-2">
                                                    <label class="form-label d-block">WhatsApp</label>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="phones[0][is_whatsapp]" value="1">
                                                        <label class="form-check-label">Yes</label>
                                                    </div>
                                                </div>

                                                <div class="col-md-2 d-flex">
                                                    <button type="button" class="btn btn-sm btn-danger remove-phone">
                                                        Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ================= ADDRESS DETAIL ================= -->
                        <div class="card mb-4 company-block">
                            <div class="card-header">
                                <h5 class="mb-0">Address Detail</h5>
                            </div>

                            <div class="card-body">
                                <div class="row">

                                    <!-- Billing Address -->
                                    <div class="col-md-6 border-end">
                                        <div class="d-flex justify-content-between">
                                            <h5 class="mb-3 text-center">Billing Address</h5>
                                            <div>
                                                <label class="me-2">Same</label>
                                                <input type="checkbox" name="companies[0][is_same_adr]" value="1"
                                                    class="form-check-input big-checkbox same-address"
                                                    data-target="shipping_block_0">
                                            </div>
                                        </div>

                                        <div class="address-block">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label>Country</label>
                                                    <select name="companies[0][billing_country]"
                                                        class="form-control form-control-sm  country">
                                                        <option value="">Select Country</option>
                                                        @foreach ($country_list as $id => $name)
                                                            <option value="{{ $id }}">
                                                                {{ $name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="text-danger error-billing_country"></span>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>State</label>
                                                    <select name="companies[0][billing_state]"
                                                        class="form-control form-control-sm  state">
                                                        <option value="">Select State</option>
                                                    </select>
                                                    <span class="text-danger error-billing_state"></span>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>City</label>
                                                    <select name="companies[0][billing_city]"
                                                        class="form-control form-control-sm  city">
                                                        <option value="">Select City</option>
                                                    </select>
                                                    <span class="text-danger error-billing_city"></span>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Zip Code</label>
                                                    <input type="text" name="companies[0][billing_zipcode]"
                                                        class="form-control form-control-sm " placeholder="Enter Zipcode">
                                                    <span class="text-danger error-billing_zipcode"></span>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Address Line 1</label>
                                                    <textarea name="companies[0][billing_address_line_1]" class="form-control form-control-sm " rows="5"></textarea>
                                                    <span class="text-danger error-billing_address_line_1"></span>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Address Line 2</label>
                                                    <textarea name="companies[0][billing_address_line_2]" class="form-control form-control-sm " rows="5"></textarea>
                                                    <span class="text-danger error-billing_address_line_2"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Shipping Address -->
                                    <div class="col-md-6 shipping-block" id="shipping_block_0">
                                        <h5 class="mb-3 text-center">Shipping Address</h5>
                                        <div class="address-block">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label>Country</label>
                                                    <select name="companies[0][shipping_country]"
                                                        class="form-control form-control-sm  country">
                                                        <option value="">Select Country</option>
                                                        @foreach ($country_list as $id => $name)
                                                            <option value="{{ $id }}">
                                                                {{ $name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="text-danger error-shipping_country"></span>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>State</label>
                                                    <select name="companies[0][shipping_state]"
                                                        class="form-control form-control-sm  state">
                                                        <option value="">Select State</option>
                                                    </select>
                                                    <span class="text-danger error-shipping_state"></span>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>City</label>
                                                    <select name="companies[0][shipping_city]"
                                                        class="form-control form-control-sm  city">
                                                        <option value="">Select City</option>
                                                    </select>
                                                    <span class="text-danger error-shipping_city"></span>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Zip Code</label>
                                                    <input type="text" name="companies[0][shipping_zipcode]"
                                                        class="form-control form-control-sm " placeholder="Enter Zipcode">
                                                    <span class="text-danger error-shipping_zipcode"></span>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Address Line 1</label>
                                                    <textarea name="companies[0][shipping_address_line_1]" class="form-control form-control-sm " rows="5"></textarea>
                                                    <span class="text-danger error-shipping_address_line_1"></span>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Address Line 2</label>
                                                    <textarea name="companies[0][shipping_address_line_2]" class="form-control form-control-sm " rows="5"></textarea>
                                                    <span class="text-danger error-shipping_address_line_2"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- ================= SUBMIT ================= -->
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-sm btn-success px-4" id="submitBtn">
                                Submit
                            </button>
                        </div>

                    </form>



                </div>
            </div>

        </div>
    </div>
@endsection


@section('page-script')
    <script>
        $(document).ready(function() {

            function reindexRows() {
                $("#phone-container .phone-row").each(function(rowIndex) {
                    $(this).find("input, select, textarea").each(function() {
                        let name = $(this).attr("name");
                        if (name) {
                            let updated = name.replace(/\[\d+\]/, "[" + rowIndex + "]");
                            $(this).attr("name", updated);
                        }
                    });
                });
            }

            $("#add_more_phone").on("click", function() {
                let index = $("#phone-container .phone-row").length;
                let newRow = $(".phone-row:first").clone();

                newRow.find("input[type='tel']").val("");
                newRow.find("input[type='radio']").prop("checked", false);
                newRow.find("input[type='checkbox']").prop("checked", false);
                newRow.find(".error-message").text("");

                newRow.find("input, select, textarea").each(function() {
                    let name = $(this).attr("name");
                    if (name) {
                        let updated = name.replace(/\[\d+\]/, "[" + index + "]");
                        $(this).attr("name", updated);
                    }
                });

                $("#phone-container").append(newRow);
            });

            $(document).on("click", ".remove-phone", function() {
                if ($("#phone-container .phone-row").length > 1) {
                    $(this).closest(".phone-row").remove();
                    reindexRows();
                }
            });

            $(document).on("change", "input[type=radio][value=primary]", function() {
                $("input[type=radio][value=primary]").not(this).prop("checked", false);
            });

            $('#customerForm').on('submit', function(e) {
                e.preventDefault();

                let submitBtn = $('#submitBtn');
                submitBtn.prop('disabled', true).text('Submitting...');

                let hasError = false;

                // Clear old errors
                $('.text-danger').text('');
                $('.error-message').text('');

                /* ================= BASIC FIELD VALIDATION ================= */
                let name = $('input[name="name"]').val().trim();
                let email = $('input[name="email"]').val().trim();
                let leadType = $('select[name="lead_type_id"]').val();

                if (name === '') {
                    $('#error-name').text('Name is required');
                    hasError = true;
                }

                // if (email === '') {
                //     $('#error-email').text('Email is required');
                //     hasError = true;
                // } else if (!/^\S+@\S+\.\S+$/.test(email)) {
                //     $('#error-email').text('Enter valid email');
                //     hasError = true;
                // }

                // if (leadType === '') {
                //     $('#error-lead_type_id').text('Please select lead type');
                //     hasError = true;
                // }

                /* ================= PHONE VALIDATION ================= */
                $('#phone-container .phone-row').each(function() {
                    let phoneInput = $(this).find("input[name*='phone']");
                    let phoneVal = phoneInput.val().trim();

                    if (phoneVal === '' || phoneVal.length !== 10 || !/^\d+$/.test(phoneVal)) {
                        $(this).find('.error-message').text(
                            'Please enter a valid 10-digit phone number');
                        hasError = true;
                    }
                });

                /* ================= ADDRESS VALIDATION ================= */
                $('.company-block').each(function(index) {
                    let wrapper = $(this);

                    // Billing
                    let billingCountry = wrapper.find('select[name*="[billing_country]"]').val();
                    let billingState = wrapper.find('select[name*="[billing_state]"]').val();
                    let billingCity = wrapper.find('select[name*="[billing_city]"]').val();
                    let billingZip = wrapper.find('input[name*="[billing_zipcode]"]').val().trim();
                    let billingAddress1 = wrapper.find('textarea[name*="[billing_address_line_1]"]')
                        .val().trim();

                    // if (!billingCountry) {
                    //     wrapper.find('.error-billing_country').text('Select billing country');
                    //     hasError = true;
                    // }
                    // if (!billingState) {
                    //     wrapper.find('.error-billing_state').text('Select billing state');
                    //     hasError = true;
                    // }
                    // if (!billingCity) {
                    //     wrapper.find('.error-billing_city').text('Select billing city');
                    //     hasError = true;
                    // }
                    // if (!billingZip) {
                    //     wrapper.find('.error-billing_zipcode').text('Enter billing zip code');
                    //     hasError = true;
                    // }
                    // if (!billingAddress1) {
                    //     wrapper.find('.error-billing_address_line_1').text(
                    //         'Enter billing address line 1');
                    //     hasError = true;
                    // }

                    // Shipping (only if visible)
                    let shippingBlock = wrapper.find('.shipping-block');
                    if (shippingBlock.is(':visible')) {
                        let shippingCountry = shippingBlock.find(
                            'select[name*="[shipping_country]"]').val();
                        let shippingState = shippingBlock.find('select[name*="[shipping_state]"]')
                            .val();
                        let shippingCity = shippingBlock.find('select[name*="[shipping_city]"]')
                            .val();
                        let shippingZip = shippingBlock.find('input[name*="[shipping_zipcode]"]')
                            .val().trim();
                        // let shippingAddress1 = shippingBlock.find(
                        //     'textarea[name*="[shipping_address_line_1]"]').val().trim();

                        // if (!shippingCountry) {
                        //     shippingBlock.find('.error-shipping_country').text(
                        //         'Select shipping country');
                        //     hasError = true;
                        // }
                        // if (!shippingState) {
                        //     shippingBlock.find('.error-shipping_state').text(
                        //         'Select shipping state');
                        //     hasError = true;
                        // }
                        // if (!shippingCity) {
                        //     shippingBlock.find('.error-shipping_city').text('Select shipping city');
                        //     hasError = true;
                        // }
                        // if (!shippingZip) {
                        //     shippingBlock.find('.error-shipping_zipcode').text(
                        //         'Enter shipping zip code');
                        //     hasError = true;
                        // }
                        // if (!shippingAddress1) {
                        //     shippingBlock.find('.error-shipping_address_line_1').text(
                        //         'Enter shipping address line 1');
                        //     hasError = true;
                        // }
                    }
                });

                if (hasError) {
                    submitBtn.prop('disabled', false).text('Submit');
                    return false;
                }

                /* ================= AJAX SUBMIT ================= */
                let formData = new FormData(this);

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    success: function(res) {
                        if (res.error === 'yes') {
                            show_toastr('error', res.message || 'Something went wrong');
                            submitBtn.prop('disabled', false).text('Submit');
                            return;
                        }

                        if (res.success === 'yes') {
                            show_toastr('success', res.message ||
                                'Customer added successfully');
                            setTimeout(function() {
                                window.location.href = res.redirect_url ||
                                    "{{ route('customers.index') }}";
                            }, 1500);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;

                            $.each(errors, function(key, value) {
                                if ($('#error-' + key).length) {
                                    $('#error-' + key).text(value[0]);
                                } else if (key.startsWith('companies')) {
                                    let parts = key.split('.');
                                    let companyIndex = parts[1];
                                    let fieldName = parts[parts.length - 1];

                                    $('.company-block')
                                        .eq(companyIndex)
                                        .find('.error-' + fieldName)
                                        .text(value[0]);
                                }
                            });

                            show_toastr('error', 'Please fix validation errors');
                        } else {
                            show_toastr('error', 'Something went wrong.');
                        }

                        submitBtn.prop('disabled', false).text('Submit');
                    }
                });

            });




        });
    </script>
    <script>
        $(document).ready(function() {
            $(document).on('change', '.country', function() {
                let countryID = $(this).val();
                let wrapper = $(this).closest('.address-block');
                let stateDropdown = wrapper.find('.state');
                let cityDropdown = wrapper.find('.city');

                stateDropdown.html('<option value="">Select State</option>');
                cityDropdown.html('<option value="">Select City</option>');

                if (countryID) {
                    $.ajax({
                        url: "{{ route('get.states') }}",
                        type: "POST",
                        data: {
                            country_id: countryID,
                            _token: '{{ csrf_token() }}'
                        },
                        beforeSend: function() {
                            stateDropdown.html('<option value="">Loading...</option>');
                        },
                        success: function(res) {
                            stateDropdown.html('<option value="">Select State</option>');
                            $.each(res.states, function(id, name) {
                                stateDropdown.append('<option value="' + id + '">' +
                                    name + '</option>');
                            });
                        },
                        error: function() {
                            stateDropdown.html(
                                '<option value="">Error loading states</option>');
                        }
                    });
                }
            });

            $(document).on('change', '.state', function() {
                let stateID = $(this).val();
                let wrapper = $(this).closest('.address-block');
                let cityDropdown = wrapper.find('.city');

                cityDropdown.html('<option value="">Select City</option>');

                if (stateID) {
                    $.ajax({
                        url: "{{ route('get.cities') }}",
                        type: "POST",
                        data: {
                            state_id: stateID,
                            _token: '{{ csrf_token() }}'
                        },
                        beforeSend: function() {
                            cityDropdown.html('<option value="">Loading...</option>');
                        },
                        success: function(res) {
                            cityDropdown.html('<option value="">Select City</option>');
                            $.each(res.cities, function(id, name) {
                                cityDropdown.append('<option value="' + id + '">' +
                                    name + '</option>');
                            });
                        },
                        error: function() {
                            cityDropdown.html('<option value="">Error loading cities</option>');
                        }
                    });
                }
            });

        });
    </script>

    <script>
        $(document).ready(function() {
            let companyIndex = 1;

            $(document).on("click", ".add_company", function(e) {
                e.preventDefault();

                let newBlock = $(".company-block:first").clone();

                // Reset values
                newBlock.find("input, textarea, select").val("");
                newBlock.find("input[type=checkbox], input[type=radio]").prop("checked", false);

                // Update all names with new index
                newBlock.find("input, textarea, select").each(function() {
                    let name = $(this).attr("name");
                    if (name) {
                        let newName = name.replace(/\[\d+\]/, "[" + companyIndex + "]");
                        $(this).attr("name", newName);
                    }
                });

                // Update shipping block ID and checkbox target
                newBlock.find(".shipping-block").attr("id", "shipping_block_" + companyIndex);
                newBlock.find(".same-address").attr("data-target", "shipping_block_" + companyIndex);

                $("#company-container").append(newBlock);
                companyIndex++;
            });


            $(document).on("click", ".remove-company", function() {
                if ($(".company-block").length > 1) {
                    $(this).closest(".company-block").remove();
                } else {
                    show_toastr('error', 'At least one company is required.');
                }
            });

            $(document).on("change", ".same-address", function() {
                let targetId = $(this).data("target");
                if ($(this).is(":checked")) {
                    $("#" + targetId).hide();
                } else {
                    $("#" + targetId).show();
                }
            });

        });
    </script>
    <script>
        $(document).ready(function () {
            $('#name').on('input', function () {
                $('#company_name').val($(this).val());
            });
        });
    </script>
@endsection
