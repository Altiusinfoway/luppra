@extends('layouts.app')

@section('page-css')
    <style>
        .invoice-index-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .invoice-index-suite .hero-shell,
        .invoice-index-suite .toolbar-shell,
        .invoice-index-suite .table-shell {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
        }

        .invoice-index-suite .hero-shell {
            overflow: hidden;
        }

        .invoice-index-suite .hero-eyebrow {
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

        .invoice-index-suite .hero-title {
            font-size: clamp(2rem, 3vw, 2.75rem);
            line-height: 1.04;
            letter-spacing: -0.04em;
            font-weight: 800;
            margin: 1rem 0 .45rem;
            color: #0f172a;
        }

        .invoice-index-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.84);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .invoice-index-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .invoice-index-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
        }

        .invoice-index-suite .filter-shell {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 1rem;
        }

        .invoice-index-suite .filter-label {
            display: block;
            margin-bottom: 0.35rem;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .invoice-index-suite .search-shell {
            position: relative;
        }

        .invoice-index-suite .search-shell .search-icon {
            position: absolute;
            top: 50%;
            right: 14px;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
        }

        .invoice-index-suite .table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
        }

        .invoice-index-suite .table-wrap table {
            margin-bottom: 0;
        }

        .invoice-index-suite .table-shell .card-body,
        .invoice-index-suite .toolbar-shell .card-body {
            padding: 1.4rem;
        }
    </style>
@endsection

@section('content')
    <div class="page-content invoice-index-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-8">
                                    <span class="hero-eyebrow">Billing Workspace</span>
                                    <h1 class="hero-title">Invoices</h1>
                                    <p class="text-muted mb-0">
                                        Search, filter, and manage invoice history from the same calm dashboard shell used across the refreshed catalog and sales flows.
                                    </p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                            <li class="breadcrumb-item active">Invoices</li>
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
                            <span class="label">Billing</span>
                            <h3>Invoices</h3>
                            <p class="text-muted mb-0 mt-2">Track invoice activity in one cleaner finance workspace.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Status</span>
                            <h3>Paid + Unpaid</h3>
                            <p class="text-muted mb-0 mt-2">Filter finance history by payment state without leaving the page.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card toolbar-shell mb-4" id="orderList">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                                <div>
                                    <h5 class="card-title mb-1">Invoice History</h5>
                                    <p class="text-muted mb-0">Keep finance operations searchable and easy to scan.</p>
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <button class="btn btn-soft-danger" id="remove-actions" onclick="deleteMultiple()"
                                        style="display: none;"><i class="ri-delete-bin-2-line"></i></button>
                                    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                                        <i class="ri-file-add-line me-1"></i> Add Invoice
                                    </a>
                                </div>
                            </div>
                            <div class="filter-shell">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <label class="filter-label" for="flt_search-bar-options">Search</label>
                                        <div class="search-shell">
                                            <i class="ri-search-line search-icon"></i>
                                            <input type="text" class="form-control" id="flt_search-bar-options"
                                                name="flt_search"
                                                placeholder="Search invoice number, customer, amount">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="filter-label" for="flt_dateRange">Invoice Dates</label>
                                        <input type="text" class="form-control datepicker-range" id="flt_dateRange"
                                            name="flt_dateRange" data-provider="flatpickr" data-date-format="d M, Y"
                                            data-range-date="true" placeholder="Select Invoice Dates">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="filter-label" for="flt_paymentMethod">Payment Status</label>
                                        <select name="flt_paymentMethod" class="form-control" id="flt_paymentMethod"
                                            data-choices data-choices-removeItem>
                                            <option value="">Select Payment Status</option>
                                            <option value="paid">Paid</option>
                                            <option value="unpaid">Un-Paid</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-muted small">Use filters to narrow invoice history without leaving the page.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card table-shell">
                        <div class="card-body">
                        <div class="table-wrap">
                        <table id="paymentsList"
                            class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 150px;">Sr No</th>
                                    <th data-ordering="false">Invoice ID</th>
                                    <th>Customer</th>
                                    <th>Invoice Date</th>
                                    <th>Amount</th>
                                    <th>Payment Status</th>
                                    {{-- <th>Order Status</th> --}}
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

            var table = $('#paymentsList').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                paging: true,
                ajax: {
                    url: "{{ route('invoices.index') }}",
                    data: function(d) {
                        d.search = $('input[name="flt_search"]').val();
                        d.dateRange = $('input[name="flt_dateRange"]').val();
                        d.paymentMethod = $('select[name="flt_paymentMethod"]').val();
                        d.order_statusMethod = $('select[name="flt_order_statusMethod"]').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'order_number',
                        name: 'order_number'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'grand_total',
                        name: 'grand_total'
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    // { data: 'order_status', name: 'status' },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
            });


            table.on('xhr', function(e, settings, json) {
                var api = table;

                var pageTotal = api
                    .column(2, {
                        page: 'current'
                    })
                    .data()
                    .reduce(function(a, b) {
                        return parseFloat(b.toString().replace(/[\$,]/g, '')) + a;
                    }, 0);

                console.log(pageTotal);

            });

            $('#flt_search-bar-options').on('keyup', function() {
                table.draw();
            });

            $('#flt_dateRange, #flt_paymentMethod,#flt_order_statusMethod').on('change', function() {
                table.draw();
            });

        });
    </script>
@endsection
