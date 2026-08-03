@extends('layouts.app')

@section('page-css')
<style>
.target-suite {
    background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
}

.target-suite .hero-shell,
.target-suite .shell-card {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
}

.target-suite .hero-shell {
    background:
        radial-gradient(circle at top right, rgba(139, 92, 246, 0.16), transparent 30%),
        radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
        linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}

.target-suite .hero-eyebrow {
    display: inline-flex;
    align-items: center;
    padding: 7px 12px;
    border-radius: 999px;
    border: 1px solid #ddd6fe;
    background: rgba(255, 255, 255, 0.86);
    color: #6d28d9;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.target-suite .summary-card {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.86);
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
}

.target-suite .summary-card .label {
    display: block;
    margin-bottom: 8px;
    color: #64748b;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.target-suite .summary-card h3 {
    margin: 0;
    font-size: 1.7rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: #0f172a;
}

.target-suite .toolbar-shell {
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: #f8fafc;
    padding: 14px 16px;
}

.target-suite .search-shell {
    position: relative;
    min-width: min(100%, 300px);
}

.target-suite .search-shell .form-control {
    min-height: 44px;
    padding-left: 2.7rem;
    border-radius: 14px;
    border-color: #cbd5e1;
    background: #fff;
}

.target-suite .search-shell .search-icon {
    position: absolute;
    top: 50%;
    left: 14px;
    transform: translateY(-50%);
    color: #64748b;
    pointer-events: none;
}

.target-suite .table-wrap {
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    overflow: hidden;
    background: #fff;
}

.target-suite .table-wrap thead th {
    background: #f8fafc !important;
}
</style>
@endsection

@section('content')
<div class="page-content target-suite">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <span class="hero-eyebrow">Performance Rules</span>
                                <h2 class="mt-3 mb-2">Sales Target</h2>
                                <p class="text-muted mb-0">Manage target thresholds and incentive rules from a lighter policy dashboard aligned with the refreshed design system.</p>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('sales-targets.index') }}">Sales Target</a></li>
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
                        <span class="label">Rules</span>
                        <h3>Target Slabs</h3>
                        <p class="text-muted mb-0 mt-2">Keep incentive thresholds and sales-rule definitions visible in the same crisp dashboard language as the products module.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Incentives</span>
                        <h3>Policy Setup</h3>
                        <p class="text-muted mb-0 mt-2">Review sales bonus rules quickly before assigning targets across the wider employee performance flow.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card shell-card">
                    <div class="card-header">
                        <div class="toolbar-shell d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <h5 class="card-title mb-1">Sales Target List</h5>
                                <p class="text-muted mb-0">Search performance policies and manage target rules from one compact setup surface.</p>
                            </div>
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div class="search-shell">
                                    <i class="ri-search-line search-icon"></i>
                                    <input type="text" id="sales-target-search" class="form-control" placeholder="Search target or rule">
                                </div>
                                @can('create sales target')
                                <a href="{{ route('sales-targets.create') }}" class="btn btn-sm btn-primary" id="addproduct-btn">
                                    <i class="ri-add-line align-bottom me-1"></i> Add Sales Target
                                </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table-wrap">
                            <table id="saleTargetList" class="table table-bordered dt-responsive nowrap table-striped align-middle mb-0" style="width:100%">
                                <thead>
                                    <tr>
                                        <th data-ordering="false">Sr No</th>
                                        <th data-ordering="false">Target</th>
                                        <th data-ordering="false">Incentive Rule</th>
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

    var table = $('#saleTargetList').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('sales-targets.index') }}",
            data: function (d) {
            }
        },
        columns: [
            {  data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false },
            { data: 'target_value', name: 'target_value' },
            { data: 'incentive_rule', name: 'incentive_rule' },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ]
    });

    $('#sales-target-search').on('keyup change', function () {
        table.search($(this).val()).draw();
    });
});
</script>
@endsection
