@extends('layouts.app')

@section('page-css')
<style>
.payroll-suite {
    background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
}

.payroll-suite .hero-shell,
.payroll-suite .shell-card {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
}

.payroll-suite .hero-shell {
    background:
        radial-gradient(circle at top right, rgba(16, 185, 129, 0.14), transparent 30%),
        radial-gradient(circle at left center, rgba(59, 130, 246, 0.14), transparent 30%),
        linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}

.payroll-suite .hero-eyebrow {
    display: inline-flex;
    align-items: center;
    padding: 7px 12px;
    border-radius: 999px;
    border: 1px solid #d1fae5;
    background: rgba(255, 255, 255, 0.86);
    color: #047857;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.payroll-suite .summary-card {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.86);
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
}

.payroll-suite .summary-card .label {
    display: block;
    margin-bottom: 8px;
    color: #64748b;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
}

.payroll-suite .summary-card h3 {
    margin: 0;
    font-size: 1.7rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: #0f172a;
}

.payroll-suite .toolbar-shell {
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: #f8fafc;
    padding: 14px 16px;
}

.payroll-suite .search-shell {
    position: relative;
    min-width: min(100%, 300px);
}

.payroll-suite .search-shell .form-control {
    min-height: 44px;
    padding-left: 2.7rem;
    border-radius: 14px;
    border-color: #cbd5e1;
    background: #fff;
}

.payroll-suite .search-shell .search-icon {
    position: absolute;
    top: 50%;
    left: 14px;
    transform: translateY(-50%);
    color: #64748b;
    pointer-events: none;
}

.payroll-suite .tab-shell {
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: #f8fafc;
    padding: 8px;
}

.payroll-suite .tab-shell .nav-link {
    border: 0;
    border-radius: 14px;
    color: #475569;
    font-weight: 700;
    padding: 10px 16px;
}

.payroll-suite .tab-shell .nav-link.active {
    background: #ffffff;
    color: #0f172a;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
}

.payroll-suite .table-wrap {
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    overflow: hidden;
    background: #fff;
}
</style>
@endsection

@section('content')
<div class="page-content payroll-suite">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="card hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <span class="hero-eyebrow">Compensation Center</span>
                                <h2 class="mt-3 mb-2">Payroll</h2>
                                <p class="text-muted mb-0">Generate salaries, process payouts, and review sales bonus calculations from a cleaner payroll workspace.</p>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('payrolls.index') }}">Payroll</a></li>
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
                        <span class="label">Payroll</span>
                        <h3>Compensation</h3>
                        <p class="text-muted mb-0 mt-2">Handle salary generation and payout actions in the same polished dashboard language as catalog screens.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Split</span>
                        <h3>Sales + Staff</h3>
                        <p class="text-muted mb-0 mt-2">Separate incentive-driven employees from other staff with a cleaner tabbed reporting surface.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Operations</span>
                        <h3>Salary Runs</h3>
                        <p class="text-muted mb-0 mt-2">Keep bulk salary generation and payout actions visible at the top of the payroll workspace.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card shell-card">
                  <div class="toolbar-shell d-flex justify-content-between align-items-center m-3 flex-wrap gap-3">
                    <div>
                        <h5 class="card-title mb-1">Payroll</h5>
                        <p class="text-muted mb-0">Review compensation, search employee payroll rows, and process salary actions from one compact control bar.</p>
                    </div>

                    <div class="d-flex justify-content-end gap-3 flex-wrap align-items-center">
                        <div class="search-shell">
                            <i class="ri-search-line search-icon"></i>
                            <input type="text" id="payroll-search" class="form-control" placeholder="Search employee payroll">
                        </div>
                        <a href="{{ route('payrolls.cal_all_emp_sal') }}" class="btn btn-primary text-right">Generate All Employee Salary</a>
                        <a href="{{ route('payrolls.process') }}" class="btn btn-primary text-right">Pay Salaries</a>
                    </div>
                  </div>


                    <div class="card-body">

                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs mb-3 tab-shell" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#tb_purchase_request" role="tab" aria-selected="false">
                                   Sales
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tb_purchase_order_sent" role="tab" aria-selected="false">
                                    Other Employee
                                </a>
                            </li>

                        </ul>

                        <!-- Tab panes -->
                        <div class="tab-content  text-muted">
                            <div class="tab-pane active" id="tb_purchase_request" role="tabpanel">
                                <div class="table-responsive table-wrap">
                                <table id="tbl_sales_employee" class="table table-bordered dt-responsive nowrap table-striped align-middle mb-0" style="width:100%">
                                <thead>
                                <tr>
                                    <th data-ordering="false">Sr No</th>
                                    <th>Name</th>
                                    <th>Salary</th>
                                    <th>Sales Target</th>
                                    <th>Incentive Rule</th>
                                    <th>Target Achieve</th>
                                    <th>Sales Bonus</th>
                                    <th>Received Salary</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                                </table>
                                </div>
                            </div>
                            <div class="tab-pane" id="tb_purchase_order_sent" role="tabpanel">
                                <div class="table-responsive table-wrap">
                                <table id="tbl_other_employee" class="table table-bordered dt-responsive nowrap table-striped align-middle mb-0" style="width:100%">
                                <thead>
                                <tr>
                                    <th data-ordering="false">Sr No</th>
                                    <th>Name</th>
                                    <th>Salary</th>
                                    <th>Received Salary</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                                </table>
                                </div>
                            </div>

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
        // sales emp
        var table = $('#tbl_sales_employee').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            paging: true,
            ajax: {
                url: "{{ route('payrolls.index') }}",
                data: function (d) {
                }
            },
            columns: [
                {  data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false },
                { data: 'name', name: 'name' },
                { data: 'salary', name: 'salary' },
                { data: 'sales_target', name: 'sales_target' },
                { data: 'get_incentive', name: 'get_incentive' },
                { data: 'target_achieve', name: 'target_achieve' },
                { data: 'sales_bonus', name: 'sales_bonus'},
                { data: 'received_sal', name: 'received_sal' },
                {data:'action', name:'action'},
            ]
        });

        // other emp
        var otherEmployeeTable = $('#tbl_other_employee').DataTable({
            processing: true,
            serverSide: true,
             searching: false,
            ajax: {
                url: '{{ route("payrolls.other_emp_payroll") }}',
                data: function (d) {
                }
            },
            columns: [
                {  data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false },
                { data: 'name', name: 'name' },
                { data: 'salary', name: 'salary' },
                { data: 'received_sal', name: 'received_sal' },
                {data:'action', name:'action'},
            ]
        });

        $('#payroll-search').on('keyup change', function () {
            var keyword = $(this).val();
            table.search(keyword).draw();
            otherEmployeeTable.search(keyword).draw();
        });

    });
</script>
@endsection
