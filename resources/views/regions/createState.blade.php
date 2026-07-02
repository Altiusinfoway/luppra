
{{ Form::open(['route' =>  isset($state) && !is_null(optional($state)->id) ? ['regions.states.update',$state->id] : 'regions.states.store', 'method' => 'post',  'id'=>'createStateForm','autocomplete' => 'off']) }}

    <div class="row">

        <div class="col-md-12 mb-1">
            <label for="is_active" class="form-label">Country Name</label>
            <select class="form-select  choices-select form-control" id="country_id" name="country_id" required>
                @if($country->isNotEmpty())
                    @foreach ($country as $cntry)

                       <option value="{{$cntry->id}}" {{ isset($state) && optional($state)->country_id == $cntry->id ? 'selected' :'' }} >{{$cntry->name}}</option>

                    @endforeach
                @endif

            </select>
            <span class="text-danger" id="error-is_active"></span>
        </div>

        <div class="col-md-12 mb-1">
            <label for="name" class="form-label">State Name</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ isset($state) ? optional($state)->name :'' }}" placeholder="Enter state name" required>
            <span class="text-danger" id="error-name"></span>
        </div>

        <div class="col-md-12">
            <label for="is_active" class="form-label">Status</label>
            <select class="form-select mb-3 choices-select form-control" id="is_active" name="is_active" required>
                <option value="1" {{ isset($state) && optional($state)->is_active == 1 ? 'selected' :'' }} >Active</option>
                <option value="0" {{ isset($state) && optional($state)->is_active == 0 ? 'selected' :'' }} >Inactive</option>
            </select>
            <span class="text-danger" id="error-is_active"></span>
        </div>

        <div class="mt-4">


            <div class="col-md-12 hstack gap-2 justify-content-end">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success">{{ isset($state) ? 'Update' :'Add' }} State</button>
            </div>

        </div>

    </div>
{{ Form::close() }}

<script>
$(document).ready(function () {

    $('#createStateForm').on('submit', function (e) {
        e.preventDefault();

        let form = $(this);
        let url = form.attr('action');

        let formArray = form.serializeArray();
        let formDataObj = {};

        formArray.forEach(item => {
            if (formDataObj[item.name]) {
                // If already exists, push into array
                if (!Array.isArray(formDataObj[item.name])) {
                    formDataObj[item.name] = [formDataObj[item.name]];
                }
                formDataObj[item.name].push(item.value);
            } else {
                // First time assign
                formDataObj[item.name] = item.value;
            }
        });


        postAjax(url, formDataObj, function (res) {

            if(res.success == "true"){

                show_toastr('success',res.message);
                $('#createStateForm')[0].reset();

                reloadTable();
                hideCommanModal();

            } else {

                show_toastr('error',res.message);

            }

        });

    });

});
</script>
