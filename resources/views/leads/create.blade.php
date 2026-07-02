<!-- Lead Modal  Start -->
@php
    $country_list = \App\Models\Country::isActive()->pluck('name', 'id');
@endphp

                {{ Form::open(['route' => 'leads.save', 'method' => 'post', 'id' => 'create-lead']) }}
                <div class="row">
                    <!-- --------- customer multiple phone --------- -->
                    <div class="col-12">
                        <div id="phone-container">
                            <div class="row align-items-end phone-row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Phone<span class="text-danger">*</span></label>
                                    <input type="tel" name="phones[phone][0]" class="form-control form-control-sm"
                                        placeholder="Enter Phone">
                                    <span class="text-danger error-message"></span>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label d-block">Phone Type</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="phones[phone_type][0]"
                                            value="primary">
                                        <label class="form-check-label">Primary</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="phones[phone_type][0]"
                                            value="secondary">
                                        <label class="form-check-label">Secondary</label>
                                    </div>
                                    <span class="text-danger" id="error-phone_type"></span>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label d-block">WhatsApp</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="phones[is_whatsapp][0]"
                                            value="1">
                                        <label class="form-check-label">Yes</label>
                                    </div>
                                </div>

                                <div class="col-md-2 d-flex justify-content-end add_div">
                                    <button type="button" id="add_more_phone{{ time() }}" class="btn btn-primary btn-sm ">Add
                                        Phone</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="divider">
                    <!-- --------- customer first section --------- -->
                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            {{ Form::text('name', null, [
                                'class' => 'form-control form-control-sm',
                                'id' => 'name',
                                'placeholder' => __('Enter name.'),
                                'required' => 'required',
                            ]) }}
                        </div>
                        <input type="hidden" value="0" class="form-control form-control-sm customer_id"
                            name="customer_id">
                    </div>
                    <!-- --------- customer second section --------- -->
                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email </label>
                            {{ Form::email('email', null, [
                                'class' => 'form-control form-control-sm',
                                'id' => 'email',
                                'placeholder' => __('Enter Email'),

                            ]) }}
                        </div>
                    </div>
                    <!-- --------- end customer second section --------- -->

                    <hr class="divider">
                    <div class="col-md-4">
                        @php
                            $lead_type = \App\Models\LeadType::where('created_by', '=', \Auth::user()->creatorId())
                                ->get()
                                ->pluck('name', 'id');
                        @endphp
                        {{ Form::label('lead_type_id', __('Lead Type'), ['class' => 'form-label']) }}
                        {{ Form::select('lead_type_id', ['' => 'Select Lead Type'] + $lead_type->toArray(), null, ['class' => 'mb-3 form-select form-select-sm ']) }}
                        {{-- \Auth::user()->type != 'company' ? 'required' : '' --}}
                    </div>
                    <div class="col-md-4">
                        <div class="">
                            @php
                                $leadSources = \App\Models\LeadSource::where(
                                    'created_by',
                                    '=',
                                    \Auth::user()->creatorId(),
                                )
                                    ->get()
                                    ->pluck('name', 'id');
                            @endphp

                            <label for="lead_source" class="form-label">Lead Source <span
                                    class="text-danger">*</span></label>
                            {{ Form::select('lead_source', ['' => 'Select Source'] + $leadSources->toArray(), null, [
                                'class' => 'form-select form-select-sm  mb-2',
                                'id' => 'lead_source',
                                'aria-label' => 'Select Lead Source',
                                'required',
                            ]) }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="row">
                            <div class="">
                                @php
                                    $stage = \App\Models\LeadStage::where('created_by', '=', \Auth::user()->creatorId())
                                        ->get()
                                        ->pluck('name', 'id');
                                @endphp
                                {{ Form::label('stage_id', __('Stage'), ['class' => 'form-label']) }}<span
                                    class="text-danger">*</span>
                                {{ Form::select('stage_id', ['' => 'Select Stage'] + $stage->toArray(), null, ['class' => 'mb-3 form-select form-select-sm ', \Auth::user()->type != 'company' ? 'required' : '']) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="description" class="form-label">Description </label>
                        {{ Form::textarea('description', null, ['class' => 'form-control form-control-sm', 'id' => 'description', 'placeholder' => __('Enter description.'),  'rows' => '2']) }}
                    </div>



                    <!-- --------- end customer multiple phone --------- -->


                    <!-- address ---------- -->
                    <div class="address-block">
                        <input type="hidden" class="form-control form-control-sm" name="billing_address_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Country</label>
                                <select name="billing_country" class="form-control form-control-sm  country">
                                    <option value="">Select Country</option>
                                    @foreach ($country_list as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger " id="error-billing_country"></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>State</label>
                                <select name="billing_state" class="form-control form-control-sm state">
                                    <option value="">Select State</option>
                                </select>
                                <span class="text-danger " id="error-billing_state"></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>City</label>
                                <select name="billing_city" class="form-control form-control-sm  city">
                                    <option value="">Select City</option>
                                </select>
                                <span class="text-danger " id="error-billing_city"></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Zip Code</label>
                                <input type="text" name="billing_zipcode" class="form-control form-control-sm "
                                    placeholder="Enter Zipcode">
                                <span class="text-danger " id="error-billing_zipcode"></span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Address Line 1</label>
                                <textarea name="billing_address_line_1" class="form-control form-control-sm" rows="2"></textarea>
                                <span class="text-danger " id="error-billing_address_line_1"></span>
                            </div>
                            <div class="col-md-6">
                                <label>Address Line 2</label>
                                <textarea name="billing_address_line_2" class="form-control form-control-sm" rows="2"></textarea>
                                <span class="text-danger"></span>
                            </div>
                        </div>
                    </div>
                    <hr class="mb-2 mt-3">

                    <!-- ------------ end address ------------ -->

                    <div class="mt-4">
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-sm btn-success" id="addNewLead">Add
                                Lead</button>
                        </div>
                    </div>

                </div>

                {{ Form::close() }}


<script>
    $(document).ready(function() {

        $('#createLeadmodal').on('show.bs.modal', function() {
            var form = $('#create-lead');
            form[0].reset();

            $('#name').prop('readonly', false);
            $('#email').prop('readonly', false);
            $('#lead_source').val('').trigger('change');
        });


        $(document).on('blur', 'input[name="phones[phone][0]"]', function() {
            var phone = $(this).val();

            let submitBtn = $('#addNewLead');

            if (phone.length >= 6) {
                postAjax('{{ route('check.entity.phone') }}', {
                    phone: phone
                }, function(res)
                {
                    if(res.status == 'error')
                    {
                        show_toastr('error',res.message || 'Customer you can not added ');
                        submitBtn.prop('disabled', true);
                        return;
                    }

                    if (res.status === 'found') {
                        submitBtn.prop('disabled', false);
                        $('input[name="customer_id"]').val(res.customer_id);

                        $('#name').val(res.name).prop('readonly', true);
                        $('#email').val(res.email).prop('readonly', true);

                        if (res.lead_type_id && res.lead_type_id !== null && res
                            .lead_type_id !== '') {
                            $('#lead_type_id').val(res.lead_type_id).trigger('change');
                        } else {
                            $('#lead_type_id').val('').trigger('change');
                        }

                        // company dropdown
                        var companySelect = $('#company');
                        companySelect.empty().append(
                            '<option value="">Select Company</option>');
                        if (res.company_all && res.company_all.length > 0) {
                            $.each(res.company_all[0], function(id, name) {
                                companySelect.append('<option value="' + id + '">' +
                                    name + '</option>');
                            });
                        }

                        //  Fill first phone row type + whatsapp
                        if (res.customer_phone) {
                            var phoneData = res.customer_phone;
                            let firstRow = $("#phone-container .phone-row").eq(0);

                            firstRow.find('input[name^="phones[phone_type]"]').prop('checked',
                                false);
                            if (phoneData.is_primary == 1) {
                                firstRow.find('input[value="primary"]').prop('checked', true);
                            }
                            if (phoneData.is_secondary == 1) {
                                firstRow.find('input[value="secondary"]').prop('checked', true);
                            }

                            firstRow.find('input[name^="phones[is_whatsapp]"]').prop('checked',
                                phoneData.is_whatsapp == 1);
                        }

                        // address data fill
                        if (res.billing_address_data) {
                            let address = res.billing_address_data;

                            console.log(address);

                            $('input[name="billing_address_id"]').val(address.id);

                            $('.country').val(address.country).trigger('change');

                            setTimeout(function() {
                                $('.state').val(address.state).trigger('change');

                                setTimeout(function() {
                                    $('.city').val(address.city).trigger(
                                        'change');
                                }, 500);

                            }, 500);

                            $('input[name="billing_zipcode"]').val(address.zipcode);
                            $('textarea[name="billing_address_line_1"]').val(address
                                .address_line_1);
                            $('textarea[name="billing_address_line_2"]').val(address
                                .address_line_2);
                        }
                    } else {

                        submitBtn.prop('disabled', false);

                        // reset if not found
                        $('input[name="customer_id"]').val(0);
                        $('#name').prop('readonly', false);
                        $('#email').prop('readonly', false);
                        $('#lead_type_id').val('').trigger('change');

                        $('#company').empty().append(
                            '<option value="">Select Company</option>');

                        // let firstRow = $("#phone-container .phone-row").eq(0);
                        // firstRow.find('input[name^="phones[phone_type]"]').prop('checked',
                        //     false);
                        // firstRow.find('input[name^="phones[is_whatsapp]"]').prop('checked',
                        //     false);
                    }

                });
            }
        });

        // Reset name/email when phone cleared
        $(document).on('keyup', 'input[name="phones[phone][0]"]', function() {
            if ($(this).val().trim() === '') {
                $('#name').val('').prop('readonly', false);
                $('#email').val('').prop('readonly', false);
            }
        });

        $('#create-lead').on('submit', function(e) {
            e.preventDefault();

            let submitBtn = $('#addNewLead');
            if (submitBtn.prop('disabled')) {
                return false;
            }
            submitBtn.prop('disabled', true).text('Processing...');

            let hasError = false;

            $('.text-danger').text('');
            $('.error-message').text('');

            // Validate all phone numbers
            $('#phone-container .phone-row').each(function() {
                let phoneInput = $(this).find("input[name*='[phone]']");
                let phoneVal = phoneInput.val().trim();

                if (!/^\d{10}$/.test(phoneVal)) {
                    $(this).find(".error-message").text(
                        'Please enter a valid 10-digit phone number');
                    hasError = true;
                }
            });

            if (hasError)
            {
                submitBtn.prop('disabled', false).text('Add Lead');
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
                        submitBtn.prop('disabled', false).text('Add Lead');
                        show_toastr('error', res.message);
                    } else {
                        show_toastr('success', res.message || 'Lead added successfully');
                        localStorage.removeItem('lead_form_data');
                        setTimeout(function() {
                            window.location.href = res.redirect_url ||
                                "{{ route('leads.index') }}";
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                         submitBtn.prop('disabled', false).text('Add Lead');
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            if ($("#error-" + key).length) {
                                $("#error-" + key).text(value[0]);
                                show_toastr("error", value[0]);
                            } else {
                                show_toastr("error", value[0]);
                            }
                        });
                    } else {
                        console.log("Something went wrong.");
                    }
                }
            });
        });

        // Reindex phone rows
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

        // Add new phone row
        $(document).on("click", "#add_more_phone{{ time() }}", function() {
            let index = $("#phone-container .phone-row").length;
            let newRow = $(".phone-row:first").clone();

            let extra = $('<hr class="divider">');
            newRow.prepend(extra);

            newRow.find('.add_div').html('');
            newRow.find("input").each(function() {
                if ($(this).attr("type") === "radio") {
                    $(this).attr("name", "phones[phone_type][" + index + "]");
                    $(this).prop("checked", false);
                } else if ($(this).attr("type") === "checkbox") {
                    $(this).attr("name", "phones[is_whatsapp][" + index + "]");
                    $(this).prop("checked", false);
                } else {
                    $(this).val("");
                }
            });

            newRow.find(".error-message").text("");
            newRow.find('.add_div').html(
                '<button type="button" class="btn btn-danger remove-phone btn-sm"><i class="fa-solid fa-trash"></i></button>'
            );

            $("#phone-container").append(newRow);
            reindexRows();
        });

        // Remove phone row
        $(document).on("click", ".remove-phone", function() {
            if ($("#phone-container .phone-row").length > 1) {
                $(this).closest(".phone-row").remove();
                reindexRows();
            }
        });

        // Allow only one primary across rows
        $(document).on("change", "input[type=radio][value=primary]", function() {
            $("input[type=radio][value=primary]").not(this).prop("checked", false);
        });

        // Save lead form in localStorage (for company create popup etc.)


    });
</script>
<script>
    $(document).ready(function() {
        $(document).on('change', '.country', function() {
            var country_id = $(this).val();
            var stateDropdown = $(this).closest('.address-block').find('.state');
            var cityDropdown = $(this).closest('.address-block').find('.city');

            stateDropdown.html('<option value="">Loading...</option>');
            cityDropdown.html('<option value="">Select City</option>');

            if (country_id) {
                $.ajax({
                    url: "{{ route('get.states') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        country_id: country_id
                    },
                    success: function(res) {
                        var options = '<option value="">Select State</option>';
                        $.each(res.states, function(id, name) {
                            options += '<option value="' + id + '">' + name +
                                '</option>';
                        });
                        stateDropdown.html(options);
                    }
                });
            } else {
                stateDropdown.html('<option value="">Select State</option>');
                cityDropdown.html('<option value="">Select City</option>');
            }
        });

        $(document).on('change', '.state', function() {
            var state_id = $(this).val();
            var cityDropdown = $(this).closest('.address-block').find('.city');

            cityDropdown.html('<option value="">Loading...</option>');

            if (state_id) {
                $.ajax({
                    url: "{{ route('get.cities') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        state_id: state_id
                    },
                    success: function(res) {
                        var options = '<option value="">Select City</option>';
                        $.each(res.cities, function(id, name) {
                            options += '<option value="' + id + '">' + name +
                                '</option>';
                        });
                        cityDropdown.html(options);
                    }
                });
            } else {
                cityDropdown.html('<option value="">Select City</option>');
            }
        });
    });
</script>
