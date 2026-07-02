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
                                <li class="breadcrumb-item"><a href="{{ route('leaves.index') }}">Leave</a></li>
                                <li class="breadcrumb-item active">List</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end page title -->
            <div class="row">

                <!-- Varying Modal Content -->
                <div class="col-lg-12">
                    <div class="card">
                        <h5 class="card-title m-2">Leave List</h5>
                        <div class="card-header d-flex justify-content-between align-items-center">

                            <div class="d-flex align-items-center w-100 justify-content-between">

                                <div class="row w-100">
                                    @if (\Auth::user()->type == 'HRM' || \Auth::user()->type == 'company')
                                        <div class="col p-1">
                                            <select name="employee_name" class="form-control form-control-sm" id="employee_filter">
                                                <option value="">Select Employee</option>
                                                @foreach ($emp_list as $elist)
                                                    <option value="{{ $elist->id }}"
                                                        {{ request('employee_name') == $elist->id ? 'selected' : '' }}>
                                                        {{ $elist->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col p-1">
                                            <select name="role_filter" class="form-control form-control-sm" id="role_filter">
                                                <option value="0">Select Role</option>
                                                @foreach ($role_list as $rlist)
                                                    <option value="{{ $rlist['name'] }}"
                                                        {{ request('role_filter') == $rlist['name'] ? 'selected' : '' }}>
                                                        {{ $rlist['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <div class="col p-1">
                                        <select name="status_filter" class="form-control form-control-sm" id="status_filter">
                                            <option value="">Select Status</option>
                                            <option value="1" {{ request('status_filter') == 1 ? 'selected' : '' }}>
                                                Pending</option>
                                            <option value="2" {{ request('status_filter') == 2 ? 'selected' : '' }}>
                                                Accept</option>
                                            <option value="3" {{ request('status_filter') == 3 ? 'selected' : '' }}>
                                                Reject</option>
                                        </select>
                                    </div>

                                    <div class="col p-1">
                                        <input type="date"
                                            class="form-control form-control-sm datepicker-range flatpickr-input start_date_filter"
                                            id="datepicker-range" data-provider="flatpickr" data-range="true"
                                            name="start_date_filter" value="{{ request('start_date_filter') }}"
                                            placeholder="Select Start Date">
                                    </div>
                                    <div class="col p-1">
                                        <input type="date"
                                            class="form-control form-control-sm datepicker-range flatpickr-input end_date_filter"
                                            id="datepicker-range" data-provider="flatpickr" data-range="true"
                                            name="end_date_filter" value="{{ request('end_date_filter') }}"
                                            placeholder="Select End Date">
                                    </div>
                                    <div class="col p-1">
                                        @can('create leave')
                                            <a href="{{ route('leaves.create') }}" class="btn btn-sm btn-success w-100" id="addproduct-btn">
                                                <i class="ri-add-line align-bottom me-1"></i> Add Leave
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="leaveList"
                                class="table table-sm table-bordered dt-responsive nowrap table-striped align-middle"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th data-ordering="false">Sr No</th>
                                        @if (Auth::user()->type == 'HRM' || Auth::user()->type == 'company')
                                            <th data-ordering="false">Employee Name</th>
                                            <th data-ordering="false">Department Name</th>
                                        @endif
                                        <th data-ordering="false">Start Date</th>
                                        <th data-ordering="false">End Date</th>
                                        <th data-ordering="false">Total Days</th>
                                        <th data-ordering="false">Leave Type</th>
                                        <th data-ordering="false">Leave Half /Full</th>
                                        <th data-ordering="false">Reason</th>
                                        <th data-ordering="false">Status</th>
                                        <th data-ordering="false">Remark</th>
                                        <th style="width: 80px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>

                        </div>
                    </div>
                </div><!--end col-->
            </div><!--end row-->

        </div>
        <!-- container-fluid -->
    </div>
@endsection


@section('scripts')
    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
            var table = $('#leaveList').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('leaves.index') }}",
                    data: function(d) {
                        d.employee_filter = $('#employee_filter').val();
                        d.role_filter = $('#role_filter').val();
                        d.status_filter = $('#status_filter').val();
                        d.start_date_filter = $('.start_date_filter').val();
                        d.end_date_filter = $('.end_date_filter').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    @if (Auth::user()->type == 'HRM' || Auth::user()->type == 'company')
                        {
                            data: 'emp_name',
                            name: 'emp_name'
                        }, {
                            data: 'department_name',
                            name: 'department_name'
                        },
                    @endif {
                        data: 'start_date',
                        name: 'start_date'
                    },
                    {
                        data: 'end_date',
                        name: 'end_date'
                    },
                    {
                        data: 'total_days',
                        name: 'total_days'
                    },
                    {
                        data: 'leave_type_name',
                        name: 'leave_type_name'
                    },
                    {
                        data: 'hours_leave_name',
                        name: 'hours_leave_name'
                    },
                    {
                        data: 'reason',
                        name: 'reason'
                    },
                    {
                        data: 'status_name',
                        name: 'status_name'
                    },
                    {
                        data: 'remark',
                        name: 'remark'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
            $('#employee_filter').on('change', function() {
                table.draw();
            });
            $('#role_filter').on('change', function() {
                table.draw();
            });
            $('#status_filter').on('change', function() {
                table.draw();
            });
            $('.start_date_filter').on('change', function() {
                table.draw();
            });
            $('.end_date_filter').on('change', function() {
                table.draw();
            });
        });
    </script>
@endsection
