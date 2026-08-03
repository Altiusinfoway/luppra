@extends('layouts.app')

@section('page-css')
    <style>
        .hr-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .hr-suite .hero-shell,
        .hr-suite .table-shell {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
        }

        .hr-suite .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.76);
            border: 1px solid #dbeafe;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .hr-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.84);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .hr-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .hr-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
        }

        .hr-suite .toolbar-shell {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 14px;
        }

        .hr-suite .filter-shell {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 1rem;
        }

        .hr-suite .filter-label {
            display: block;
            margin-bottom: 0.35rem;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .hr-suite .table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
        }

        .hr-suite .table-wrap table {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
    <div class="page-content hr-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-8">
                                    <span class="hero-eyebrow">Time Off Management</span>
                                    <h1 class="mb-3">Leaves</h1>
                                    <p class="text-muted mb-0">Review leave requests, apply filters, and manage approval flow inside the same modern workforce shell.</p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('leaves.index') }}">Leave</a></li>
                                            <li class="breadcrumb-item active">List</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Requests</span>
                            <h3>Leaves</h3>
                            <p class="text-muted mb-0 mt-2">Track employee time-off requests from one cleaner approval workspace.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Workflow</span>
                            <h3>Filtered</h3>
                            <p class="text-muted mb-0 mt-2">Refine requests by employee, role, date, and approval status without leaving the list.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card table-shell">
                        <div class="card-header">
                            <div class="toolbar-shell mb-3 d-flex justify-content-between align-items-start flex-wrap gap-3">
                                <div>
                                    <h5 class="card-title mb-1">Leave List</h5>
                                    <p class="text-muted mb-0">Manage approvals and review leave history from the same polished HR shell used across the refreshed admin area.</p>
                                </div>
                                @can('create leave')
                                    <a href="{{ route('leaves.create') }}" class="btn btn-sm btn-primary" id="addproduct-btn">
                                        <i class="ri-add-line align-bottom me-1"></i> Add Leave
                                    </a>
                                @endcan
                            </div>

                            <div class="d-flex align-items-center w-100 justify-content-between">

                                <div class="filter-shell w-100">
                                <div class="row w-100 g-3 align-items-end">
                                    @if (\Auth::user()->type == 'HRM' || \Auth::user()->type == 'company')
                                        <div class="col p-1">
                                            <label class="filter-label" for="employee_filter">Employee</label>
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
                                            <label class="filter-label" for="role_filter">Role</label>
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
                                        <label class="filter-label" for="status_filter">Status</label>
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
                                        <label class="filter-label" for="leave_start_date">Start Date</label>
                                        <input type="date"
                                            class="form-control form-control-sm datepicker-range flatpickr-input start_date_filter"
                                            id="leave_start_date" data-provider="flatpickr" data-range="true"
                                            name="start_date_filter" value="{{ request('start_date_filter') }}"
                                            placeholder="Select Start Date">
                                    </div>
                                    <div class="col p-1">
                                        <label class="filter-label" for="leave_end_date">End Date</label>
                                        <input type="date"
                                            class="form-control form-control-sm datepicker-range flatpickr-input end_date_filter"
                                            id="leave_end_date" data-provider="flatpickr" data-range="true"
                                            name="end_date_filter" value="{{ request('end_date_filter') }}"
                                            placeholder="Select End Date">
                                    </div>
                                    <div class="col p-1">
                                        <button type="button" class="btn btn-sm btn-light w-100" id="leave-reset-filters">
                                            Reset
                                        </button>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive table-wrap">
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
            $('#leave-reset-filters').on('click', function() {
                $('#employee_filter').val('');
                $('#role_filter').val('0');
                $('#status_filter').val('');
                $('.start_date_filter').val('');
                $('.end_date_filter').val('');
                table.draw();
            });
        });
    </script>
@endsection
