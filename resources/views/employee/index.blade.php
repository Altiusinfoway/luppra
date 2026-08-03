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

        .hr-suite .table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
        }

        .hr-suite .status-banner {
            border: 1px solid #bbf7d0;
            border-radius: 18px;
            padding: 0.95rem 1rem;
            margin: 1rem 1rem 0;
            background: linear-gradient(180deg, #ecfdf3 0%, #f7fffb 100%);
            color: #067647;
            box-shadow: 0 12px 26px rgba(16, 185, 129, 0.08);
        }

        .hr-suite .status-banner .banner-label {
            display: block;
            margin-bottom: 0.3rem;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            opacity: .82;
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
                                    <span class="hero-eyebrow">Workforce Directory</span>
                                    <h1 class="mb-3">Employees</h1>
                                    <p class="text-muted mb-0">Search employees, review roles, and manage the workforce list from the same clean admin shell used across the refreshed project.</p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
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
                            <span class="label">Directory</span>
                            <h3>Employees</h3>
                            <p class="text-muted mb-0 mt-2">Manage the workforce directory with the same clean dashboard rhythm used across the product UI.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Focus</span>
                            <h3>Searchable</h3>
                            <p class="text-muted mb-0 mt-2">Keep role, email, and birthday details easier to scan from a more refined list surface.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card table-shell">
                        <div id="success-message" class="status-banner d-none">
                            <span class="banner-label">Directory update</span>
                        </div>
                        <div class="card-header">
                            <div class="row align-items-center toolbar-shell g-3">
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
                            <div class="table-responsive table-wrap">
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
