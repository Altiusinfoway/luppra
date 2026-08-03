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
            background: rgba(255, 255, 255, 0.86);
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
            padding: 14px 16px;
        }

        .hr-suite .search-shell {
            position: relative;
            min-width: min(100%, 300px);
        }

        .hr-suite .search-shell .form-control {
            min-height: 44px;
            padding-left: 2.7rem;
            border-radius: 14px;
            border-color: #cbd5e1;
            background: #fff;
        }

        .hr-suite .search-shell .search-icon {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: #64748b;
            pointer-events: none;
        }

        .hr-suite .table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
        }

        .hr-suite .table-wrap thead th {
            background: #f8fafc !important;
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
                                <span class="hero-eyebrow">Schedule Setup</span>
                                <h1 class="mb-3">Working Hours</h1>
                                <p class="text-muted mb-0">Manage weekly operating hours from the same cleaner scheduling shell as the rest of the HR/admin area.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('working-hours.index') }}">Working Hours</a></li>
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
                        <span class="label">Schedule Setup</span>
                        <h3>Weekly Hours</h3>
                        <p class="text-muted mb-0 mt-2">Define standard business timing in the same cleaner dashboard language used across the refreshed admin area.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Coverage</span>
                        <h3>Day Slots</h3>
                        <p class="text-muted mb-0 mt-2">Review open-close windows quickly before updating staffing and attendance settings elsewhere.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card table-shell">
                    <div class="card-header">
                        <div class="toolbar-shell d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <h5 class="card-title mb-1">Working Hours List</h5>
                                <p class="text-muted mb-0">Search by day or time range and keep schedule maintenance in one compact setup panel.</p>
                            </div>
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div class="search-shell">
                                    <i class="ri-search-line search-icon"></i>
                                    <input type="text" id="working-hours-search" class="form-control" placeholder="Search day or timing">
                                </div>
                                @can('create working hours')
                                <a href="{{ route('working-hours.create') }}" class="btn btn-sm btn-primary" id="addproduct-btn">
                                    <i class="ri-add-line align-bottom me-1"></i> Add Working Hours
                                </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table-wrap">
                            <table id="WorkingList" class="table table-bordered dt-responsive nowrap table-striped align-middle mb-0" style="width:100%">
                                <thead>
                                    <tr>
                                        <th data-ordering="false">Sr No</th>
                                        <th data-ordering="false">Day</th>
                                        <th data-ordering="false">Start Time</th>
                                        <th>End Time</th>
                                        @can('edit working hours')
                                        <th style="width: 80px;">Action</th>
                                        @endcan
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
$(document).ready(function ()
{

    var table = $('#WorkingList').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('working-hours.index') }}",
            data: function (d) {
            }
        },
        columns: [
            {  data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false },
            { data: 'day_name', name: 'day_name' },
            { data: 'start_time', name: 'start_time' },
            { data: 'end_time', name: 'end_time' },
            @can('edit working hours')
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
            @endcan
        ]
    });

    $('#working-hours-search').on('keyup change', function () {
        table.search($(this).val()).draw();
    });
});
</script>
@endsection
