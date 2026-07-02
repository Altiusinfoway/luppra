@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Payments</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Payments</a></li>
                                <li class="breadcrumb-item active">List</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>


            <div class="card">
                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6 d-flex gap-2">

                            <div class="col-md-4 search-box ">
                                <input type="text" class="form-control search" id="flt_search-bar-options"
                                    name="flt_search" placeholder="Search for payment ID, order ID...">
                                <i class="ri-search-line search-icon"></i>
                            </div>

                            <div class="col-md-4">
                                <input type="text" class="form-control datepicker-range" id="flt_dateRange"
                                    name="flt_dateRange" data-provider="flatpickr" data-date-format="d M, Y"
                                    data-range-date="true" placeholder="Payment Dates">
                            </div>
                            <div class="col-md-4">
                                <select name="flt_paymentMethod" class="form-control" id="flt_paymentMethod" data-choices
                                    data-choices-removeItem>
                                    <option value="">Payment Method</option>
                                    @if ($paymentMethods)
                                        @foreach ($paymentMethods as $key => $val)
                                            <option value="{{ $key }}">{{ $val }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 d-flex justify-content-end">
                            <div class="">
                                <div class="hstack gap-2">

                                    @can('create payment')
                                        <div>
                                            <a href="javascript:void(0);" class="btn btn-success" data-size="lg"
                                                data-url="{{ route('payments.create') }}" data-ajax-popup="true"
                                                data-bs-original-title="{{ __('Add Payment') }}"><i
                                                    class="ri-add-line align-bottom me-1"></i> Add Payment</a>
                                        </div>
                                    @endcan

                                </div>

                            </div>
                        </div>

                    </div>
                    <!--end row-->
                </div>
                <!--end card-body-->
            </div>
            <!--end card-->

            <div class="row">
                <!-- Varying Modal Content -->
                <div class="col-lg-12">
                    <div class="card">

                        <div class="card-header">
                            <h5 class="card-title  mb-0">Payments List</h5>
                        </div>

                        <div class="card-body">

                            <ul class="nav nav-tabs mb-3" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#tb_all_payment" role="tab"
                                        aria-selected="true">
                                        All
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tb_credit_payment" role="tab"
                                        aria-selected="false">
                                        Credit
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tb_debit_payment" role="tab"
                                        aria-selected="false">
                                        Debit
                                    </a>
                                </li>
                            </ul>


                            <div class="tab-content  text-muted">

                                <div class="tab-pane active" id="tb_all_payment" role="tabpanel">
                                    <table id="tbl_all"
                                        class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">Sr No</th>
                                                <th>Date</th>
                                                <th>Transaction ID</th>
                                                <th>Description</th>
                                                <th>Payment Method</th>
                                                <th>Debit Amount</th>
                                                <th>Credit Amount</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="6"></th>
                                                <th style="text-align:right">Page Total / Grand Total:</th>
                                                <th id="footer-total"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <!-- end table -->

                                <!-- credit payment -->
                                <div class="tab-pane " id="tb_credit_payment" role="tabpanel">
                                    <table id="tbl_credit"
                                        class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th style="width: 150px;">Sr No</th>
                                                <th>Amount</th>
                                                <th>Date</th>
                                                <th>Transaction ID</th>
                                                <th>Payment Method</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr>
                                                <th style="text-align:right">Page Total / Grand Total:</th>
                                                <th id="footer-total"></th>
                                                <th colspan="4"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <!-- end table -->

                                <!-- debit payment -->
                                <div class="tab-pane " id="tb_debit_payment" role="tabpanel">
                                    <table id="tbl_debit"
                                        class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th style="width: 150px;">Sr No</th>
                                                <th>Amount</th>
                                                <th>Date</th>
                                                <th>Transaction ID</th>
                                                <th>Payment Method</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr>
                                                <th style="text-align:right">Page Total / Grand Total:</th>
                                                <th id="footer-total"></th>
                                                <th colspan="4"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <!-- end table -->

                            </div>


                        </div>
                    </div>
                </div><!--end col-->
            </div>
        </div>
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
            // all payment
            var table = $('#tbl_all').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                paging: true,
                ajax: {
                    url: "{{ route('payments.index') }}",
                    data: function(d) {
                        d.search = $('input[name="flt_search"]').val();
                        d.dateRange = $('input[name="flt_dateRange"]').val();
                        d.paymentMethod = $('select[name="flt_paymentMethod"]').val();
                    }
                },
                columns: [{
                       data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'paymentDate',
                        name: 'paymentDate'
                    },
                    {
                        data: 'transaction_id',
                        name: 'transaction_id'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'payment_method',
                        name: 'payment_method'
                    },
                    {
                        data: 'paymentsDebit',
                        name: 'paymentsDebit'
                    },
                    {
                        data: 'paymentsCredit',
                        name: 'paymentsCredit'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },

                ],
                footerCallback: function(row, data, start, end, display) {
                    var api = this.api();

                    var json = api.ajax.json();
                    var grandTotal = json.grand_total;

                    var parseValue = function(value) {
                        return typeof value === 'string' ?
                            parseFloat(value.replace(/[\$,]/g, '')) || 0 :
                            typeof value === 'number' ?
                            value :
                            0;
                    };

                    /*  // Total over all pages
                     var grandTotal = api
                         .column(1)
                         .data()
                         .reduce(function (a, b) {
                             return parseValue(a) + parseValue(b);
                         }, 0); */

                    // Total over this page
                    var pageTotal = api
                        .column(1, {
                            page: 'current'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return parseValue(a) + parseValue(b);
                        }, 0);

                    // Update footer
                    $(api.column(7).footer()).html(
                        pageTotal.toLocaleString() + ' / ' + grandTotal.toLocaleString()
                    );
                }
            });


            $('#flt_search-bar-options').on('keyup', function() {
                table.draw();
            });

            $('#flt_dateRange, #flt_paymentMethod').on('change', function() {
                table.draw();
            });


            // -----------------------------------------------------

            // credit payment
            var table2 = $('#tbl_credit').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                paging: true,
                ajax: {
                    url: "{{ route('payments.payment_credit') }}",
                    data: function(d) {
                        d.search = $('input[name="flt_search"]').val();
                        d.dateRange = $('input[name="flt_dateRange"]').val();
                        d.paymentMethod = $('select[name="flt_paymentMethod"]').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'paymentDate',
                        name: 'paymentDate'
                    },
                    {
                        data: 'transaction_id',
                        name: 'transaction_id'
                    },
                    {
                        data: 'payment_method',
                        name: 'payment_method'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    }

                ],
                footerCallback: function(row, data, start, end, display) {
                    var api = this.api();

                    var json = api.ajax.json();
                    var grandTotal = json.grand_total;

                    var parseValue = function(value) {
                        return typeof value === 'string' ?
                            parseFloat(value.replace(/[\$,]/g, '')) || 0 :
                            typeof value === 'number' ?
                            value :
                            0;
                    };

                    // Total over all pages
                    /* var grandTotal = api
                        .column(1)
                        .data()
                        .reduce(function (a, b) {
                            return parseValue(a) + parseValue(b);
                        }, 0); */

                    // Total over this page
                    var pageTotal = api
                        .column(1, {
                            page: 'current'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return parseValue(a) + parseValue(b);
                        }, 0);

                    // Update footer
                    $(api.column(1).footer()).html(
                        pageTotal.toLocaleString() + ' / ' + grandTotal.toLocaleString()
                    );
                }
            });

            $('#flt_search-bar-options').on('keyup', function() {
                table2.draw();
            });

            $('#flt_dateRange, #flt_paymentMethod').on('change', function() {
                table2.draw();
            });


            // -----------------------------------------------------

            // debit payment
            var table3 = $('#tbl_debit').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                paging: true,
                ajax: {
                    url: "{{ route('payments.payment_debit') }}",
                    data: function(d) {
                        d.search = $('input[name="flt_search"]').val();
                        d.dateRange = $('input[name="flt_dateRange"]').val();
                        d.paymentMethod = $('select[name="flt_paymentMethod"]').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'paymentDate',
                        name: 'paymentDate'
                    },
                    {
                        data: 'transaction_id',
                        name: 'transaction_id'
                    },
                    {
                        data: 'payment_method',
                        name: 'payment_method'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    }

                ],
                footerCallback: function(row, data, start, end, display) {
                    var api = this.api();

                    var json = api.ajax.json();
                    var grandTotal = json.grand_total;

                    var parseValue = function(value) {
                        return typeof value === 'string' ?
                            parseFloat(value.replace(/[\$,]/g, '')) || 0 :
                            typeof value === 'number' ?
                            value :
                            0;
                    };

                    // Total over all pages
                    /* var grandTotal = api
                        .column(1)
                        .data()
                        .reduce(function (a, b) {
                            return parseValue(a) + parseValue(b);
                        }, 0); */

                    // Total over this page
                    var pageTotal = api
                        .column(1, {
                            page: 'current'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return parseValue(a) + parseValue(b);
                        }, 0);

                    // Update footer
                    $(api.column(1).footer()).html(
                        pageTotal.toLocaleString() + ' / ' + grandTotal.toLocaleString()
                    );
                }
            });

            $('#flt_search-bar-options').on('keyup', function() {
                table3.draw();
            });

            $('#flt_dateRange, #flt_paymentMethod').on('change', function() {
                table3.draw();
            });

            window.reloadPaymentsTables = function() {
                table.ajax.reload(null, false);
                table2.ajax.reload(null, false);
                table3.ajax.reload(null, false);
            };



        });
    </script>
@endsection
