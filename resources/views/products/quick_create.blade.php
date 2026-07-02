<style>
    .product-modal-form .section-card {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 12px;
        background: #f8fafc;
    }
    .product-modal-form .section-title {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .4px;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 10px;
    }
    .product-modal-form .form-label {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
    }
    .product-modal-form .required-dot {
        color: #dc2626;
        margin-left: 3px;
    }
    .product-modal-form .form-control,
    .product-modal-form .form-select {
        min-height: 40px;
    }
</style>

<!-- Product Modal  Start -->
{{ Form::open(array('route' => 'products.quick_store', 'method' => 'post', 'enctype' => "multipart/form-data", 'id' => 'quick-create-product', 'class' => 'needs-validation product-modal-form', 'novalidate', 'autocomplete' => 'off')) }}

    <div class="row g-3">
        <div class="col-12">
            <div class="section-card">
                <div class="section-title">Basic Details</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Name <span class="required-dot">*</span></label>
                        {{ Form::text('name', null, ['class' => 'form-control', 'id' => 'name', 'placeholder' => __('Enter product name'), 'required' => 'required']) }}
                    </div>

                    <div class="col-md-6">
                        <label for="code" class="form-label">SKU Code <span class="required-dot">*</span></label>
                        {{ Form::text('code', null, ['class' => 'form-control', 'id' => 'code', 'placeholder' => __('Enter SKU code'), 'required' => 'required']) }}
                    </div>

                    <div class="col-md-6">
                        <label for="hsn_code" class="form-label">HSN Code</label>
                        {{ Form::text('hsn_code', null, ['class' => 'form-control', 'id' => 'hsn_code', 'placeholder' => __('Enter HSN code')]) }}
                    </div>

                    <div class="col-md-6">
                        <label for="price" class="form-label">Price <span class="required-dot">*</span></label>
                        {{ Form::number('price', null, ['class' => 'form-control', 'id' => 'price', 'step' => '0.01', 'min' => '0', 'placeholder' => __('Enter price'), 'required' => 'required']) }}
                    </div>
                    <div class="col-md-6">
                        <label for="gst_slab_master_id" class="form-label">GST (%) <span class="required-dot">*</span></label>
                        {{ Form::select('gst_slab_master_id', [0 => 'Select GST'] + ($gst_all ?? []), old('gst_slab_master_id'), [
                            'class' => 'form-select',
                            'id' => 'gst_slab_master_id',
                            'required',
                        ]) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="section-card">
                <div class="section-title">Unit & Media</div>
                <div class="row g-3">
                    <div class="col-lg-12" id="dealer_hidden">
                        <label for="dealer_price" class="form-label">Dealer Price</label>
                        {{ Form::number('dealer_price', null, ['class' => 'form-control', 'id' => 'dealer_price', 'placeholder' => __('Enter dealer price')]) }}
                    </div>

                    <div class="col-md-6">
                        <label for="unit_type" class="form-label">Unit Type <span class="required-dot">*</span></label>
                        {{ Form::select('unit_type', ['' => 'Select Type'] + $unitTypes, null, [
                            'class' => 'form-select',
                            'id' => 'unit_type',
                            'required',
                            'onChange' => 'loadUnits(this)',
                        ]) }}
                    </div>

                    <div class="col-md-6">
                        <label for="unit" class="form-label">Unit <span class="required-dot">*</span></label>
                        {{ Form::select('unit', ['' => 'Select Unit'], null, [
                            'class' => 'form-select',
                            'id' => 'unit',
                            'required',
                        ]) }}
                    </div>

                    <div class="col-lg-12">
                        <label for="image" class="form-label">Product Image <span class="required-dot">*</span></label>
                        {{ Form::file('image', ['class' => 'form-control', 'required']) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 mt-2">
            <div class="hstack gap-2 justify-content-end">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success" id="addNewLead">Add Product</button>
            </div>
        </div>
    </div>
{{ Form::close() }}


<script>
    $(document).ready(function(){
        let dealer= document.getElementById('dealer_hidden');
        dealer.style="display:none";
        $("#user_type").change(function () {
           let get_val=$("#user_type").val();
           if(get_val == 'vendor')
           {
            dealer.style="display:block";
           }
           else
           {
            dealer.style="display:none";
           }
        });
    });
</script>
<script>

    function loadUnits(e){

        var url = "{{ route('get.units', ':id') }}";
        url = url.replace(':id', e.value);
        getAjax(url, function (res) {

            // Create options
            var options = '<option value="">Select Unit</option>';

            $.each(res, function (key, value) {
                options += '<option value="' + key + '">' + value + '</option>';
            });

            // Set options
            $('#unit').html(options);

        });

    }
</script>
<!-- Product Modal  Ends -->
