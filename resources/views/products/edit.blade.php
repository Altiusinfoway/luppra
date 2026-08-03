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
    .product-modal-form .marketplace-note {
        border: 1px solid #bfdbfe;
        border-radius: 16px;
        background: linear-gradient(135deg, #eff6ff 0%, #f8fbff 100%);
        padding: 12px 14px;
        color: #1e3a8a;
    }
    .product-modal-form .current-image-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 12px;
        background: #ffffff;
        display: inline-flex;
        flex-direction: column;
        gap: 10px;
    }
</style>

<!-- Product Modal  Start -->
{{ Form::model($product, ['route' => ['products.update', $product->id], 'method' => 'PATCH', 'enctype' => 'multipart/form-data', 'id' => 'update-product', 'class' => 'needs-validation product-modal-form', 'autocomplete' => 'off']) }}

<div class="row g-3">
    <div class="col-12">
        <div class="section-card">
            <div class="section-title">Basic Details</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Name <span class="required-dot">*</span></label>
                    {{ Form::text('name', $product->name, ['class' => 'form-control', 'id' => 'name', 'placeholder' => __('Enter product name'), 'required' => 'required']) }}
                </div>
                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">Select Category</option>
                        @foreach ($categories as $parent)
                            <option value="{{ $parent->id }}" {{ $product->category_id == $parent->id ? 'selected' : '' }}>
                                {{ $parent->name }}
                            </option>
                            @foreach ($parent->children as $child)
                                <option value="{{ $child->id }}" {{ $product->category_id == $child->id ? 'selected' : '' }}>
                                    -- {{ $child->name }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="code" class="form-label">Master SKU <span class="required-dot">*</span></label>
                    {{ Form::text('sku_code', $product->sku_code, ['class' => 'form-control', 'id' => 'code', 'placeholder' => __('Enter internal master SKU'), 'required' => 'required']) }}
                </div>
                <div class="col-md-6">
                    <label for="hsn_code" class="form-label">HSN Code</label>
                    {{ Form::text('hsn_code', $product->hsn_code, ['class' => 'form-control', 'id' => 'hsn_code', 'placeholder' => __('Enter HSN code')]) }}
                </div>
                <div class="col-md-6">
                    <label for="stock_qty" class="form-label">Stock Qty</label>
                    {{ Form::text('stock_qty', $product->stock_qty, ['class' => 'form-control', 'id' => 'stock_qty', 'placeholder' => __('Enter stock qty')]) }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="section-card">
            <div class="section-title">Pricing & Tax</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="price" class="form-label">Price <span class="required-dot">*</span></label>
                    {{ Form::number('price', $product->price, ['class' => 'form-control', 'id' => 'price', 'step' => '0.01', 'min' => '0', 'placeholder' => __('Enter price'), 'required' => 'required']) }}
                </div>
                <div class="col-md-6">
                    <label for="gst_slab_master_id" class="form-label">GST (%) <span class="required-dot">*</span></label>
                    {{ Form::select(
                        'gst_slab_master_id',
                        [0 => 'Select GST'] + $gst_all,
                        old('gst_slab_master_id', $product->gst_slab_master_id),
                        [
                            'class' => 'form-select',
                            'id' => 'gst_slab_master_id',
                            'required',
                        ]
                    ) }}
                    @error('gst_slab_master_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="section-card">
            <div class="section-title">Unit & Media</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="unit_type" class="form-label">Unit Type <span class="required-dot">*</span></label>
                    {{ Form::select('unit_type', ['' => 'Select Type'] + $unitTypes, $product->unit_type, [
                        'class' => 'form-select',
                        'id' => 'unit_type',
                        'required',
                        'onChange' => 'loadUnits(this)',
                    ]) }}
                </div>
                <div class="col-md-6">
                    <label for="unit" class="form-label">Unit <span class="required-dot">*</span></label>
                    {{ Form::select('unit', ['' => 'Select Type'] + $units, $product->unit, [
                        'class' => 'form-select',
                        'id' => 'unit',
                        'required',
                    ]) }}
                </div>
                <div class="col-md-12">
                    <label for="image" class="form-label">Choose File</label>
                    {{ Form::file('image', ['class' => 'form-control']) }}
                </div>
                @if ($product->image)
                    <div class="col-md-12">
                        <div class="current-image-card">
                            <div class="form-label mb-0">Current Image</div>
                            <img src="{{ $product->image }}" alt="product" class="img-thumbnail" width="100">
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="marketplace-note d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span>Marketplace listings and product-level marketplace reports are managed on a dedicated page.</span>
            <a href="{{ route('products.marketplace', $product->id) }}" class="btn btn-sm btn-primary">Open Marketplace Page</a>
        </div>
    </div>

    <div class="col-12 mt-2">
        <div class="hstack gap-2 justify-content-end">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success" id="editProduct">Edit Product</button>
        </div>
    </div>
</div>
{{ Form::close() }}

<script>
    $(document).ready(function() {
        let dealer = document.getElementById('dealer_hidden');

        if (!dealer) {
            return;
        }

        let get_first = $("#user_type").val();
        if (get_first == 'customer') {
            dealer.style = "display:none";
        }
        $("#user_type").change(function() {
            let get_val = $("#user_type").val();
            if (get_val == 'vendor') {
                dealer.style = "display:block";
            } else {
                dealer.style = "display:none";
            }
        });
    });
</script>

<script>
    function loadUnits(e) {
        var url = "{{ route('get.units', ':id') }}";
        url = url.replace(':id', e.value);
        getAjax(url, function(res) {
            var options = '<option value="">Select Unit</option>';

            $.each(res, function(key, value) {
                options += '<option value="' + key + '">' + value + '</option>';
            });

            $('#unit').html(options);
        });
    }
</script>

<!-- Product Modal  Ends -->
