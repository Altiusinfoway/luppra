@extends('layouts.app')

@section('page-css')
    <style>
        .transport-form-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .transport-form-suite .hero-shell,
        .transport-form-suite .form-shell {
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        }

        .transport-form-suite .hero-shell {
            background:
                radial-gradient(circle at top right, rgba(16, 185, 129, 0.14), transparent 30%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        .transport-form-suite .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            padding: 7px 12px;
            border-radius: 999px;
            border: 1px solid #d1fae5;
            background: rgba(255, 255, 255, 0.86);
            color: #047857;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .transport-form-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.86);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .transport-form-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .transport-form-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
        }

        .transport-form-suite .section-card {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 16px;
        }
    </style>
@endsection

@section('content')
    <div class="page-content transport-form-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-7">
                                    <span class="hero-eyebrow">Logistics Directory</span>
                                    <h2 class="mt-3 mb-2">Edit Transport</h2>
                                    <p class="text-muted mb-0">Update transport profile, contacts, and address coverage with the same cleaner logistics form treatment.</p>
                                </div>
                                <div class="col-lg-5">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('transports.index') }}">Transport</a></li>
                                            <li class="breadcrumb-item active">Edit</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-3 mb-4 justify-content-center">
                <div class="col-md-6 col-xl-3">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Logistics Setup</span>
                            <h3>Edit Partner</h3>
                            <p class="text-muted mb-0 mt-2">Update transporter profile details with the same stronger form hierarchy used across the refreshed logistics workflow.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Profile Scope</span>
                            <h3>Contacts + Address</h3>
                            <p class="text-muted mb-0 mt-2">Keep core partner info, contact numbers, and address coverage grouped in one cleaner edit experience.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-xl-12 col-xxl-10">
                    <div class="card form-shell">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title  mb-0">Transport Edit</h5>
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('transports.update', $transport['id']) }}" method="POST" id="main-form">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-lg-8">
                                        <div class="section-card h-100">
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
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="section-card h-100">
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

                                    </div>

                                    <hr class="divider">

                                    <div class="col-md-12">
                                        <div class="section-card">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="card-title mb-0">Address Details</h5>
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

                                </div>
                                <div class="text-center mt-3">
                                    <button type="submit" class="btn btn-sm btn-primary w-sm" id="editTransportBtn">Submit</button>
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
