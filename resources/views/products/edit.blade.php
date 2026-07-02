<!-- Product Modal  Start -->

{{ Form::model($product, ['route' => ['products.update', $product->id], 'method' => 'PATCH', 'enctype' => 'multipart/form-data', 'id' => 'update-product', 'class' => 'needs-validation', 'autocomplete' => 'off']) }}



<div class="row">

    <div class="col-lg-12 mt-1">
        <label for="name" class="form-label">Name</label><x-required></x-required>
        {{ Form::text('name', $product->name, ['class' => 'form-control', 'id' => 'name', 'placeholder' => __('Enter name.'), 'required' => 'required']) }}
    </div>

    <div class="col-lg-12 mt-1">
        <label class="form-label">Category</label>

        <select name="category_id" class="form-select" >
            <option value="">Select Category</option>

            @foreach ($categories as $parent)
                <!-- Parent selectable -->
                <option value="{{ $parent->id }}" {{ $product->category_id == $parent->id ? 'selected' : '' }}>
                    {{ $parent->name }}
                </option>

                <!-- Children -->
                @foreach ($parent->children as $child)
                    <option value="{{ $child->id }}" {{ $product->category_id == $child->id ? 'selected' : '' }}>
                        &nbsp;&nbsp;— {{ $child->name }}
                    </option>
                @endforeach
            @endforeach
        </select>
    </div>


    <div class="col-lg-12 mt-1">
        <label for="code" class="form-label">SKU Code</label>

        {{ Form::text('sku_code', $product->sku_code, ['class' => 'form-control', 'id' => 'code', 'placeholder' => __('Enter code.')]) }}

    </div>

    <div class="col-md-12 mt-1">
        <label for="hsn_code" class="form-label">HSN Code</label>
        {{ Form::text('hsn_code', $product->hsn_code, ['class' => 'form-control', 'id' => 'hsn_code', 'placeholder' => __('Enter HSN Code.')]) }}

    </div>

    <div class="col-md-12 mt-1">
        <label for="stock_qty" class="form-label">Stock Qty</label>
        {{ Form::text('stock_qty', $product->stock_qty, ['class' => 'form-control', 'id' => 'stock_qty', 'placeholder' => __('Enter Stock Qty')]) }}

    </div>



    <div class="col-lg-12 mt-1">
        <label for="price" class="form-label">Price</label><x-required></x-required>

        {{ Form::number('price', $product->price, ['class' => 'form-control', 'id' => 'price', 'step' => '0.01', 'min' => '0', 'placeholder' => __('Enter price.'), 'required' => 'required']) }}

    </div>

     <div class="col-md-12 mt-1">
        <label for="gst_slab_master_id" class="form-label">GST (%)</label><x-required></x-required>
        {{ Form::select(
            'gst_slab_master_id',
            [0 => 'Select GST'] + $gst_all,
            old('gst_slab_master_id',$product->gst_slab_master_id),
            [
                'class' => 'form-select',
                'id' => 'gst_slab_master_id',
                 'required'
            ]
        ) }}
        @error('gst_slab_master_id')
            <small class="text-danger">{{ $message }}</small>
        @enderror

    </div>

    <div class="col-md-6 mt-1">
        <label for="price" class="form-label">Unit Type</label><x-required></x-required>

        {{ Form::select('unit_type', ['' => 'Select Type'] + $unitTypes, $product->unit_type, [
            'class' => 'form-select',
            'id' => 'unit_type',
            'required',
            'onChange="loadUnits(this)"',
        ]) }}

    </div>

    <div class="col-md-6 mt-1">
        <label for="price" class="form-label">Unit</label><x-required></x-required>

        {{ Form::select('unit', ['' => 'Select Type'] + $units, $product->unit, [
            'class' => 'form-select',
            'id' => 'unit',
            'required',
        ]) }}

    </div>


    {{-- <div class="col-lg-12" id="dealer_hidden" >
            <label for="dealer_price" class="form-label">Dealer Price</label>
            {{ Form::number('dealer_price', $product->dealer_price, ['class' => 'form-control', 'id' => 'dealer_price', 'placeholder' => __('Enter dealer price.')]) }}

        </div> --}}

    <div class="col-lg-12 mt-1">
        <label for="image" class="form-label">Choose File</label><x-required></x-required>
        {{ Form::file('image', ['class' => 'form-control']) }}
    </div>

    @if ($product->image)
        <div class="col-lg-6 mt-4">
            <label for="image" class="form-label">Current Image</label>
            <img src="{{ $product->image }}" alt="product" class="img-thumbnail" width="100">
        </div>
    @endif


    <div class="mt-4">
        <div class="hstack gap-2 justify-content-end">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success" id="editProduct">Edit
                Product</button>
        </div>
    </div>

</div>
{{ Form::close() }}


<script>
    $(document).ready(function() {
        let dealer = document.getElementById('dealer_hidden');

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

            // Create options
            var options = '<option value="">Select Unit</option>';

            $.each(res, function(key, value) {
                options += '<option value="' + key + '">' + value + '</option>';
            });

            // Set options
            $('#unit').html(options);

        });

    }
</script>


<!-- Product Modal  Ends -->
