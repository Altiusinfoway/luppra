@extends('layouts.app')

@section('content')
<style>
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
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Vendor Operation Section</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('vendors.index') }}">Vendor Operation</a></li>
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
                            <h5 class="card-title  mb-0">Vendor Edit</h5>
                        </div>
                    </div>
                    <div class="card-body">


                        <form action="{{ route('vendors.update',$vendor['id']) }}" method="POST" id="vendor_form_edit">
                            @csrf
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

                                <div class="card text-start">
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


                            <div class="text-center mb-3">
                                <button type="submit" class="btn btn-success w-sm">Submit</button>
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
                    show_toastr("error", res.message || "Something went wrong");
                }
            },

            error: function(xhr) {
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
