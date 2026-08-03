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

.payroll-suite .filter-shell {
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: #f8fafc;
    padding: 14px 16px;
}

.payroll-suite .filter-label {
    display: block;
    margin-bottom: 0.35rem;
    color: #64748b;
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.payroll-suite .table-wrap {
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    overflow: hidden;
    background: #fff;
}

.payroll-suite .selection-hint {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 999px;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 12px;
    font-weight: 700;
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
                                <h2 class="mt-3 mb-2">Pending Payroll Payments</h2>
                                <p class="text-muted mb-0">Review unpaid salary rows, select batches, and open the payroll payment workflow from a cleaner processing screen.</p>
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
                        <span class="label">Queue</span>
                        <h3>Pending</h3>
                        <p class="text-muted mb-0 mt-2">Process unpaid salary rows from a cleaner batch-payment surface.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Action</span>
                        <h3>Batch Pay</h3>
                        <p class="text-muted mb-0 mt-2">Select one or many records and launch the payment workflow with less visual noise.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Selection</span>
                        <h3 id="selected-payroll-count">0 rows</h3>
                        <p class="text-muted mb-0 mt-2">Track how many salary rows are selected before opening the payment workflow.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Workflow</span>
                        <h3>Review + Pay</h3>
                        <p class="text-muted mb-0 mt-2">Filter pending rows, select the batch, then launch the payment details step from one place.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card shell-card">
                    <div class="toolbar-shell d-flex justify-content-between align-items-center m-3 flex-wrap gap-3">
                        <div>
                            <h5 class="card-title mb-1">Payroll</h5>
                            <p class="text-muted mb-0">Process unpaid salary lines from the same cleaner payroll operations shell used across the refreshed admin area.</p>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="selection-hint" id="selection-hint">0 selected</span>
                            <a href="javascript:void(0);" class="btn btn-primary text-right" id="pay_salary">Pay Salaries</a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="filter-shell mb-3">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6 col-xl-4">
                                    <label class="filter-label" for="payroll-search">Search</label>
                                    <input type="text" class="form-control" id="payroll-search" placeholder="Search employee, month, amount...">
                                </div>
                                <div class="col-md-6 col-xl-3">
                                    <button type="button" class="btn btn-light w-100" id="payroll-reset-filters">Reset</button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive table-wrap">
                        <table class="table table-bordered dt-responsive nowrap table-striped align-middle mb-0" id="tbl_unpaid_payments">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">
                                        <div class="form-check">
                                            <input class="form-check-input row-checkbox" type="checkbox" value="all" id="checkAll">
                                            <label class="form-check-label" for="checkAll"></label>
                                        </div>
                                    </th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Month</th>
                                    <th scope="col">Total</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
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
        function updateSelectedCount() {
            var checkedValues = $('.row-checkbox:checked').not('[value="all"]').length;
            $('#selected-payroll-count').text(checkedValues + ' rows');
            $('#selection-hint').text(checkedValues + ' selected');
        }

        // sales emp
        var table = $('#tbl_unpaid_payments').DataTable({
            processing: true,
            serverSide: true,
            searching: true,
            paging: true,
            ajax: {
                url: "{{ route('payrolls.process') }}",
                data: function (d) {
                }
            },
            columns: [
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false},
                { data: 'name', name: 'name' },
                { data: 'payment_date', name: 'payment_date' },
                { data: 'amount_raw', name: 'amount_raw' },
                { data: 'payment_status', name: 'payment_status' },
                {data:'action', name:'action', orderable: false, searchable: false}
            ]
        });

        // Handle click on "Select all" control
        $('#checkAll').on('click', function(){
            var rows = table.rows({ 'search': 'applied' }).nodes();
            $('input[type="checkbox"].row-checkbox', rows).prop('checked', this.checked);
            updateSelectedCount();
        });

        $('#tbl_unpaid_payments tbody').on('change', 'input.row-checkbox', function(){
            var rows = table.rows({ 'search': 'applied' }).nodes();
            var total = $('input.row-checkbox', rows).not('[value="all"]').length;
            var selected = $('input.row-checkbox:checked', rows).not('[value="all"]').length;
            $('#checkAll').prop('checked', total > 0 && total === selected);
            updateSelectedCount();
        });

        $('#payroll-search').on('keyup change', function () {
            table.search($(this).val()).draw();
        });

        $('#payroll-reset-filters').on('click', function () {
            $('#payroll-search').val('');
            $('#checkAll').prop('checked', false);
            table.search('').draw();
            updateSelectedCount();
        });

        table.on('draw', function () {
            $('#checkAll').prop('checked', false);
            updateSelectedCount();
        });

        $("#pay_salary").on("click",function(){

            var checkedValues = [];
            $('.row-checkbox:checked').each(function() {
                checkedValues.push($(this).val());
            });

            if(checkedValues.length > 0) {

                // Show payment modal here..
                var selected = '';
                if(checkedValues.includes('all')){
                    selected = 'all';
                } else {
                    selected = checkedValues.join(',');
                }

                var url = "{{ route('payrolls.pay',':selected') }}";
                url = url.replace(':selected', selected);

                let link = $('<a>', {
                    href: 'javascript:void(0);',
                    'data-size':"xl",
                    'data-url':url,
                    'data-ajax-popup':"true",
                    'data-bs-original-title':"{{__('Payroll Payment Details')}}",
                }).appendTo('body');

                link[0].click();
                link.remove();


            } else {
                show_toastr('error',"Please select at least one row.");
            }
        });

        updateSelectedCount();

    });
</script>
@endsection
