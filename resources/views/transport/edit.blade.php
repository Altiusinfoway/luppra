@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Transport </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('transports.index') }}">Transport </a>
                                </li>
                                <li class="breadcrumb-item active">Edit</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end page title -->
            <div class="row justify-content-center">
                <!-- Varying Modal Content -->
                <div class="col-xl-12 col-xxl-10">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title  mb-0">Transport Edit</h5>
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('transports.update', $transport['id']) }}" method="POST" id="main-form">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-lg-8 border-end">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="name">name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" name="name"
                                                        class="form-control form-control-sm " id="name"
                                                        placeholder="Enter name"
                                                        value="{{ old('name', $transport['name'] ?? '') }}">
                                                    <span class="text-danger" id="error-name"></span>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="email">Email <span
                                                            class="text-danger">*</span></label>
                                                    <input type="email" name="email"
                                                        class="form-control form-control-sm " id="email"
                                                        placeholder="Enter Email"
                                                        value="{{ old('email', $transport['email'] ?? '') }}">

                                                    <span class="text-danger" id="error-email"></span>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="company_name">Company Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" name="company_name"
                                                        class="form-control form-control-sm " id="company_name"
                                                        placeholder="Enter Company Name"
                                                        value="{{ old('company_name', $transport['company_name'] ?? '') }}">

                                                    <span class="text-danger" id="error-company_name"></span>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="gst_no">GST No </label>
                                                    <input type="text" name="gst_no"
                                                        class="form-control form-control-sm " id="manufacturer-name-input"
                                                        placeholder="Enter GST No."
                                                        value="{{ old('gst_no', $transport['gst_no'] ?? '') }}">
                                                    <span class="text-danger" id="error-gst_no"></span>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <label for="specification" class="form-label">Specification <span
                                                        class="text-danger">*</span></label>
                                                <textarea class="form-control form-control-sm " name="specification" id="specification" rows="5"
                                                    placeholder="Enter Specification">{{ old('specification', $transport['specification'] ?? '') }}</textarea>
                                                <span class="text-danger" id="error-specification"></span>
                                            </div>



                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="row">
                                            <div class="col-md-12">

                                                <label class="form-label mb-2">Contact Numbers<span
                                                        class="text-danger">*</span></label>

                                                <div id="contact-wrapper">
                                                    {{-- @php $oldContacts = old('contact', ['']); @endphp --}}
                                                    @php
                                                        $oldContacts = old('contact', $contact_array ?? ['']);
                                                    @endphp

                                                    @foreach ($oldContacts as $index => $contact)
                                                        <div class="row mb-2 contact-row">
                                                            <div class="col-md-8">
                                                                <input type="number" name="contact[]"
                                                                    class="form-control form-control-sm  contact-input"
                                                                    placeholder="Enter Contact" value="{{ $contact }}"
                                                                    required>
                                                                <span class="text-danger ajax-error-contact d-block"></span>
                                                            </div>
                                                            <div class="col-md-4 d-flex">
                                                                @if ($loop->first)
                                                                    <button type="button"
                                                                        class="btn btn-sm w-100 btn-primary ms-2"
                                                                        id="add-contact-btn">Add Contact</button>
                                                                @else
                                                                    <button type="button"
                                                                        class="btn btn-sm  w-100 btn-danger ms-2 remove-contact-btn">Remove</button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>

                                            </div>
                                            {{-- <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="" class="form-label">Status <span
                                                            class="text-danger">*</span></label>
                                                    <div>
                                                        <input class="form-check-input" class="form-check" type="radio"
                                                            name="is_active" id="active" value="1"
                                                            @if ($transport['is_active'] == 1) checked @endif>
                                                        <label class="form-check-label" for="flexRadioDefault1">
                                                            Active
                                                        </label>
                                                        &nbsp;
                                                        <input class="form-check-input" class="form-check" type="radio"
                                                            name="is_active" value="0" id="inactive"
                                                            @if ($transport['is_active'] == 0) checked @endif>
                                                        <label class="form-check-label" for="flexRadioDefault2">
                                                            In Active
                                                        </label>

                                                    </div>
                                                </div>
                                            </div> --}}

                                        </div>

                                    </div>

                                    <hr class="divider">

                                    <div class="col-md-12">
                                        <div class="d-flex justify-content-end mb-3">
                                            <button type="button" class="btn btn-sm  btn-sm btn-primary"
                                                id="add-address-btn">+
                                                Add Address</button>
                                        </div>

                                        <div id="address-container">
                                            @foreach ($transport->getEntityAddress as $index => $address_rcd)
                                                @include(
                                                    'address.multiple_address_list',
                                                    compact('index', 'address_rcd'))
                                            @endforeach
                                        </div>
                                    </div>

                                </div>
                                <div class="text-center mt-3">
                                    <button type="submit" class="btn btn-sm  btn-success w-sm" id="editTransportBtn">Submit</button>
                                </div>
                            </form>
                        </div>
                        <!-- end card body -->
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
        //add more contact
        document.addEventListener("DOMContentLoaded", function() {
            const contactWrapper = document.getElementById("contact-wrapper");
            const addBtn = document.getElementById("add-contact-btn");

            addBtn.addEventListener("click", function() {
                const newRow = document.createElement("div");
                newRow.classList.add("row", "mb-2", "contact-row");

                newRow.innerHTML = `
                <div class="col-md-8">
                    <input type="number" name="contact[]" class="form-control form-control-sm  contact-input" placeholder="Enter Contact">
                    <span class="text-danger ajax-error-contact d-block"></span>
                </div>
                <div class="col-md-4 d-flex ">
                    <button type="button" class="btn btn-sm w-100 btn-danger ms-2 remove-contact-btn">Remove</button>
                </div>
            `;

                contactWrapper.appendChild(newRow);
            });

            // Remove contact row
            contactWrapper.addEventListener("click", function(e) {
                if (e.target && e.target.classList.contains("remove-contact-btn")) {
                    e.target.closest(".contact-row").remove();
                }
            });
        });
    </script>


    <script>
        $(document).ready(function() {
            let addressIndex = {{ count($transport->getEntityAddress) }};

            $('#add-address-btn').on('click', function() {
                $.ajax({
                    url: '{{ route('transports.address.block') }}',
                    data: {
                        index: addressIndex
                    },
                    success: function(response) {
                        $('#address-container').append(response);

                        // Show remove buttons for all except first
                        $('.address-block-wrapper').each(function(i) {
                            if (i === 0) {
                                $(this).find('.remove-address-btn').hide();
                            } else {
                                $(this).find('.remove-address-btn').show();
                            }
                        });


                        addressIndex++;
                    }
                });
            });

            $(document).on('click', '.remove-address-btn', function() {
                $(this).closest('.address-block-wrapper').remove();
            });

            // Dynamic dependent dropdowns
            $(document).on('change', '.country-input', function() {
                let countryID = $(this).val();
                let $block = $(this).closest('.address-block-wrapper');
                let $state = $block.find('.state-input');
                let $city = $block.find('.city-input');

                $state.html('<option value="">Loading...</option>');
                $city.html('<option value="">Select City</option>');

                $.post("{{ route('get.states') }}", {
                    country_id: countryID,
                    _token: '{{ csrf_token() }}'
                }, function(res) {
                    $state.html('<option value="">Select State</option>');
                    $.each(res.states, function(id, name) {
                        $state.append('<option value="' + id + '">' + name + '</option>');
                    });
                });
            });

            $(document).on('change', '.state-input', function() {
                let stateID = $(this).val();
                let $block = $(this).closest('.address-block-wrapper');
                let $city = $block.find('.city-input');

                $city.html('<option value="">Loading...</option>');

                $.post("{{ route('get.cities') }}", {
                    state_id: stateID,
                    _token: '{{ csrf_token() }}'
                }, function(res) {
                    $city.html('<option value="">Select City</option>');
                    $.each(res.cities, function(id, name) {
                        $city.append('<option value="' + id + '">' + name + '</option>');
                    });
                });
            });


            $('#main-form').on('submit', function(e) {
                e.preventDefault();

                let submitBtn = $('#editTransportBtn');
                if (submitBtn.prop('disabled')) {
                    return false;
                }
                submitBtn.prop('disabled', true).text('Processing...');

                let form = $(this);
                let url = form.attr('action');
                let formData = form.serialize();

                // Clear previous errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();
                $('.ajax-error-contact').text('');

                $.post(url, formData, function(res) {
                    if (res.success) {
                        show_toastr('success', res.success);
                        setTimeout(() => {
                            window.location.href = res.redirect_url;
                        }, 1500);
                    }

                    if (res.error) {
                         submitBtn.prop('disabled', false).text('Submit');
                        show_toastr('error', res.message);
                    }

                }).fail(function(xhr) {
                    submitBtn.prop('disabled', false).text('Submit');
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;

                        $.each(errors, function(key, messages) {
                            // For arrayed inputs like country.0, state.1 etc.
                            if (key.includes('.')) {
                                let [field, index] = key.split('.');
                                let $block = $('.address-block-wrapper').eq(index);

                                if (field === 'contact') {
                                    let $contactInput = $('.contact-input').eq(index);
                                    $contactInput.addClass('is-invalid');
                                    $contactInput.after(
                                        `<div class="invalid-feedback">${messages[0]}</div>`
                                    );
                                } else {
                                    let $input = $block.find(`[name="${field}[]"]`);
                                    $input.addClass('is-invalid');
                                    $input.after(
                                        `<div class="invalid-feedback">${messages[0]}</div>`
                                    );
                                }
                            } else {
                                // Normal non-array fields
                                let $input = $(`[name="${key}"]`);
                                if ($input.length > 0) {
                                    $input.addClass('is-invalid');
                                    $input.after(
                                        `<div class="invalid-feedback">${messages[0]}</div>`
                                    );
                                } else {
                                    show_toastr('error', messages[0]);
                                }
                            }
                        });
                    } else {
                        show_toastr('error', "Something went wrong.");
                    }
                });
            });


        });
    </script>
@endsection
