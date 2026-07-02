{{ Form::model($category, [
    'route' => ['category.update', $category->id],
    'method' => 'PUT',
    'id' => 'edit-category',
    'class' => 'needs-validation',
    'novalidate',
]) }}

<div class="row">

    {{-- Category Name --}}
    <div class="col-md-12 mt-1">
        <label class="form-label">Category Name</label><x-required />
        {{ Form::text('name', null, [
            'class' => 'form-control',
            'required',
        ]) }}
    </div>

    {{-- Parent Category --}}
    <div class="col-md-12 mt-3">
        <label class="form-label">Parent Category</label>
        {{ Form::select('parent_id', ['' => 'None'] + $categories, $category->parent_id, ['class' => 'form-select']) }}
    </div>

    {{-- Buttons --}}
    <div class="mt-4">
        <div class="hstack gap-2 justify-content-end">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                Close
            </button>
            <button type="submit" class="btn btn-success">
                Update Category
            </button>
        </div>
    </div>

</div>

{{ Form::close() }}
