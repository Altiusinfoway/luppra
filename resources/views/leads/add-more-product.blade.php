<div class="row mb-3 align-items-center product-row">
    <div class="col-md-4">

        @php
            $products = \App\Models\Products::InHouse()->where('created_by', '=', \Auth::user()->creatorId())->get()->select('name', 'id');
        @endphp

        <select name="products[]" class="form-select choices-select" data-choices data-choices-removeItem required>

            @foreach($products as $product)
            <option value="{{ $product['id'] }}">{{ $product['name'] }}</option>
            @endforeach

        </select>
    </div>
    <div class="col-md-3">
        <input type="number" name="price[]" class="form-control" placeholder="Price" required>
    </div>
    <div class="col-md-3">
        <input type="number" name="qty[]" class="form-control" placeholder="Quantity" required>
    </div>
    <div class="col-md-2 text-center">
        @if($row == 0)
            <span class="btn btn-sm btn-primary action-icon add-more-product"><i class="mdi mdi-plus text-white"></i></span>
        @else
            <span class="btn btn-sm btn-danger action-icon remove-row"><i class="mdi mdi-trash-can-outline text-white"></i></span>
        @endif
    </div>
</div>
