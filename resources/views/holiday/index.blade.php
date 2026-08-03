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
                                <span class="hero-eyebrow">Calendar Setup</span>
                                <h1 class="mb-3">Holidays</h1>
                                <p class="text-muted mb-0">Manage holiday periods in the same streamlined scheduling workspace as the rest of the HR tools.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('holidays.index') }}">Holiday</a></li>
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
                        <span class="label">Calendar</span>
                        <h3>Holidays</h3>
                        <p class="text-muted mb-0 mt-2">Keep business closure periods and observances easy to review from one clean list.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Range</span>
                        <h3>Date Based</h3>
                        <p class="text-muted mb-0 mt-2">Track start and end windows so employee scheduling stays predictable.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">View</span>
                        <h3>Searchable</h3>
                        <p class="text-muted mb-0 mt-2">Find holiday records faster from the same structured HR setup shell.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Planning</span>
                        <h3>Calendar Ready</h3>
                        <p class="text-muted mb-0 mt-2">Keep time-off planning and holiday windows visually grouped in one workspace.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card table-shell">
                    <div class="card-header">
                        <div class="toolbar-shell d-flex justify-content-between align-items-start flex-wrap gap-3">
                            <div>
                                <h5 class="card-title mb-1">Holiday List</h5>
                                <p class="text-muted mb-0">Manage closure periods and observances from the same polished HR calendar shell.</p>
                            </div>
                            @can('create holiday')
                            <a href="{{ route('holidays.create') }}" class="btn btn-sm btn-primary" id="addproduct-btn">
                                <i class="ri-add-line align-bottom me-1"></i> Add Holiday
                            </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="filter-shell mb-3">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6 col-xl-4">
                                    <label class="filter-label" for="holiday-search">Search</label>
                                    <input type="text" class="form-control" id="holiday-search" placeholder="Search holiday name...">
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive table-wrap">
                        <table id="holidayList" class="table table-bordered dt-responsive nowrap table-striped align-middle mb-0" style="width:100%">
                            <thead>
                                <tr>
                                    <th data-ordering="false" style="width: 50px">Sr No</th>
                                    <th data-ordering="false">Name</th>
                                    <th data-ordering="false" style="width: 150px">Start Date</th>
                                    <th style="width: 150px">End Date</th>
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
$(document).ready(function ()
{

    var table = $('#holidayList').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('holidays.index') }}",
            data: function (d) {
            }
        },
        columns: [
            {  data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false },
            { data: 'name', name: 'name' },
            { data: 'start_date', name: 'start_date' },
            { data: 'end_date', name: 'end_date' },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ]
    });

    $('#holiday-search').on('keyup change', function () {
        table.search($(this).val()).draw();
    });
});
</script>
@endsection
