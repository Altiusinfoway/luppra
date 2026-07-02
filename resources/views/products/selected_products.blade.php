@if(!$products->isEmpty())
@foreach($products as $product)
    <div class="row align-items-center mt-2 product-row">
        <div class="col-md-6">
            <label for="metal" class="form-label">Name</label>
            <input type="text" value="{{ $product->name }}" class="form-control" readonly>
            <input type="hidden" name="productIds[]" value="{{ $product->id }}">
        </div>
        <div class="col-md-2">
            <label for="qty" class="form-label">Qty</label>
            <input type="number" step="0.01" name="qty[]" class="form-control" value="{{ isset($usage[$product->id]) ? $usage[$product->id]['qty'] :'' }}" required>
        </div>
        <div class="col-md-2">
            <label for="units" class="form-label">Unit Type</label>

            {{ Form::select('units[]', \App\Models\Utility::getUnits($product->unit_type), isset($usage[$product->id]) ? $usage[$product->id]['unit_id'] :$product->unit, [
                'class' => 'form-select',
                'id' => 'unit',
                'required',
            ]) }}
        </div>
        <div class="col-md-2 text-center">
            <span class="btn btn-sm btn-danger action-icon remove-row"><i class="mdi mdi-trash-can-outline text-white"></i></span>
        </div>
    </div>
@endforeach
@endif
