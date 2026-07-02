<!-- Lead Product Modal  Start -->
{{ Form::model($lead, array('route' => array('leads.users.save', $lead->id), 'method' => 'POST', 'id'=>'update-lead',  'class'=>'needs-validation', 'novalidate')) }}
        <div class="row">
            
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                    <div class="col-md-12">

                        <label for="users" class="form-label">Users </label>

                        {{ Form::select('users[]', $users->toArray(), $lead->users->pluck('id') , [
                            'class' => 'form-select mb-3 choices-select',
                            'id' => 'users',
                            'aria-label' => 'Select Users', 'required',
                            'data-choices',
                            'data-choices-removeItem',
                            'multiple'
                        ]) }}

                    </div>

                    </div>
                </div>
            </div>
            <div class="mt-4">
                <div class="hstack gap-2 justify-content-end">
                    <button type="button" class="btn btn-light"
                        data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" id="updateLead">Save
                        User</button>
                </div>
            </div>
        </div>
    {{ Form::close() }}    
<!-- Lead Product Modal  Ends -->
