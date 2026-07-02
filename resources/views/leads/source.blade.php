<!-- Lead Product Modal  Start -->
{{ Form::model($lead, array('route' => array('leads.sources.save', $lead->id), 'method' => 'POST', 'id'=>'update-lead',  'class'=>'needs-validation', 'novalidate')) }}
    <div class="row">
        
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                <div class="col-md-12">

                    <label for="sources" class="form-label">Sources </label>

                    {{ Form::select('sources[]', $source->toArray(), explode(',',$lead->sources) , [
                        'class' => 'form-select mb-3 choices-select',
                        'id' => 'sources',
                        'aria-label' => 'Select Sources', 'required',
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
