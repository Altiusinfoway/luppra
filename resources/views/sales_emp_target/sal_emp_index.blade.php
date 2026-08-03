@extends('layouts.app')

@section('page-css')
<style>
.employee-target-suite {
    background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
}

.employee-target-suite .hero-shell,
.employee-target-suite .shell-card {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
}

.employee-target-suite .hero-shell {
    background:
        radial-gradient(circle at top right, rgba(14, 165, 233, 0.14), transparent 30%),
        radial-gradient(circle at left center, rgba(139, 92, 246, 0.14), transparent 30%),
        linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}

.employee-target-suite .hero-eyebrow {
    display: inline-flex;
    align-items: center;
    padding: 7px 12px;
    border-radius: 999px;
    border: 1px solid #dbeafe;
    background: rgba(255, 255, 255, 0.86);
    color: #1d4ed8;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.employee-target-suite .summary-card {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.86);
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
}

.employee-target-suite .summary-card .label {
    display: block;
    margin-bottom: 8px;
    color: #64748b;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.employee-target-suite .summary-card h3 {
    margin: 0;
    font-size: 1.7rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: #0f172a;
}

.employee-target-suite .toolbar-shell {
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: #f8fafc;
    padding: 14px 16px;
}

.employee-target-suite .search-shell {
    position: relative;
    min-width: min(100%, 300px);
}

.employee-target-suite .search-shell .form-control {
    min-height: 44px;
    padding-left: 2.7rem;
    border-radius: 14px;
    border-color: #cbd5e1;
    background: #fff;
}

.employee-target-suite .search-shell .search-icon {
    position: absolute;
    top: 50%;
    left: 14px;
    transform: translateY(-50%);
    color: #64748b;
    pointer-events: none;
}

.employee-target-suite .table-wrap {
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    overflow: hidden;
    background: #fff;
}

.employee-target-suite .table-wrap thead th {
    background: #f8fafc !important;
}
</style>
@endsection

@section('content')
<div class="page-content employee-target-suite">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="card hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <span class="hero-eyebrow">Employee Performance</span>
                                <h2 class="mt-3 mb-2">Sales Employee Target</h2>
                                <p class="text-muted mb-0">Monitor assigned targets, collections, and performance percentages inside a lighter target-management dashboard.</p>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        @php
                                            if($dynamic_slug == 'current_month')
                                            {
                                               $usr=null;
                                               $slg='all_months';
                                            }
                                            else {
                                                $usr=$user_id;
                                                $slg=$dynamic_slug;
                                            }
                                        @endphp
                                        <li class="breadcrumb-item"><a href="{{ route('sales-employee-targets.index',[$slg,$usr]) }}">Sales Employee Target</a></li>
                                        <li class="breadcrumb-item active">List </li>
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
                        <span class="label">Performance</span>
                        <h3>{{ $dynamic_slug == 'current_month' ? 'Monthly View' : 'History View' }}</h3>
                        <p class="text-muted mb-0 mt-2">Track assigned targets and collected value in the same dashboard rhythm as the refreshed sales and product surfaces.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Monitoring</span>
                        <h3>{{ $exists ? 'Configured' : 'Setup Needed' }}</h3>
                        <p class="text-muted mb-0 mt-2">Keep target status, employee performance, and follow-up actions visible from one compact management screen.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card shell-card">
                    <div class="card-header">
                        <div class="toolbar-shell d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <h5 class="card-title mb-1">Sales Employee Target</h5>
                                <p class="text-muted mb-0">Search salesperson performance, compare target vs collection, and manage assignments from one cleaner control bar.</p>
                            </div>

                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div class="search-shell">
                                    <i class="ri-search-line search-icon"></i>
                                    <input type="text" id="sales-employee-target-search" class="form-control" placeholder="Search salesperson or totals">
                                </div>
                                @if(!$exists)
                                <div>
                                    <a href="{{ route('sales-employee-targets.create') }}" class="btn btn-sm btn-primary" id="addproduct-btn">
                                        <i class="ri-add-line align-bottom me-1"></i> Add Target
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">

                        <div class="table-responsive table-wrap">
                            <table id="SalesTargetList" class="table table-bordered dt-responsive nowrap table-striped align-middle mb-0" style="width:100%">
                                <thead>

                                    <tr>
                                        <th data-ordering="false">Sr No</th>
                                        <th data-ordering="false">Sales Person Name</th>
                                         @if($dynamic_slug == 'current_month')
                                        <th data-ordering="false">Month</th>
                                        @endif

                                        @if($dynamic_slug == 'all_months')
                                        <th data-ordering="false">Total Target Collection</th>
                                        @else
                                        <th data-ordering="false" >Total Target</th>
                                        @endif

                                        <th data-ordering="false">Total Collection</th>
                                         <th data-ordering="false">Performance (%)</th>
                                        <th>Action</th>
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

    var table = $('#SalesTargetList').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('sales-employee-targets.index',[$dynamic_slug,$user_id]) }}",
            data: function (d) {}
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'user_name', name: 'user_name' },
            @if($dynamic_slug == 'current_month')
             { data: 'month_name', name: 'month_name'},
            @endif
            { data: 'total_target', name: 'total_target'},

            { data: 'achieve_amt', name: 'achieve_amt' },
             { data: 'performance_per', name: 'performance_per' },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ]
    });

    $('#sales-employee-target-search').on('keyup change', function () {
        table.search($(this).val()).draw();
    });
});
</script>
@endsection
