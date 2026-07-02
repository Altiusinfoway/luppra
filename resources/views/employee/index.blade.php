@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Employee </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employee </a></li>
                                <li class="breadcrumb-item active">List</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end page title -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div id="success-message" class="alert alert-success d-none"></div>
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <form class="app-search d-none d-md-block">
                                        <div class="position-relative">
                                            <input type="text" class="form-control rounded-pill "
                                                placeholder="Search Name..." autocomplete="off" id="search-task-options">
                                            <span class="mdi mdi-magnify search-widget-icon"></span>
                                            <span
                                                class="mdi mdi-close-circle search-widget-icon search-widget-icon-close d-none"
                                                id="search-close-options"></span>
                                        </div>
                                    </form>
                                </div>
                                <div class="col text-end">
                                    @can('create employee')
                                        <a href="{{ route('users.create', ['type' => 'employee']) }}"
                                            class="btn btn-sm btn-primary " id="addproduct-btn"><i
                                                class="ri-add-line align-bottom me-1"></i> Add New Employee</a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="EmpList"
                                class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                style="width:100%">
                                <thead>

                                    <tr>
                                        <th data-ordering="false">Sr No</th>
                                        <th data-ordering="false">Employee Name</th>
                                        <th data-ordering="false">Role</th>
                                        <th>Employee Email</th>
                                        <th>Date Of Birth</th>
                                        <th>Action</th>
                                    </tr>

                                </thead>
                                <tbody></tbody>
                            </table>

                        </div>
                    </div>
                </div>





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
            var table = $('#EmpList').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('employees.index') }}",
                    data: function(d) {
                        d.name = $('#search-task-options').val();
                    }
                },
                columns: [{
                       data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'role',
                        name: 'role'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'dob',
                        name: 'dob'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#search-task-options').on('keyup', function() {
                table.draw();
            });
        });
    </script>
@endsection
