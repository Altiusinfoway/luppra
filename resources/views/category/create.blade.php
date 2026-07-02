<!-- Category Modal Start -->
{{ Form::open([
    'route' => 'category.store',
    'method' => 'post',
    'id' => 'create-category',
    'class' => 'needs-validation',
    'novalidate',
    'autocomplete' => 'off',
]) }}

<div class="row">

    {{-- Category Name --}}
    <div class="col-md-12 mt-1">
        <label for="name" class="form-label">Category Name</label><x-required />
        {{ Form::text('name', null, [
            'class' => 'form-control',
            'id' => 'name',
            'placeholder' => __('Enter category name'),
            'required',
        ]) }}
    </div>

    {{-- Parent Category --}}
    <div class="col-md-12 mt-3">
        <label for="parent_id" class="form-label">Parent Category</label>
        {{ Form::select('parent_id', ['' => 'None'] + $categories, null, [
            'class' => 'form-select',
            'id' => 'parent_id',
        ]) }}
    </div>

    {{-- Buttons --}}
    <div class="mt-4">
        <div class="hstack gap-2 justify-content-end">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                Close
            </button>
            <button type="submit" class="btn btn-success">
                Add Category
            </button>
        </div>
    </div>

</div>

{{ Form::close() }}
<!-- Category Modal End -->
