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
        .big-checkbox
        {
            transform: scale(1.6);
            margin-top: 4px;
            cursor: pointer;
        }

        .quote-customer-offcanvas {
            width: min(96vw, 1120px) !important;
            max-width: 1120px !important;
        }

        .quote-customer-offcanvas .offcanvas-header {
            border-bottom: 1px solid #e9ecef;
        }

        .quote-customer-offcanvas .offcanvas-body {
            padding: 1rem;
        }

        .customer-form-shell {
            max-width: 100%;
        }

        .customer-panel-card {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 0.9rem;
            background: #fff;
            height: 100%;
        }

        .address-block {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 0.85rem;
            background: #fafbfc;
        }

        .customer-submit-wrap {
            position: sticky;
            bottom: 0;
            background: #fff;
            border-top: 1px solid #e9ecef;
            padding-top: 0.85rem;
            margin-top: 0.75rem;
            z-index: 2;
        }

        @media (max-width: 991.98px) {
            .quote-customer-offcanvas {
                width: 100vw !important;
                max-width: 100vw !important;
            }

            .big-checkbox {
                transform: scale(1.25);
                margin-top: 2px;
            }
        }
</style>
<div class="col-md-12">
@php
     $country_list = \App\Models\Country::isActive()->pluck('name', 'id');
@endphp
     <div class="offcanvas offcanvas-end fade quote-customer-offcanvas" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel"
         aria-modal="true" role="dialog">

         <div class="offcanvas-header bg-light">
             <h5 class="offcanvas-title" id="offcanvasExampleLabel">Add Customer</h5>
             <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                 aria-label="Close"></button>
         </div>


        <div class="offcanvas-body">
            <div class="customer-form-shell">
                <form  action="{{ route('quotes.customer_store') }}" method="POST" id="quoteCustomerCreate"
                                        method="post" >
                                        @csrf

                                        <div class="step-arrow-nav mb-4">

                                            <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link  active" id="steparrow-gen-info-tab"
                                                        data-bs-toggle="pill" data-bs-target="#steparrow-gen-info" type="button"
                                                        role="tab" aria-controls="steparrow-gen-info" aria-selected="true"
                                                        data-position="0" tabindex="-1">Customer Detail</button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link " id="steparrow-description-info-tab"
                                                        data-bs-toggle="pill" data-bs-target="#steparrow-description-info"
                                                        type="button" role="tab" aria-controls="steparrow-description-info"
                                                        aria-selected="false" data-position="1">Other Detail</button>
                                                </li>

                                            </ul>
                                        </div>


                                        <div class="tab-content">

                                            <!-- ------------------------------------ company detail --------------------- -->
                                            <div class="tab-pane show active" id="steparrow-gen-info" role="tabpanel"
                                                aria-labelledby="steparrow-gen-info-tab">

                                                <div class="row mt-3 g-3">
                                                      <div class="col-12 col-lg-6">
                                                <div class="customer-panel-card">


                                                <div class="row mt-1 g-3">
                                                    <div class="col-12 col-md-6">
                                                        <label class="form-label" for="name">Name<span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" name="name" class="form-control"
                                                            id="name" placeholder="Enter name"
                                                            value="{{ old('name') }}">
                                                        <span class="text-danger" id="error-name"></span>
                                                    </div>

                                                    <div class="col-12 col-md-6">
                                                        <label class="form-label" for="email">Email </label>
                                                        <input type="email" name="email" class="form-control"
                                                            id="email" placeholder="Enter Email"
                                                            value="{{ old('email') }}">
                                                        <span class="text-danger" id="error-email"></span>
                                                    </div>
                                                </div>

                                                <div class="row mt-3 g-3">
                                                    <div class="col-12 col-md-6">
                                                        <label>Company Name <span
                                                                class="text-danger">*</span></label></label>
                                                        <input type="text" name="company_name"
                                                            class="form-control" placeholder="Enter Company Name" id="company_name">
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <label>Company GST No</label>
                                                        <input type="text" name="gst_no"
                                                            class="form-control" placeholder="Enter Company GST No">
                                                    </div>


                                                </div>
                                                <div class="row mt-3 g-3">
                                                     <div class="col-12 col-md-6">
                                                        <label>Company Adhar No</label>
                                                        <input type="text" name="company_adhar_no"
                                                            class="form-control" placeholder="Enter Company Adhar No">
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <label>Company Udhyam No</label>
                                                        <input type="text" name="company_udhyam_no"
                                                            class="form-control" placeholder="Enter Company Udhyam No">
                                                    </div>
                                                </div>

                                                <div class="row mt-3">

                                                    {{-- <div class="col-md-6">
                                                        <label class="form-label" for="rate">Product Rate <span
                                                                class="text-danger">*</span></label>
                                                        <div class="form-group required">
                                                            <div class="d-flex flex-row-reverse justify-content-end">
                                                                @for ($i = 5; $i >= 1; $i--)
                                                                    <input class="star" type="radio"
                                                                        id="star-{{ $i }}" name="rate"
                                                                        value="{{ $i }}"
                                                                        {{ old('rate') == $i ? 'checked' : '' }}>
                                                                    <label for="star-{{ $i }}">&#9733;</label>
                                                                @endfor
                                                            </div>
                                                        </div>
                                                        <span class="text-danger" id="error-rate"></span>
                                                    </div> --}}

                                                    {{-- <div class="col-md-6">
                                                        <label for="" class="form-label">Status <span
                                                                class="text-danger">*</span></label>
                                                        <div>
                                                            <input class="form-check-input" class="form-check"
                                                                type="radio" name="is_active" id="active"
                                                                value="1" checked>
                                                            <label class="form-check-label" for="flexRadioDefault1">
                                                                Active
                                                            </label>
                                                            &nbsp;

                                                            <input class="form-check-input" class="form-check"
                                                                type="radio" name="is_active" value="0"
                                                                id="inactive">
                                                            <label class="form-check-label" for="flexRadioDefault2">
                                                                In Active
                                                            </label>

                                                        </div>
                                                    </div> --}}

                                                </div>

                                                {{-- <div class="row mt-3">
                                                    <div class="col-md-6">
                                                        <label for="description" class="form-label">Description <span
                                                                class="text-danger">*</span></label>
                                                        <textarea class="form-control" name="description" id="description" rows="5" cols="19"
                                                            placeholder="Enter Description">{{ old('description') }}</textarea>
                                                        <span class="text-danger" id="error-description"></span>
                                                    </div>
                                                </div> --}}



                                            </div>
                                            </div>

                                            <!-- ------------ second section ----------- -->
                                            <div class="col-12 col-lg-6">
                                                <div class="customer-panel-card">
                                                <!-- --------------- multiple phone ------------ -->
                                                <div class="row ">
                                                    <div class=" text-end">
                                                        <button type="button" id="add_more_phone"
                                                            class="btn btn-primary btn-sm">Add
                                                            Phone</button>
                                                    </div>
                                                </div>

                                                <div id="phone-container">
                                                    <div class="row align-items-end phone-row mb-3 g-2">
                                                        <div class="col-12 col-md-4">
                                                            <label class="form-label">Phone <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="tel" name="phones[0][phone]"
                                                                class="form-control" placeholder="Enter Phone">
                                                            <span class="text-danger error-message"></span>
                                                        </div>

                                                        <div class="col-12 col-md-4">
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
                                                            <span class="text-danger" id="error-phone_type"></span>
                                                        </div>

                                                        <div class="col-6 col-md-2">
                                                            <label class="form-label d-block">WhatsApp</label>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="phones[0][is_whatsapp]" value="1">
                                                                <label class="form-check-label">Yes</label>
                                                            </div>
                                                        </div>

                                                        <div class="col-6 col-md-2 d-grid">
                                                            <button type="button"
                                                                class="btn btn-danger remove-phone btn-sm">Remove</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- --------------- End multiple phone ------------ -->
                                            </div>
                                            </div>


                                                </div>

                                            </div>
                                            <!-- ------------------------------------- END -------------------------------- -->

                                            <!-- ------------------------------------ company Detail --------------------- -->
                                            <div class="tab-pane fade " id="steparrow-description-info" role="tabpanel"
                                                aria-labelledby="steparrow-description-info-tab">


                                                <div id="company-container">
                                                    <!-- Company Block Start -->
                                                    <div class="row mt-3 company-block">
                                                        <!-- Billing Address -->
                                                <div class="col-12 col-xl-6">
                                                    <h4 class="mt-4 mb-2 text-center">Billing Address</h4>
                                                    <div class="address-block">
                                                        <div class="row g-2">
                                                            <div class="col-12 col-sm-6 col-lg-3">
                                                                <label>Country</label>
                                                                <select name="companies[0][billing_country]"
                                                                    class="form-control country">
                                                                    <option value="">Select Country</option>
                                                                    @foreach ($country_list as $id => $name)
                                                                        <option value="{{ $id }}">
                                                                            {{ $name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <span class="text-danger error-billing_country"></span>
                                                            </div>
                                                            <div class="col-12 col-sm-6 col-lg-3">
                                                                <label>State</label>
                                                                <select name="companies[0][billing_state]"
                                                                    class="form-control state">
                                                                    <option value="">Select State</option>
                                                                </select>
                                                                <span class="text-danger error-billing_state"></span>
                                                            </div>
                                                            <div class="col-12 col-sm-6 col-lg-3">
                                                                <label>City</label>
                                                                <select name="companies[0][billing_city]"
                                                                    class="form-control city">
                                                                    <option value="">Select City</option>
                                                                </select>
                                                                <span class="text-danger error-billing_city"></span>
                                                            </div>
                                                            <div class="col-12 col-sm-6 col-lg-2">
                                                                <label>Zip Code</label>
                                                                <input type="text" name="companies[0][billing_zipcode]"
                                                                    class="form-control" placeholder="Enter Zipcode">
                                                                <span class="text-danger error-billing_zipcode"></span>
                                                            </div>
                                                            <div class="col-12 col-lg-1">
                                                                <label class="me-2">Same</label>
                                                                <input type="checkbox" name="companies[0][is_same_adr]"
                                                                    value="1"
                                                                    class="form-check-input big-checkbox same-address"
                                                                    data-target="shipping_block_0">
                                                            </div>
                                                        </div>
                                                        <div class="row mt-2 g-2">
                                                            <div class="col-12 col-md-6">
                                                                <label>Address Line 1</label>
                                                                <textarea name="companies[0][billing_address_line_1]" class="form-control" rows="3"></textarea>
                                                                <span
                                                                    class="text-danger error-billing_address_line_1"></span>
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <label>Address Line 2</label>
                                                                <textarea name="companies[0][billing_address_line_2]" class="form-control" rows="3"></textarea>
                                                                <span
                                                                    class="text-danger error-billing_address_line_2"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Shipping Address -->
                                                <div class="col-12 col-xl-6 shipping-block" id="shipping_block_0">
                                                    <h4 class="mt-4 mb-2 text-center">Shipping Address</h4>
                                                    <div class="address-block">
                                                        <div class="row g-2">
                                                            <div class="col-12 col-sm-6 col-lg-3">
                                                                <label>Country</label>
                                                                <select name="companies[0][shipping_country]"
                                                                    class="form-control country">
                                                                    <option value="">Select Country</option>
                                                                    @foreach ($country_list as $id => $name)
                                                                        <option value="{{ $id }}">
                                                                            {{ $name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <span class="text-danger error-shipping_country"></span>
                                                            </div>
                                                            <div class="col-12 col-sm-6 col-lg-3">
                                                                <label>State</label>
                                                                <select name="companies[0][shipping_state]"
                                                                    class="form-control state">
                                                                    <option value="">Select State</option>
                                                                </select>
                                                                <span class="text-danger error-shipping_state"></span>
                                                            </div>
                                                            <div class="col-12 col-sm-6 col-lg-3">
                                                                <label>City</label>
                                                                <select name="companies[0][shipping_city]"
                                                                    class="form-control city">
                                                                    <option value="">Select City</option>
                                                                </select>
                                                                <span class="text-danger error-shipping_city"></span>
                                                            </div>
                                                            <div class="col-12 col-sm-6 col-lg-3">
                                                                <label>Zip Code</label>
                                                                <input type="text"
                                                                    name="companies[0][shipping_zipcode]"
                                                                    class="form-control" placeholder="Enter Zipcode">
                                                                <span class="text-danger error-shipping_zipcode"></span>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-2 g-2">
                                                            <div class="col-12 col-md-6">
                                                                <label>Address Line 1</label>
                                                                <textarea name="companies[0][shipping_address_line_1]" class="form-control" rows="3"></textarea>
                                                                <span
                                                                    class="text-danger error-shipping_address_line_1"></span>
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <label>Address Line 2</label>
                                                                <textarea name="companies[0][shipping_address_line_2]" class="form-control" rows="3"></textarea>
                                                                <span
                                                                    class="text-danger error-shipping_address_line_2"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>



                                                    </div>
                                                    <!-- Company Block End -->
                                                </div>



                                            </div>
                                            <!--  ------------------------------------ END ---------------------  -->

                                            <div class="d-flex align-items-start mt-4 customer-submit-wrap">
                                                    <button type="submit"
                                                        class="btn btn-success right ms-auto px-4" id="quoteAddCustBtn">
                                                        Submit
                                                    </button>
                                                </div>


                                        </div>
                                        <!-- end tab content -->
                                    </form>

            </div>
        </div>

     </div>
</div>


 @section('page-script')
    <script>
        $(document).ready(function()
        {

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

            $('#quoteCustomerCreate').on('submit', function(e) {
                e.preventDefault();

                let submitBtn = $('#quoteAddCustBtn');
                if (submitBtn.prop('disabled')) {
                    return false;
                }
                submitBtn.prop('disabled', true).text('Processing...');

                let hasError = false;

                $('.text-danger').text('');
                $('.error-message').text('');

                // $('#phone-container .phone-row').each(function() {
                //     let phoneInput = $(this).find("input[name*='phone']");
                //     let phoneVal = phoneInput.val().trim();

                //     if (phoneVal === '' || phoneVal.length < 10 || !/^\d+$/.test(phoneVal)) {
                //         $(this).find(".error-message").text(
                //             'Please enter a valid 10-digit phone number');
                //         hasError = true;
                //     }
                // });

                $('#phone-container .phone-row').each(function() {
                    let phoneInput = $(this).find("input[name*='phone']");
                    let phoneVal = phoneInput.val().trim();

                    // Check if not exactly 10 digits
                    if (!/^\d{10}$/.test(phoneVal)) {
                        $(this).find(".error-message").text('Please enter a valid 10-digit phone number');
                        hasError = true;
                    } else {
                        $(this).find(".error-message").text('');
                    }
                });

                if (hasError)
                {
                    submitBtn.prop('disabled', false).text('Submit');
                    return false;
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
                        if(res.error)
                        {
                            submitBtn.prop('disabled', false).text('Submit');
                            show_toastr('error', res.message);
                        }
                        if(res.success)
                        {
                             show_toastr('success', res.success || 'Customer added successfully');
                            setTimeout(() => {
                                    window.location.href = res.redirect_url;
                                }, 1500);
                        }

                    },
                    error: function(xhr)
                    {
                        submitBtn.prop('disabled', false).text('Submit');
                        if (xhr.status === 422) {

                            let errors = xhr.responseJSON.errors;

                            $.each(errors, function(key, value) {
                                if ($("#error-" + key).length) {
                                    $("#error-" + key).text(value[0]);
                                    show_toastr("error", value[0]);
                                }

                                else if (key.startsWith("companies")) {
                                    let parts = key.split(".");
                                    let companyIndex = parts[1];
                                    let fieldName = parts[parts.length - 1];

                                    let companyBlock = $(".company-block").eq(companyIndex);
                                    companyBlock.find(".error-" + fieldName).text(value[0]);
                                    show_toastr("error", value[0]);
                                }
                                else
                                {
                                    show_toastr("error", value[0]);
                                }
                            });
                        } else {
                            show_toastr("error", "Something went wrong.");
                        }
                    }

                });
            });

        });
    </script>
    <script>
        $(document).ready(function()
        {
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

                newBlock.find("input, textarea, select").val("");
                 newBlock.find("input[type=checkbox], input[type=radio]").prop("checked", false);

                newBlock.find("input, textarea, select").each(function() {
                    let name = $(this).attr("name");
                    if (name) {
                        let newName = name.replace(/\[\d+\]/, "[" + companyIndex + "]");
                        $(this).attr("name", newName);
                    }
                });

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
