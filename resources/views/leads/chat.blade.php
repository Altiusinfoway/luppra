<!-- Lead chat Modal  Start -->
{{ Form::open(['route' => ['leads.chat.save', $lead['id']], 'method' => 'post', 'id' => 'create-lead', 'class' => 'needs-validation']) }}

<style>
    .flatpickr-months .flatpickr-month
    {
        background: white;
    }
</style>
    <div class="row">

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="lead_id" class="form-label">Lead Status</label>
                            {{ Form::select('stage_id', ['' => 'Select Lead Stage'] + $lead_stage_list->toArray(), null, ['class' => 'form-select mb-3','id' => 'stage_id','aria-label' => 'Select Lead Stage','required']) }}
                        </div>

                         <div class="col-md-12 mb-3">
                            <label for="next_date" class="form-label">Next Follow up Date</label>
                           {{ Form::date('next_date', null, ['class' => 'form-control datepicker-range', 'id' => 'datepicker-range','data-provider' => 'flatpickr', 'data-range' => 'true']) }}
                        </div>


                        <div class="col-md-12">
                            <label for="chat" class="form-label">Follow up Communication</label>
                            {{ Form::textarea('chat',null, ['class' => 'form-control', 'id' => 'chat', 'placeholder' => __('Enter Follow up Communication.'), 'required' => 'required']) }}
                        </div>
                    </div>



                </div>
            </div>
        </div>
        <div class="mt-4">
            <div class="hstack gap-2 justify-content-end">
                <button type="button" class="btn btn-light"
                    data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success" id="updateLead">Save
                    </button>
            </div>
        </div>

    </div>
{{ Form::close() }}
<!-- Lead chat Modal  Ends -->
