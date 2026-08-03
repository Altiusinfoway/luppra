@extends('layouts.app')

@section('page-css')
    <style>
        .payments-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .payments-suite .hero-shell,
        .payments-suite .toolbar-shell,
        .payments-suite .table-shell {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
        }

        .payments-suite .hero-eyebrow {
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

        .payments-suite .hero-title {
            font-size: clamp(2rem, 3vw, 2.75rem);
            line-height: 1.04;
            letter-spacing: -0.04em;
            font-weight: 800;
            margin: 1rem 0 .45rem;
            color: #0f172a;
        }

        .payments-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.88);
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
            height: 100%;
        }

        .payments-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .payments-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
        }

        .payments-suite .filter-shell {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 1rem;
        }

        .payments-suite .filter-label {
            display: block;
            margin-bottom: 0.35rem;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .payments-suite .search-shell {
            position: relative;
        }

        .payments-suite .search-shell .search-icon {
            position: absolute;
            top: 50%;
            right: 14px;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
        }

        .payments-suite .segment-tabs {
            display: inline-flex;
            gap: 8px;
            padding: 6px;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            background: #f8fafc;
        }

        .payments-suite .segment-tabs .nav-link {
            border: none;
            border-radius: 999px;
            padding: .55rem 1rem;
            color: #475569;
            font-weight: 700;
        }

        .payments-suite .segment-tabs .nav-link.active {
            background: linear-gradient(135deg, #0f766e 0%, #2563eb 100%);
            color: #fff;
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.18);
        }

        .payments-suite .table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
        }

        .payments-suite .table-wrap table {
            margin-bottom: 0;
        }

        .payments-suite .toolbar-shell .card-body,
        .payments-suite .table-shell .card-body {
            padding: 1.4rem;
        }
    </style>
@endsection

@section('content')
    <div class="page-content payments-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-8">
                                    <span class="hero-eyebrow">Finance Workspace</span>
                                    <h1 class="hero-title">Payments</h1>
                                    <p class="text-muted mb-0">Track all credit and debit activity with the same clean table and filter shell used across the refreshed admin experience.</p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-lg-end align-items-center gap-2 flex-wrap">
                                        @can('create payment')
                                            <a href="javascript:void(0);" class="btn btn-primary" data-size="lg"
                                                data-url="{{ route('payments.create') }}" data-ajax-popup="true"
                                                data-bs-original-title="{{ __('Add Payment') }}"><i
                                                    class="ri-add-line align-bottom me-1"></i> Add Payment</a>
                                        @endcan
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                            <li class="breadcrumb-item active">List</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="summary-card">
                        <div class="card-body">
                            <span class="label">Views</span>
                            <h3>3</h3>
                            <p class="text-muted mb-0 mt-2">All, credit, and debit payment streams in one workspace.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="summary-card">
                        <div class="card-body">
                            <span class="label">Search Ready</span>
                            <h3>Live</h3>
                            <p class="text-muted mb-0 mt-2">Filter by payment id, date, and payment method without reloading.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="summary-card">
                        <div class="card-body">
                            <span class="label">Entry Mode</span>
                            <h3>Quick</h3>
                            <p class="text-muted mb-0 mt-2">Launch payment capture directly from the finance list view.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="summary-card">
                        <div class="card-body">
                            <span class="label">Workspace</span>
                            <h3>Finance</h3>
                            <p class="text-muted mb-0 mt-2">Consistent reporting shell shared with the rest of the admin UI.</p>
                        </div>
                    </div>
                </div>
            </div>


            <div class="card toolbar-shell mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                        <div>
                            <h5 class="card-title mb-1">Payment History</h5>
                            <p class="text-muted mb-0">Search, segment, and add payment records from one cleaner finance shell.</p>
                        </div>
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

                    <div class="filter-shell">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="filter-label" for="flt_search-bar-options">Search</label>
                                <div class="search-shell">
                                    <i class="ri-search-line search-icon"></i>
                                    <input type="text" class="form-control search" id="flt_search-bar-options"
                                        name="flt_search" placeholder="Search payment id, order id, description">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="filter-label" for="flt_dateRange">Payment Dates</label>
                                <input type="text" class="form-control datepicker-range" id="flt_dateRange"
                                    name="flt_dateRange" data-provider="flatpickr" data-date-format="d M, Y"
                                    data-range-date="true" placeholder="Payment Dates">
                            </div>
                            <div class="col-md-4">
                                <label class="filter-label" for="flt_paymentMethod">Payment Method</label>
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
                    </div>
                    <!--end row-->
                </div>
                <!--end card-body-->
            </div>
            <!--end card-->

            <div class="row">
                <div class="col-lg-12">
                    <div class="card table-shell">

                        <div class="card-header">
                            <h5 class="card-title  mb-0">Payments List</h5>
                        </div>

                        <div class="card-body">

                            <ul class="nav nav-tabs segment-tabs mb-3" role="tablist">
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
                                    <div class="table-wrap">
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
                                </div>
                                <!-- end table -->

                                <!-- credit payment -->
                                <div class="tab-pane " id="tb_credit_payment" role="tabpanel">
                                    <div class="table-wrap">
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
                                </div>
                                <!-- end table -->

                                <!-- debit payment -->
                                <div class="tab-pane " id="tb_debit_payment" role="tabpanel">
                                    <div class="table-wrap">
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
