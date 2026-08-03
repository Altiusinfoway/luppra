@extends('layouts.app')

@section('page-css')
<style>
    .vendor-form-suite {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
    }

    .vendor-form-suite .hero-shell,
    .vendor-form-suite .form-shell {
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 26px;
        background:
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
            radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
            #ffffff;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
    }

    .vendor-form-suite .hero-eyebrow {
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

    .vendor-form-suite .summary-card {
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.84);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
    }

    .vendor-form-suite .summary-card .label {
        display: block;
        margin-bottom: 8px;
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .vendor-form-suite .summary-card h3 {
        margin: 0;
        font-size: 1.7rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #0f172a;
    }

    .vendor-form-suite .section-intro {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #f8fafc;
        padding: 16px 18px;
        margin-bottom: 22px;
    }

    .vendor-form-suite .section-card {
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.05);
    }

    .vendor-form-suite .product-shell {
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.95);
    }

    .vendor-form-suite .form-actions {
        border-top: 1px solid #e2e8f0;
        margin-top: 8px;
        padding-top: 20px;
    }

    .star {
        display: none;
    }

    .star + label {
        font-size: 24px;
        color: #ccc;
        cursor: pointer;
    }

    .star:checked ~ label {
        color: #ffc700;
    }

    .star + label:hover,
    .star + label:hover ~ label {
        color: #deb217;
    }
</style>
@endsection

@section('content')
<div class="page-content vendor-form-suite">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-8">
                                <span class="hero-eyebrow">Procurement Directory</span>
                                <h1 class="mb-3">Edit Vendor</h1>
                                <p class="text-muted mb-0">Update supplier details, ratings, addresses, and supply products inside the same polished vendor workspace.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('vendors.index') }}">Vendors</a></li>
                                        <li class="breadcrumb-item active">Edit</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Workflow</span>
                        <h3>Edit</h3>
                        <p class="text-muted mb-0 mt-2">Refine supplier details, quality rating, and procurement relationships from one place.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Vendor Status</span>
                        <h3>{{ $vendor['is_active'] == 1 ? 'Active' : 'Inactive' }}</h3>
                        <p class="text-muted mb-0 mt-2">Quickly review whether this vendor is currently available for sourcing flows.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card form-shell">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title  mb-0">Vendor Edit</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('vendors.update',$vendor['id']) }}" method="POST" id="vendor_form_edit">
                            @csrf
                        <div class="section-card p-3 p-lg-4">
                            <div class="section-intro">
                                <h5 class="mb-1">Vendor Configuration</h5>
                                <p class="text-muted mb-0">Update supplier business details, address data, and supply-product links from the same procurement workspace.</p>
                            </div>
                        <div class="row">
                            <div class="col-lg-6 border-end">

                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label" for="name">name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" id="name"
                                            placeholder="Enter name" value="{{ $vendor['name'] }}">
                                        <span class="text-danger error-name"></span>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="contact">Contact <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" name="contact" class="form-control" id="contact"
                                                placeholder="Enter Contact" value="{{ $vendor['contact'] }}">
                                            <span class="text-danger error-contact"></span>
                                        </div>
                                    </div>

                                </div>
                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="email">Email <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control" id="email"
                                                placeholder="Enter Email" value="{{ $vendor['email'] }}">
                                              <span class="text-danger error-email"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="company_name">Company Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="company_name" class="form-control"
                                                id="company_name" placeholder="Enter Company Name"
                                                value="{{ $vendor['company_name'] }}">
                                            <span class="text-danger error-company_name"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="gst_no">GST No </label>
                                            <input type="text" name="gst_no" class="form-control" id="gst_no"
                                                placeholder="Enter GST No." value="{{ $vendor['gst_no'] }}">
                                           <span class="text-danger error-gst_no"></span>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="rate">Product Rate <span
                                                    class="text-danger">*</span></label>
                                            <div class="form-group required">
                                                <div class="d-flex flex-row-reverse justify-content-end">
                                                    @for ($i = 5; $i >= 1; $i--)
                                                        <input class="star" type="radio"
                                                            id="star-{{ $i }}" name="rate"
                                                            value="{{ $i }}"
                                                            {{ old('rate', isset($vendor) ? $vendor->rate : '') == $i ? 'checked' : '' }}>
                                                        <label for="star-{{ $i }}">&#9733;</label>
                                                    @endfor
                                                </div>
                                            </div>
                                            <span class="text-danger error-rate"></span>
                                        </div>
                                    </div>

                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="description" class="form-label">Description <span
                                                class="text-danger">*</span></label>
                                        <textarea class="form-control" name="description" id="description" rows="9" placeholder="Enter Description"
                                            style="height: 120px; direction: ltr; text-align: left;">{{ $vendor['description'] }}</textarea>

                                         <span class="text-danger error-description"></span>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="" class="form-label">Status <span
                                                class="text-danger">*</span></label>
                                        <div>
                                            <input class="form-check-input" class="form-check" type="radio"
                                                name="is_active" id="active" value="1"
                                                @if ($vendor['is_active'] == 1) checked @endif>
                                            <label class="form-check-label" for="flexRadioDefault1">
                                                Active
                                            </label>
                                            &nbsp;
                                            <input class="form-check-input" class="form-check" type="radio"
                                                name="is_active" value="0" id="inactive"
                                                @if ($vendor['is_active'] == 0) checked @endif>
                                            <label class="form-check-label" for="flexRadioDefault2">
                                                In Active
                                            </label>

                                        </div>
                                    </div>

                                </div>

                               <div class="row">
                                      @include('address.address_list', ['address_rcd'=>$address_data])
                               </div>


                            </div>

                            <div class="col-lg-6">

                                <div class="card text-start product-shell">
                                    <div class="card-header">
                                        <p class="card-text">Supply Products</p>
                                    </div>
                                    <div class="card-body">

                                        <div class="row">
                                            <label class="form-label">Products</label>
                                            <div class="col-md-8">
                                                <select name="raw_ids[]" id="raw_ids" class="form-control choices-select mb-3" data-choices data-choices-removeItem>
                                                    <option value=""></option>
                                                    @foreach ($products as $product)
                                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="button" id="loadProductRaw" class="btn btn-primary add-dana btn-block form-control">Add</button>
                                            </div>
                                        </div>

                                        <div id="productRawList" class="mt-2">

                                            @include('vendor.add-more-products', ['products' => $selectedProducts, 'productDetail' => $productDetail])

                                        </div>


                                    </div>
                                </div>

                            </div>

                        </div>


                            <div class="text-center mb-3 form-actions">
                                <button type="submit" class="btn btn-primary w-sm" id="vendorEditSubmit">Save Vendor</button>
                            </div>
                        </div>

                        </form>
                        <!-- end card body -->
                        </div>
                </div>
            </div><!--end col-->
        </div><!--end row-->

    </div>
    <!-- container-fluid -->
</div>

<script>

    $(document).ready(function(){

        $('#loadProductRaw').click(function(){

            var $raw_ids = $('#raw_ids').val();

            if ($raw_ids == '') {
                show_toastr('error',"Please select raw material.");
                return;
            }

            var $addedRawIds = $('[name="productIds[]"]').map(function() {
                return $(this).val();
            }).get();

            // Check for duplicates
            if ($addedRawIds.includes($raw_ids)) {
                show_toastr('error',"You cannot add the same raw material twice.");
                return;
            }

            var url = "{{ route('vendors.get-selected-product') }}";
            postAjax(url, {productIds:$raw_ids},function (res) {

                $('#productRawList').prepend(res);

            });

        });

    });


    $(document).on('click', '.remove-row', function () {
        $(this).closest('.product-row').remove();
    });

</script>
<script>
      $(document).ready(function () {

    $('#vendor_form_edit').on('submit', function(e) {
        e.preventDefault();

        const submitButton = document.getElementById('vendorEditSubmit');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerText = 'Saving...';
        }

        let form = this;
        let formData = new FormData(form);

        $('.error').text('');
        $('.error-message').text('');

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
                if (res.success === 'yes') {
                    show_toastr("success", res.message || "Vendor updated successfully");

                    setTimeout(function () {
                        window.location.href = res.redirect_url || "{{ route('vendors.index') }}";
                    }, 1200);
                }


                if (res.error === 'yes') {
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerText = 'Save Vendor';
                    }
                    show_toastr("error", res.message || "Something went wrong");
                }
            },

            error: function(xhr) {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerText = 'Save Vendor';
                }
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;

                    $.each(errors, function(key, value) {


                        if ($("#error-" + key).length) {
                            $("#error-" + key).text(value[0]);
                        }

                        show_toastr("error", value[0]);
                    });

                    return;
                }
                show_toastr("error", "Something went wrong.");
            }
        });
    });

});
</script>



@endsection
