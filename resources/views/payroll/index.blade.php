@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">PayRoll</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('payrolls.index') }}">PayRoll</a></li>
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
                  <div class="d-flex justify-content-between align-items-center m-2">
                    <h5 class="card-title mb-0">PayRoll</h5>

                    <div class="d-flex justify-content-end gap-4">
                        <a href="{{ route('payrolls.cal_all_emp_sal') }}" class="btn btn-primary text-right">Generate All Employee Salary</a>
                        <a href="{{ route('payrolls.process') }}" class="btn btn-primary text-right">Pay Salaries</a>
                    </div>
                    </div>


                    <div class="card-body">

                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs mb-3" role="tablist">
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
                                <table id="tbl_sales_employee" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
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
                            <div class="tab-pane" id="tb_purchase_order_sent" role="tabpanel">
                                <table id="tbl_other_employee" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
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
        $('#tbl_other_employee').DataTable({
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

    });
</script>
@endsection
