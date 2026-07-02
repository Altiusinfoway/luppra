
{{ Form::open(['route' =>  isset($country) && !is_null(optional($country)->id) ? ['regions.countries.update',$country->id] : 'regions.countries.store', 'method' => 'post',  'id'=>'createCountryForm','autocomplete' => 'off']) }}

    <div class="row">
        <div class="col-md-12">
            <label for="name" class="form-label">Country Name</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ isset($country) ? optional($country)->name :'' }}" placeholder="Enter country name" required>
            <span class="text-danger" id="error-name"></span>
        </div>
        <div class="col-md-12">
            <label for="code" class="form-label">Country Code</label>
            <input type="text" id="code" name="code" class="form-control" value="{{ isset($country) ? optional($country)->code :'' }}"  placeholder="e.g., US" required>
            <span class="text-danger" id="error-code"></span>
        </div>

        <div class="col-md-12">
            <label for="is_active" class="form-label">Status</label>
            <select class="form-select mb-3 choices-select form-control" id="is_active" name="is_active" required>
                <option value="1" {{ isset($country) && optional($country)->is_active == 1 ? 'selected' :'' }} >Active</option>
                <option value="0" {{ isset($country) && optional($country)->is_active == 0 ? 'selected' :'' }} >Inactive</option>
            </select>
            <span class="text-danger" id="error-is_active"></span>
        </div>

        <div class="mt-4">


            <div class="col-md-12 hstack gap-2 justify-content-end">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success">{{ isset($country) ? 'Update' :'Add' }} Country</button>
            </div>

        </div>

    </div>
{{ Form::close() }}

<script>
$(document).ready(function () {

    $('#createCountryForm').on('submit', function (e) {
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

                console.log("fgfggf fg fg");

                show_toastr('success',res.message);
                $('#createCountryForm')[0].reset();
                reloadTable();
                hideCommanModal();

            } else {

                show_toastr('error',res.message);

            }

        });

    });

});
</script>
