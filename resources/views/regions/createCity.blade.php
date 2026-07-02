
{{ Form::open(['route' =>  isset($city) && !is_null(optional($city)->id) ? ['regions.cities.update',$city->id] : 'regions.cities.store', 'method' => 'post',  'id'=>'createCityForm','autocomplete' => 'off']) }}

    <div class="row">

        <div class="col-md-12">
            <label for="is_active" class="form-label">Country Name</label>
            <select class="form-select mb-3 choices-select form-control" id="country_id" name="country_id" required>
                @if($country->isNotEmpty())
                    @foreach ($country as $cntry)

                       <option value="{{$cntry->id}}" {{ isset($selectedCoutry) && $selectedCoutry == $cntry->id ? 'selected' :'' }} >{{$cntry->name}}</option>

                    @endforeach
                @endif

            </select>
            <span class="text-danger" id="error-is_active"></span>
        </div>

        <div class="col-md-12">
            <label for="is_active" class="form-label">State Name</label>
            <select class="form-select mb-3 choices-select-1 form-control" id="state_id" name="state_id" required>
                @if(isset($state) && $state->isNotEmpty())
                    @foreach ($state as $stt)

                       <option value="{{$stt->id}}" {{ isset($city) && optional($city)->state_id == $stt->id ? 'selected' :'' }} >{{$stt->name}}</option>

                    @endforeach
                @endif

            </select>
            <span class="text-danger" id="error-is_active"></span>
        </div>

        <div class="col-md-12">
            <label for="name" class="form-label">City Name</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ isset($city) ? optional($city)->name :'' }}" placeholder="Enter state name" required>
            <span class="text-danger" id="error-name"></span>
        </div>

        <div class="col-md-12">
            <label for="is_active" class="form-label">Status</label>
            <select class="form-select mb-3 choices-select form-control" id="is_active" name="is_active" required>
                <option value="1" {{ isset($city) && optional($city)->is_active == 1 ? 'selected' :'' }} >Active</option>
                <option value="0" {{ isset($city) && optional($city)->is_active == 0 ? 'selected' :'' }} >Inactive</option>
            </select>
            <span class="text-danger" id="error-is_active"></span>
        </div>

        <div class="mt-4">


            <div class="col-md-12 hstack gap-2 justify-content-end">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success">{{ isset($city) ? 'Update' :'Add' }} City</button>
            </div>

        </div>

    </div>
{{ Form::close() }}

<script>
$(document).ready(function () {




    $('#createCityForm').on('submit', function (e) {
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
                $('#createCityForm')[0].reset();

                reloadTable();
                hideCommanModal();

            } else {

                show_toastr('error',res.message);

            }

        });

    });

});

$("#country_id").on("change", function(){

    $id = $(this).val();
    var url = "{{ route('regions.states.list',[':id']) }}";
    getAjax(url.replace(':id',$id), function(res){
        console.log(res);

        var stateOpt = '';

        if(res.state != undefined)
        {
            var stateList = res.state;

            /* const stateOptions = stateList.map(function (state) {
                return { value: state.id, label: state.name };
            });

            stateSelect.setChoices(stateOptions, 'value', 'label', true); */

            $.each(stateList, function(index, value) {
                console.log("Index: " + index + ", Value: " + value.name);

                stateOpt +=`<option value='${value.id}'>${value.name}</option>`;


            });
        }

        //stateSelect

        $("#state_id").html(stateOpt);
    });

});
</script>
