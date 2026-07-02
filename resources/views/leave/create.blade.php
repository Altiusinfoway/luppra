@extends('layouts.app')

@section('content')

<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Leave Section</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('leaves.index') }}">Leave </a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->
            <div class="row justify-content-center">
            <!-- Varying Modal Content -->
            <div class="col-xxl-10 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title  mb-0">Leave Add</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('leaves.store') }}" method="POST" id="leavesFprms">
                            @csrf

                            @if (session('error_msg'))
                                <div class="alert alert-danger" id="error_model">
                                    {{ session('error_msg') }}
                                </div>
                            @endif


                            <div class="row">

                                @if(\Auth::user()->type == 'HRM' || \Auth::user()->type == 'company')
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="employee_id">Select Employee <span class="text-danger">*</span></label>
                                       <select name="employee_id" class="form-control">
                                          <option value="0">Select Employee</option>
                                            @foreach ($emp_list as $elist)
                                                <option value="{{ $elist['id'] }}">{{ $elist['name'] }}</option>
                                            @endforeach

                                       </select>
                                        @if($errors->has('employee_id'))
                                            <div class="error text-danger">{{ $errors->first('employee_id') }}</div>
                                        @endif
                                    </div>
                                </div>
                                @endif

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="leave_type">Leave Type <span class="text-danger">*</span></label>
                                       <select name="leave_type" class="form-control">
                                          <option value="">Select Leave Type</option>
                                          @foreach ($leave_type_list as $id=>$name)
                                               <option value="{{ $id }}">{{ $name }}</option>
                                          @endforeach
                                       </select>
                                        @if($errors->has('leave_type'))
                                            <div class="error text-danger">{{ $errors->first('leave_type') }}</div>
                                        @endif
                                    </div>
                                </div>

                                @if( ! (\Auth::user()->type == 'HRM' || \Auth::user()->type == 'company'))
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="hours_leave">Leave Half / Full Day</label>
                                        <select name="hours_leave" class="form-control">
                                            <option>select half/full day</option>
                                            <option value="1">Half Day</option>
                                            <option value="2">Full Day</option>
                                        </select>

                                        @if($errors->has('hours_leave'))
                                            <div class="error text-danger">{{ $errors->first('hours_leave') }}</div>
                                        @endif
                                    </div>
                                </div>
                                @endif

                            </div>


                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="start_date">Start Date <span class="text-danger">*</span></label>
                                        <input type="date" name="start_date" class="form-control  datepicker-range flatpickr-input active"  id="datepicker-range" data-provider="flatpickr" data-range="true" placeholder="Select Start Date">
                                        @if($errors->has('start_date'))
                                            <div class="error text-danger">{{ $errors->first('start_date') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="end_date">End Date <span class="text-danger">*</span></label>
                                        <input type="date" name="end_date" class="form-control datepicker-range flatpickr-input active"  id="datepicker-range" data-provider="flatpickr" data-range="true" placeholder="Select End Date">
                                        @if($errors->has('end_date'))
                                            <div class="error text-danger">{{ $errors->first('end_date') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                @if( \Auth::user()->type == 'HRM' || \Auth::user()->type == 'company')
                                <div class="col-md-6">
                                    <div class="mb-3">
                                       <label class="form-label" for="hours_leave">Leave Half / Full Day</label>
                                        <select name="hours_leave" class="form-control">
                                            <option>select half/full day</option>
                                            <option value="1">Half Day</option>
                                            <option value="2">Full Day</option>
                                        </select>
                                        @if($errors->has('hours_leave'))
                                            <div class="error text-danger">{{ $errors->first('hours_leave') }}</div>
                                        @endif
                                    </div>
                                </div>
                                @endif

                                @if(\Auth::user()->type == 'HRM' || \Auth::user()->type == 'company')
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                                       <select name="status" class="form-control">
                                          <option value="0">Select Status</option>
                                          <option value="1">Pending</option>
                                          <option value="2">Accept</option>
                                          <option value="3">Reject</option>
                                       </select>
                                        @if($errors->has('status'))
                                            <div class="error text-danger">{{ $errors->first('status') }}</div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="reason">Reason <span class="text-danger">*</span></label>
                                   <textarea name="reason" rows=8 class="form-control"></textarea>
                                    @if($errors->has('reason'))
                                        <div class="error text-danger">{{ $errors->first('reason') }}</div>
                                    @endif
                                </div>
                            </div>

                            @if(\Auth::user()->type == 'HRM' || \Auth::user()->type == 'company')
                            <div>
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="remark">Remark </label>
                                   <textarea name="remark" rows=8 class="form-control"></textarea>
                                    @if($errors->has('remark'))
                                        <div class="error text-danger">{{ $errors->first('remark') }}</div>
                                    @endif
                                </div>
                            </div>
                            @endif


                            <div class="text-center mb-3">
                                <button type="submit" class="btn btn-success w-sm">Submit</button>
                            </div>

                        </form>
                        <!-- end card body -->
                        </div>
                </div>
            </div><!--end col-->
        </div><!--end row-->

    </div>
    <!-- container-fluid -->
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        setTimeout(function () {
            $('#error_model').fadeOut(2000);
        }, 1000);
    });
</script>

@endsection
