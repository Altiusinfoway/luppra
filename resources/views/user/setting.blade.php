@extends('layouts.app')
@section('page-css')
<style>
    .user-settings-suite {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
    }
    .user-settings-suite .hero-shell,
    .user-settings-suite .form-shell {
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 26px;
        background:
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
            radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
            #ffffff;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
    }
    .user-settings-suite .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.76);
        border: 1px solid #dbeafe;
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .user-settings-suite .summary-card {
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.84);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
    }
    .user-settings-suite .summary-card .label {
        display: block;
        margin-bottom: 8px;
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .user-settings-suite .summary-card h3 {
        margin: 0;
        font-size: 1.7rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #0f172a;
    }
    .user-settings-suite .section-intro {
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    }
    .user-settings-suite .form-actions {
        padding-top: 1rem;
        border-top: 1px solid rgba(226, 232, 240, 0.8);
    }
    .user-settings-suite .activity-shell {
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.05);
    }
    .big-checkbox {
        transform: scale(1.6);
        margin-top: 4px;
        cursor: pointer;
    }
</style>
@endsection

@section('content')
    <div class="page-content user-settings-suite">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-8">
                                    <span class="hero-eyebrow">Workspace Setup</span>
                                    <h1 class="mb-3">Settings</h1>
                                    <p class="text-muted mb-0">Manage website identity, tax details, integrations, address blocks, and branding from a cleaner settings surface.</p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                            <li class="breadcrumb-item active">Settings</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12 mb-4">
                    <div class="row g-3">
                        <div class="col-md-6 col-xl-3">
                            <div class="card summary-card h-100">
                                <div class="card-body">
                                    <span class="label">Workspace</span>
                                    <h3>Settings</h3>
                                    <p class="text-muted mb-0 mt-2">Manage branding, company identity, integrations, and address blocks from one admin surface.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="card summary-card h-100">
                                <div class="card-body">
                                    <span class="label">Coverage</span>
                                    <h3>Business Setup</h3>
                                    <p class="text-muted mb-0 mt-2">Core business metadata and operational defaults stay editable inside the same configuration flow.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card form-shell">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Settings</h4>
                        </div>
                        <!-- end card header -->
                        <div class="card-body">
                            <div class="section-intro">
                                <h5 class="mb-1">Company Configuration</h5>
                                <p class="text-muted mb-0">Update website identity, tax and contact details, integrations, logo, and structured address information from one settings workspace.</p>
                            </div>

                            <form id="main-form" action="{{ route('settings.update', \Auth::user()->id) }}"  enctype="multipart/form-data"  method="post">
                                @csrf

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="website_name" class="form-label">Website Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="website_name"
                                                        name="website_name" placeholder="Enter Website Name"
                                                        value="{{ $setting_rcd['website_name'] ?? '' }}">
                                                    <span class="text-danger" id="error-website_name"></span>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="website_url" class="form-label">Website Url <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="website_url"
                                                        name="website_url" placeholder="Enter Website Url"
                                                        value="{{ $setting_rcd['website_url'] ?? '' }}">
                                                    <span class="text-danger" id="error-website_url"></span>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="website_short_name" class="form-label">Website Short Name
                                                        <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="website_short_name"
                                                        name="website_short_name" placeholder="Enter Website Short Name"
                                                        value="{{ $setting_rcd['website_short_name'] ?? '' }}">
                                                    <span class="text-danger" id="error-website_short_name"></span>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="email" class="form-label">Email <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="email" name="email"
                                                        placeholder="Enter Email" value="{{ $setting_rcd['email'] ?? '' }}">
                                                    <span class="text-danger" id="error-email"></span>
                                                </div>
                                            </div>

                                        </div>


                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="phone" class="form-label">Phone <span
                                                            class="text-danger">*</span></label>
                                                    <input type="number" class="form-control" id="phone" name="phone"
                                                        placeholder="Enter Phone" value="{{ $setting_rcd['phone'] ?? '' }}">
                                                    <span class="text-danger" id="error-phone"></span>
                                                </div>
                                            </div>



                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="gst_no" class="form-label">GST No <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="gst_no" name="gst_no"
                                                        placeholder="Enter GST No"
                                                        value="{{ $setting_rcd['gst_no'] ?? '' }}">
                                                    <span class="text-danger" id="error-gst_no"></span>
                                                </div>
                                            </div>

		                                             <div class="col-md-3">
		                                                <div class="mb-3">
		                                                    <label for="pan_no" class="form-label">PAN No <span
		                                                            class="text-danger">*</span></label>
		                                                    <input type="text" class="form-control" id="pan_no"
		                                                        name="pan_no" placeholder="Enter PAN No"
		                                                        value="{{ $setting_rcd['pan_no'] ?? '' }}">
		                                                    <span class="text-danger" id="error-pan_no"></span>
		                                                </div>
		                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="facebook_spreadsheet_id" class="form-label">Facebook Spreadsheet ID</label>
                                                    <input type="text" class="form-control" id="facebook_spreadsheet_id"
                                                        name="facebook_spreadsheet_id" placeholder="Enter Facebook Spreadsheet ID"
                                                        value="{{ $setting_rcd['facebook_spreadsheet_id'] ?? '' }}">
                                                    <span class="text-danger" id="error-facebook_spreadsheet_id"></span>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="india_mart_key" class="form-label">IndiaMart Key</label>
                                                    <input type="text" class="form-control" id="india_mart_key"
                                                        name="india_mart_key" placeholder="Enter IndiaMart Key"
                                                        value="{{ $setting_rcd['india_mart_key'] ?? '' }}">
                                                    <span class="text-danger" id="error-india_mart_key"></span>
                                                </div>
                                            </div>

	                                                <div class="col-md-3">
	                                                    <div class="mb-3">
	                                                        <label for="is_allowed_discount" class="form-label">Discount</label>
                                                        <div class="form-check form-switch mt-2">
                                                            <input type="checkbox" class="form-check-input" id="is_allowed_discount"
                                                                name="is_allowed_discount" value="1"
                                                                {{ (string) old('is_allowed_discount', $setting_rcd['is_allowed_discount'] ?? \App\Models\Utility::isDiscountAllowed()) === '1' ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="is_allowed_discount">
                                                                Show discount in quote and invoice
                                                            </label>
                                                        </div>
                                                        <span class="text-danger" id="error-is_allowed_discount"></span>
                                                    </div>
                                                </div>

	                                             <div class="col-md-3">
	                                                <div class="mb-3">
	                                                    <label for="image_final" class="form-label">Logo <span
	                                                            class="text-danger">*</span></label>
                                                    @php
                                                        $default_img = \App\Models\Utility::defaultImage();
                                                        $website_img = \App\Models\Utility::websiteLogo();
                                                    @endphp

                                                    <input type="file" class="form-control" id="image_final"
                                                        name="image_final" accept="image/*">

                                                    <div class="mb-2">
                                                        <img id="preview-image" class="mt-3"
                                                            src="{{ !empty($website_img) ? $website_img : $default_img }}"
                                                            alt="Preview" style="max-height: 100px;"
                                                            class="img-thumbnail">
                                                    </div>

                                                    <span class="text-danger" id="error-image_final"></span>
                                                </div>
                                            </div>

                                        </div>



                                        <h4 >Company Address</h4>
                                        <div class="row mt-3">

                                            @foreach ($address_list as $index => $address_rcd)
                                                @if ($index == 1)

                                                 <div class="col-md-1 mt-3">
                                                    <label class="me-2">Same</label>
                                                    <input type="checkbox" name="is_same_adr" value="1"
                                                        class="form-check-input big-checkbox same-address"
                                                        data-target="shipping_block_1">
                                                </div>
                                                    <h4 class="mt-3 mb-3">Billing Address</h4>

                                                @endif

                                                @include('address.multiple_address_list', compact('index', 'address_rcd'))
                                            @endforeach

                                        </div>




                                    </div>
                                </div>


                                <!-- end card -->
                                <div class="text-center mt-3 form-actions">
                                    <button type="submit" class="btn btn-success w-sm">Submit</button>
                                </div>
                            </form>
                            <!-- end card body -->
                        </div>


                    </div>
                    <!-- end col -->

                </div>
                <!-- end row -->
            </div>

            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="card activity-shell">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Activity History</h4>
                        </div>
                        <div class="card-body">
                            @include('activity._timeline', [
                                'activities' => $settingsActivityTimeline,
                                'emptyMessage' => 'No activity found for company settings.',
                            ])
                        </div>
                    </div>
                </div>
            </div>




        </div>
    </div>
@endsection

@section('page-script')
    <script>
        $(document).ready(function()
        {
                $('.remove-address-btn').hide();

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

            //submit
             $('#main-form').on('submit', function (e)
             {
                e.preventDefault();

                let form = $(this)[0];
                let url = $(this).attr('action');
                let formData = new FormData(form);

                // Clear old validation errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();
                $('.text-danger').text('');

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (res) {
                        if (res.success) {
                            show_toastr('success', res.success);
                            setTimeout(() => {
                                window.location.href = res.redirect_url;
                            }, 1500);
                        }
                    },
                    error: function (xhr) {
                         if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;

                            $.each(errors, function (key, messages) {

                                // Handle array fields like country.0
                                if (key.includes('.')) {
                                    let parts = key.split('.');
                                    let field = parts[0];   // country
                                    let index = parts[1];   // 0

                                    let $block = $('.address-block-wrapper').eq(index);
                                    let $input = $block.find(`[name="${field}[]"]`);

                                    if ($input.length) {
                                        $input.addClass('is-invalid');

                                        if ($input.next('.invalid-feedback').length === 0) {
                                            $input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
                                        }
                                    }
                                }
                                // Normal (non-array) fields
                                else {
                                    let $input = $(`[name="${key}"]`);

                                    if ($input.length) {
                                        $input.addClass('is-invalid');
                                        $input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
                                    } else if ($("#error-" + key).length) {
                                        $("#error-" + key).text(messages[0]);
                                    }
                                }
                            });
                        }
                       /*if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;

                            $.each(errors, function (key, messages) {
                                let $input = $(`[name="${key}"]`);
                                if ($input.length > 0) {
                                    $input.addClass('is-invalid');
                                    $input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
                                } else if (document.getElementById("error-" + key)) {
                                    $('#error-' + key).text(messages[0]);
                                } else {
                                    console.log('error', messages[0]);
                                }
                            });
                        } else {
                            console.log('error', 'Something went wrong.');
                        }
                            */
                    }
                });
            });


        });
    </script>

    <script>
        document.getElementById('image_final').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('preview-image');

            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };

                reader.readAsDataURL(file);
            } else {
                preview.src = '#';
                preview.style.display = 'none';
            }
        });
    </script>

    <script>
$(document).ready(function () {

    function toggleAddress($checkbox) {
        let targetId = $checkbox.data("target");
        let $target = $("#" + targetId);

        if ($checkbox.is(":checked")) {
            $target.hide();   // hide target block (id=_1)
        } else {
            $target.show();   // show again when unchecked
        }
    }

    // On checkbox change
    $(document).on("change", ".same-address", function () {
        toggleAddress($(this));
    });

    // Run once on page load (for pre-checked case)
    $(".same-address").each(function () {
        toggleAddress($(this));
    });

});
    </script>
@endsection
