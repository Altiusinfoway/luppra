<!-- Product Modal Start -->
<style>
/* Modal and Form Styling */
.modal-lg-custom-height {
    max-height: 90vh;
    height: 90vh;
}
.modal-content {
    height: 100%;
    overflow-y: auto;
}
.color-dropdown {
    position: relative;
    display: inline-block;
    z-index: 10;
    width: 100%;
}
.color-dropdown-btn {
    padding: 10px;
    cursor: pointer;
    background-color: #f1f1f1;
    border: 1px solid #ccc;
    width: 100%;
    text-align: left;
    display: inline-block;
}
.color-dropdown-content {
    display: none;
    position: absolute;
    z-index: 100;
    background-color: white;
    width: 100%;
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
    padding: 5px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(30px, 1fr));
    gap: 5px;
    max-height: 150px;
    overflow-y: auto;
    top: 100%;
    left: 0;
    margin: 1%;
}
.color-option {
    width: 30px;
    height: 30px;
    border-radius: 5px;
    cursor: pointer;
    transition: transform 0.3s ease;
    border: 1px solid #ddd;
}
.color-option:hover {
    transform: scale(1.2);
}
.color-option.selected {
    outline: 3px solid #333;
}
.color-dropdown-content::-webkit-scrollbar {
    width: 8px;
}
.color-dropdown-content::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}
.color-dropdown-content::-webkit-scrollbar-thumb:hover {
    background: #555;
}
.color-dropdown-error {
    color: red;
    margin-top: 5px;
    margin-bottom: 5px;
    display: none;
}
</style>

{{ Form::open(['route' => ['products.store-productraw', $product_id], 'method' => 'post', 'enctype' => "multipart/form-data", 'id'=>'create-productdetail', 'class'=>'needs-validation', 'novalidate', 'autocomplete' => 'off']) }}


<div class="row mt-2">
    <div class="col-md-6">
        <label for="metal" class="form-label">Metal</label>
        {{ Form::select('matal_id', ['' => 'Select Metal'] + $metal_list, $product_raw->matal_id ?? null, ['class' => 'form-select mb-3', 'required']) }}
    </div>
    <div class="col-md-6">
        <label for="metal_weight" class="form-label">Metal Weight</label>
        <input type="number" name="metal_weight" class="form-control" required value="{{ old('metal_weight', $product_raw->metal_weight ?? '') }}">
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <label for="plastic" class="form-label">Plastic Dana</label>
        {{ Form::select('plastic_id', ['' => 'Select Plastic'] + $plastic_list, $product_raw->plastic_id ?? null, ['class' => 'form-select mb-3', 'required']) }}
    </div>
    <div class="col-md-6">
        <label for="plastic_weight" class="form-label">Plastic Weight (gm)</label>
        <input type="number" name="plastic_weight" class="form-control" required  value="{{ old('plastic_weight', $product_raw->plastic_weight ?? '') }}">
    </div>
</div>

<div class="row mt-3 d-flex align-items-center justify-content-center">
    <div class="col-md-6">
        <div class="color-dropdown">
            <label for="color_id" class="form-label">Select Color</label>
            <button class="color-dropdown-btn" type="button" onclick="toggleColorDropdown()" > {{ isset($product_raw->color->name) ? $product_raw->color->name : 'Select Color' }}</button>
            <span id="color-error" class="color-dropdown-error">Please select a color.</span>
            <div class="color-dropdown-content" id="colorDropdownContent">
                @foreach ($color_list as $id => $name)
                    <div class="color-option {{ (isset($product_raw) && $product_raw->color_id == $id) ? 'selected' : '' }} "
                        data-id="{{ $id }}"
                        style="background-color: {{ $name }};"
                        onclick="selectColor('{{ $id }}', '{{ $name }}')"></div>
                @endforeach
            </div>
            <input type="hidden" id="colorInput" name="color_id" required value="{{ old('color_id', $product_raw->color_id ?? '') }}">
        </div>
    </div>

    <div class="col-md-3">
        <label for="color_weight" class="form-label">Color Weight <br> <small>1kg dana = color weight in gm ?</small></label>
        <input type="number" id="color_weight" name="color_weight" class="form-control"  value="{{ old('color_weight', $product_raw->color_weight ?? '') }}" onchange="calculateMaterial()">
    </div>

    <div class="col-md-3 mt-4">
        <label for="color_weight" class="form-label">Cal</small></label>
        <input type="number" class="form-control" name="color_weight_cal" id="color_weight_cal" readonly value="{{ old('color_weight_cal', $product_raw->color_weight_cal ?? '') }}">
    </div>
</div>


<div class="row">
    <div class="mt-4">
        <div class="hstack gap-2 justify-content-end">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success" id="addProductRaw">Save</button>
        </div>
    </div>
</div>
{{ Form::close() }}

<script>
function toggleColorDropdown() {
    const dropdown = document.getElementById('colorDropdownContent');
    dropdown.style.display = dropdown.style.display === 'grid' ? 'none' : 'grid';
}

function selectColor(id, color) {
    document.getElementById('colorInput').value = id;
    document.getElementById('color-error').style.display = 'none';
    document.getElementById('colorDropdownContent').style.display = 'none';

    const button = document.querySelector('.color-dropdown-btn');
    button.innerHTML = `
        <span style="display:inline-block;width:70px;height:25px;background:${color};margin-right:10px;border:1px solid #ccc;color:white;padding:3px;">${color}</span>`;

    document.querySelectorAll('.color-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    document.querySelector(`.color-option[data-id="${id}"]`).classList.add('selected');
}

document.getElementById('create-productdetail').addEventListener('submit', function (event) {
    const colorInput = document.getElementById('colorInput').value;
    if (!colorInput) {
        document.getElementById('color-error').style.display = 'block';
        event.preventDefault();
    }
});

function calculateMaterial() {
    const piecesInput = document.querySelector('input[name="plastic_weight"]');
    const pieceCount = parseInt(piecesInput.value) || 0;

    const colorInput = document.querySelector('input[name="color_weight"]');
    const colorCount = parseInt(colorInput.value) || 0;

    const totalPlasticWeight = (colorCount/1000)* pieceCount;

    document.getElementById('color_weight_cal').value = totalPlasticWeight.toFixed(2);
}
</script>
