<!-- Lead Modal  Start -->
{{ Form::model($lead, ['route' => ['leads.update', $lead->id], 'method' => 'PUT', 'id' => 'update-lead']) }}
<style>
    .flatpickr-months .flatpickr-month {
        background: white;
    }
</style>
<div class="row">
     <div class="col-12">
        <div id="phone-container-edit">
            @if ($customer_phone_list->count() > 0)
                @foreach ($customer_phone_list as $index => $phone)
                    <div class="row align-items-end phone-row">
                        <input type="hidden" name="phone_id[{{ $index }}]" value="{{ $phone->id }}">

                        {{-- Phone --}}
                        <div class="col-md-4">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phones[phone][{{ $index }}]"
                                class="form-control form-control-sm" value="{{ $phone->phone }}"
                                placeholder="Enter Phone">
                            <span class="text-danger" id="error-phone_{{ $index }}"></span>
                        </div>

                        {{-- Phone Type --}}
                        <div class="col-md-4">
                            <label class="form-label d-block">Phone Type</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio"
                                    id="phone_type_primary{{ $index }}"
                                    name="phones[phone_type][{{ $index }}]" value="primary"
                                    @if ($phone->is_primary == 1) checked @endif>
                                <label class="form-check-label"
                                    for="phone_type_primary{{ $index }}">Primary</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio"
                                    id="phone_type_secondary{{ $index }}"
                                    name="phones[phone_type][{{ $index }}]" value="secondary"
                                    @if ($phone->is_secondary == 1) checked @endif>
                                <label class="form-check-label"
                                    for="phone_type_secondary{{ $index }}">Secondary</label>
                            </div>
                            <span class="text-danger" id="error-phone_type_{{ $index }}"></span>
                        </div>

                        {{-- WhatsApp --}}
                        <div class="col-md-2">
                            <label class="form-label d-block">WhatsApp</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                    name="phones[is_whatsapp][{{ $index }}]" value="1"
                                    @if ($phone->is_whatsapp) checked @endif>
                                <label class="form-check-label">Yes</label>
                            </div>
                            <span class="text-danger" id="error-whatsapp_{{ $index }}"></span>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="col-md-2 d-flex justify-content-end add_div">
                            @if ($loop->first)
                                <button type="button" class="btn btn-primary btn-sm add-more-phone{{ time() }}">Add Phone</button>
                            @else
                                <button type="button" class="btn btn-danger btn-sm remove-phone">
                                    <i class="fa fa-trash"></i>
                                </button>
                            @endif
                        </div>
                        <hr class="divider my-3">
                    </div>
                @endforeach
            @else
                {{-- One blank row if no phones --}}
                <div class="row align-items-end phone-row mb-3">
                    {{-- Phone --}}
                    <div class="col-md-4">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phones[phone][0]" class="form-control form-control-sm"
                            placeholder="Enter Phone">
                        <span class="text-danger" id="error-phone_0"></span>
                    </div>

                    {{-- Phone Type --}}
                    <div class="col-md-4">
                        <label class="form-label d-block">Phone Type</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="phones[phone_type][0]" value="primary">
                            <label class="form-check-label">Primary</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="phones[phone_type][0]"
                                value="secondary">
                            <label class="form-check-label">Secondary</label>
                        </div>
                        <span class="text-danger" id="error-phone_type_0"></span>
                    </div>

                    {{-- WhatsApp --}}
                    <div class="col-md-2">
                        <label class="form-label d-block">WhatsApp</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="phones[is_whatsapp][0]"
                                value="1">
                            <label class="form-check-label">Yes</label>
                        </div>
                        <span class="text-danger" id="error-whatsapp_0"></span>
                    </div>

                    {{-- Action --}}
                    <div class="col-md-2 d-flex justify-content-end add_div">
                        <button type="button" class="btn btn-primary btn-sm add-more-phone{{ time() }}">Add Phone</button>
                    </div>
                </div>
                <hr class="divider">
            @endif

        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
            {{ Form::text('name', $customer_name, ['class' => 'form-control form-control-sm', 'id' => 'name', 'placeholder' => __('Enter name.')]) }}
            <span class="invalid-feedback d-block" id="error-name"></span>
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="email" class="form-label">Email  </label>
            {{ Form::email('email', $customer_email, ['class' => 'form-control form-control-sm', 'id' => 'email', 'placeholder' => __('Enter Email')]) }}
            <span class="invalid-feedback d-block" id="error-email"></span>
        </div>
    </div>




    <div class="col-md-6 mt-1">
        {{ Form::label('sources', __('Sources'), ['class' => 'form-label']) }} <span class="text-danger">*</span>
        {{ Form::select('sources[]', $sources, null, ['class' => 'form-control choices-select', 'data-choices', 'data-choices-removeItem', 'multiple']) }}
        <span class="invalid-feedback d-block" id="error-sources"></span>
    </div>

    <div class="col-md-6  mt-1">
        @php
            $stage = \App\Models\LeadStage::where('created_by', '=', \Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');
        @endphp
        {{ Form::label('stage_id', __('Stage'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
        {{ Form::select('stage_id', $stage->toArray(), null, ['class' => 'form-control  choices-select', 'data-choices', 'data-choices-removeItem']) }}
        <span class="invalid-feedback d-block" id="error-stage_id"></span>
    </div>

    <hr class="divider">
    <div class="col-md-12 mt-1">
        @php
            $products = \App\Models\Products::InHouse()
                ->where('created_by', '=', \Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');
        @endphp

        <label for="products" class="form-label">Products </label>
        {{ Form::select('products[]', $products->toArray(), null, [
            'class' => 'form-select choices-select',
            'id' => 'products',
            'aria-label' => 'Select Product',
            'data-choices',
            'data-choices-removeItem',
            'multiple',
        ]) }}

        <span class="invalid-feedback d-block" id="error-products"></span>

    </div>
    <hr class="divider">
    <div class="col-md-6 mt-1">
        {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
        {{ Form::text('next_contact_date', null, ['class' => 'form-control datepicker-range flatpickr-input active', 'id' => 'datepicker-range', 'data-provider' => 'flatpickr']) }}
        <span class="invalid-feedback d-block" id="error-next_contact_date"></span>
    </div>

    <div class="col-md-6 mt-1">
        {{ Form::label('lead_type_id', __('Lead Type'), ['class' => 'form-label']) }}
        {{ Form::select('lead_type_id', $lead_type->toArray(), null, ['class' => 'form-control  choices-select', 'data-choices', 'data-choices-removeItem']) }}
        <span class="invalid-feedback d-block" id="error-lead_type_id"></span>
    </div>

     <div class="mt-1">
        <h5 class="">Billing Address</h5>
        <input type="hidden" class="form-control" name="bill_id" value="{{ $customer_address?->id ?? 0 }}">
        <div class="row">
            <div class="col-md-3">
                <label>Country </label>
                <select name="billing_country" class="form-control country">
                    <option value="">Select Country</option>
                    @foreach ($country_list as $id => $name)
                        <option value="{{ $id }}"
                            {{ ($customer_address?->get_country?->id ?? '') == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>State </label>
                <select name="billing_state" class="form-control state"
                    data-selected="{{ $customer_address?->state }}">
                    <option value="">Select State</option>
                    @if (!empty($customer_address?->state))
                        <option value="{{ $customer_address->state }}"
                            {{ ($customer_address?->get_state?->id ?? '') == $customer_address?->state ? 'selected' : '' }}>
                            {{ $customer_address?->get_state?->name }}</option>
                    @endif
                </select>
            </div>
            <div class="col-md-3">
                <label>City </label>
                <select name="billing_city" class="form-control city">
                    <option value="">Select City</option>
                    @if (!empty($customer_address?->city))
                        <option value="{{ $customer_address->city }}"
                            {{ ($customer_address?->get_city?->id ?? '') == $customer_address?->city ? 'selected' : '' }}>
                            {{ $customer_address?->get_city?->name }}</option>
                    @endif
                </select>
            </div>
            <div class="col-md-2">
                <label>Zipcode </label>
                <input type="text" name="billing_zipcode" class="form-control"
                    value="{{ $customer_address->zipcode ?? '' }}">
            </div>

        </div>
        <div class="row mt-2">
            <div class="col-md-6">
                <label>Address Line 1</label>
                <textarea name="billing_address_line_1" class="form-control" rows="2">{{ $customer_address?->address_line_1 ?? '' }}</textarea>
            </div>
            <div class="col-md-6">
                <label>Address Line 2</label>
                <textarea name="billing_address_line_2" class="form-control" rows="2">{{ $customer_address?->address_line_2 ?? '' }}</textarea>
            </div>
        </div>
    </div>

    <div class="col-lg-12 mt-1">
        <label for="notes" class="form-label">Description </label>
        {{ Form::textarea('notes', $lead->notes, ['class' => 'form-control', 'rows' => '3', 'id' => 'description', 'placeholder' => __('Enter description.')]) }}
        <span class="invalid-feedback d-block" id="error-notes"></span>
    </div>

    <div class="mt-4">
        <div class="hstack gap-2 justify-content-end">
            <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-sm btn-success" id="UpdateLeadBtn">Update
                Lead</button>
        </div>
    </div>
</div>
{{ Form::close() }}

<!-- Lead Modal  Ends -->

<script>
    // $(document).ready(function () {

    function reindexRows() {
        $("#phone-container-edit .phone-row").each(function(rowIndex) {
            $(this).find("input").each(function() {
                let name = $(this).attr("name");
                if (name) {
                    let newName = name.replace(/\[\d+\]/, "[" + rowIndex + "]");
                    $(this).attr("name", newName);
                }
            });
        });
    }



    $(document).on("click", ".add-more-phone{{ time() }}", function() {
        let lastRow = $("#phone-container-edit .phone-row:last"); // clone last row
        let newRow = lastRow.clone(false);


        newRow.find("input").each(function() {
            let type = $(this).attr("type");
            if (type === "radio" || type === "checkbox") {
                $(this).prop("checked", false);
            } else {
                $(this).val("");
            }
        });


        newRow.find("span.text-danger").text("");

        newRow.find(".add_div").html(
            '<button type="button" class="btn btn-danger btn-sm remove-phone"><i class="fa fa-trash"></i></button>'
        );

        $("#phone-container-edit").append(newRow);
        reindexRows();
    });


    $(document).on("click", ".remove-phone", function() {
        if ($("#phone-container-edit .phone-row").length > 1) {
            $(this).closest(".phone-row").remove();
            reindexRows();
        }
    });

    // ensure only one "primary" at a time
    $(document).on("change", "input[name*='[phone_type]']", function() {
        if ($(this).val() === 'primary') {
            $("input[name*='[phone_type]'][value='primary']").not(this).prop('checked', false);
        }
    });

    $('#update-lead').on('submit', function(e) {
        e.preventDefault();

        let submitBtn = $('#UpdateLeadBtn');
        if (submitBtn.prop('disabled')) {
            return false;
        }
        submitBtn.prop('disabled', true).text('Processing...');

        let hasError = false;

        // Reset error messages
        $('.text-danger').text('');
        $('.error-message').text('');

        // Validate phone numbers
        $('#phone-container-edit .phone-row').each(function() {
            let phoneInput = $(this).find("input[name*='[phone]']");
            let phoneVal = phoneInput.val().trim();

            if (!/^\d{10}$/.test(phoneVal)) {
                $(this).find(".error-message").text('Please enter a valid 10-digit phone number');
                hasError = true;
            }
        });

        if (hasError)
        {
            submitBtn.prop('disabled', false).text('Update Lead');
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
                if (res.success == 'error') {
                    submitBtn.prop('disabled', false).text('Update Lead');
                    show_toastr('error', res.message);
                } else {
                    show_toastr('success', res.message || 'Lead updated successfully');
                    localStorage.removeItem('lead_form_data');
                    setTimeout(function() {
                        window.location.href = res.redirect_url ||
                            "{{ route('leads.list') }}";
                    }, 1500);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                     submitBtn.prop('disabled', false).text('Update Lead');
                    let errors = xhr.responseJSON.errors;

                    $.each(errors, function(key, value) {
                        // e.g. key = "phones.phone_type.0"
                        let match = key.match(/^phones\.phone_type\.(\d+)$/);
                        if (match) {
                            // map to the right row
                            let index = match[1];
                            $("#error-phone_type_" + index).text(value[0]);
                        }

                        // generic case for other fields
                        let cleanKey = key.replace(/\.\d+$/, "");
                        if ($("#error-" + cleanKey).length) {
                            $("#error-" + cleanKey).text(value[0]);
                        }

                        show_toastr("error", value[0]);
                    });
                }
            }
        });
    });


    $(document).on('change', "input[name*='[phone_type]']", function() {
        if ($(this).val() === 'primary') {
            $("input[name*='[phone_type]'][value='primary']").not(this).prop('checked', false);
        }
    });
</script>

<script>
     $(document).ready(function() {

        let countrySelect = $('.country');
        let stateSelect = $('.state');
        let citySelect = $('.city');

        let selectedState = stateSelect.data('selected');
        let selectedCity = citySelect.data('selected');

        // ===== LOAD ON EDIT =====
        if (countrySelect.val()) {
            loadStates(countrySelect.val(), selectedState);
        }

        // ===== COUNTRY CHANGE =====
        countrySelect.on('change', function() {
            let countryId = $(this).val();
            stateSelect.html('<option value="">Select State</option>');
            citySelect.html('<option value="">Select City</option>');

            if (countryId) {
                loadStates(countryId, null);
            }
        });

        // ===== STATE CHANGE =====
        stateSelect.on('change', function() {
            let stateId = $(this).val();
            citySelect.html('<option value="">Select City</option>');

            if (stateId) {
                loadCities(stateId, null);
            }
        });

        function loadStates(countryId, selectedStateId) {
            $.ajax({
                url: "{{ route('get.states') }}",
                type: "POST",
                data: {
                    country_id: countryId,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    stateSelect.html('<option value="">Select State</option>');

                    $.each(res.states, function(id, name) {
                        stateSelect.append(
                            `<option value="${id}" ${id == selectedStateId ? 'selected' : ''}>${name}</option>`
                        );
                    });

                    if (selectedStateId && selectedCity) {
                        loadCities(selectedStateId, selectedCity);
                    }
                }
            });
        }

        function loadCities(stateId, selectedCityId) {
            $.ajax({
                url: "{{ route('get.cities') }}",
                type: "POST",
                data: {
                    state_id: stateId,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    citySelect.html('<option value="">Select City</option>');

                    $.each(res.cities, function(id, name) {
                        citySelect.append(
                            `<option value="${id}" ${id == selectedCityId ? 'selected' : ''}>${name}</option>`
                        );
                    });
                }
            });
        }

    });
</script>
