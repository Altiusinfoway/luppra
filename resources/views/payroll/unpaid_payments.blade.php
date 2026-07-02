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
                        <a href="javascript:void(0);" class="btn btn-primary text-right" id="pay_salary">Pay Salaries</a>
                    </div>

                    <div class="card-body">

                        <table class="table table-bordered dt-responsive nowrap table-striped align-middle" id="tbl_unpaid_payments">
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
        var table = $('#tbl_unpaid_payments').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
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
        });

        // Handle row checkbox change to update "Select all" checkbox
        /* $('#tbl_unpaid_payments tbody').on('change', 'input.row-checkbox', function(){
            var rows = table.rows({ 'search': 'applied' }).nodes();
            var allChecked = $('input.row-checkbox:checked', rows).length === $('input.row-checkbox', rows).length;
            $('#checkAll').prop('checked', allChecked);
        }); */

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

    });
</script>
@endsection
