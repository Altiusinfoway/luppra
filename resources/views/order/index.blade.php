@extends('layouts.app')
@section('page-css')
<style>
    .workflow-suite {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
    }
    .workflow-suite .hero-shell,
    .workflow-suite .toolbar-shell,
    .workflow-suite .summary-card {
        border: 1px solid rgba(255, 255, 255, 0.78);
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
    }
    .workflow-suite .hero-shell {
        border-radius: 28px;
        background:
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
            radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
            linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
    }
    .workflow-suite .toolbar-shell,
    .workflow-suite .summary-card {
        border-radius: 22px;
    }
    .workflow-suite .hero-eyebrow {
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
    .workflow-suite .hero-title {
        font-size: clamp(2rem, 3vw, 2.7rem);
        line-height: 1.05;
        letter-spacing: -0.04em;
        font-weight: 800;
        margin: 1rem 0 .45rem;
        color: #0f172a;
    }
    .workflow-suite .hero-subtitle,
    .workflow-suite .toolbar-note {
        color: #64748b;
    }
    .workflow-suite .summary-card .label {
        color: #64748b;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: .08em;
        font-weight: 800;
        margin-bottom: .45rem;
    }
    .workflow-suite .summary-card h3 {
        margin: 0;
        font-size: 1.7rem;
        line-height: 1.1;
        letter-spacing: -0.03em;
        font-weight: 800;
        color: #0f172a;
    }
    .workflow-suite .filter-shell {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #f8fafc;
        padding: 1rem;
    }
    .workflow-suite .filter-label {
        display: block;
        margin-bottom: 0.35rem;
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .workflow-suite .search-shell {
        position: relative;
    }
    .workflow-suite .search-shell .search-icon {
        position: absolute;
        top: 50%;
        right: 14px;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
    }
    .workflow-suite .table-wrap {
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        overflow: hidden;
        background: #fff;
    }
    .workflow-suite .table-wrap table {
        margin-bottom: 0;
    }
</style>
@endsection
@section('content')
@php
    $orderCount = isset($orders) ? $orders->count() : 0;
    $orderValue = isset($orders) ? (float) $orders->sum('grand_total') : 0;
    $todayOrderCount = isset($orders) ? $orders->where('date', now()->toDateString())->count() : 0;
    $uniqueCustomers = isset($orders) ? $orders->pluck('customer_id')->filter()->unique()->count() : 0;
@endphp
<div class="page-content workflow-suite">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-8">
                                <span class="hero-eyebrow">Order Operations</span>
                                <h1 class="hero-title">Orders</h1>
                                <p class="hero-subtitle mb-0">Monitor order volume, search order history, and keep fulfillment status visible in the same modern shell as the rest of the app.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end align-items-center gap-2 flex-wrap">
                                    <a href="{{ route('orders.create') }}" class="btn btn-primary">
                                        <i class="ri-add-line align-bottom me-1"></i> Add Order
                                    </a>
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Orders</li>
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
                <div class="summary-card h-100">
                    <div class="card-body">
                        <div class="label">Total Orders</div>
                        <h3>{{ number_format($orderCount) }}</h3>
                        <p class="text-muted mb-0 mt-2">Orders currently available in this workspace.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="summary-card h-100">
                    <div class="card-body">
                        <div class="label">Order Value</div>
                        <h3>Rs. {{ number_format($orderValue, 2) }}</h3>
                        <p class="text-muted mb-0 mt-2">Gross amount across the loaded order set.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="summary-card h-100">
                    <div class="card-body">
                        <div class="label">Orders Today</div>
                        <h3>{{ number_format($todayOrderCount) }}</h3>
                        <p class="text-muted mb-0 mt-2">Orders placed on {{ now()->format('d M Y') }}.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="summary-card h-100">
                    <div class="card-body">
                        <div class="label">Customers</div>
                        <h3>{{ number_format($uniqueCustomers) }}</h3>
                        <p class="text-muted mb-0 mt-2">Unique customers represented in current orders.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card toolbar-shell" id="orderList">
                    <div class="card-header border-0">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                            <div>
                                <h5 class="card-title mb-1">Order History</h5>
                                <p class="toolbar-note mb-0">Search by order, customer, date, or status without leaving the refreshed operations view.</p>
                            </div>
                            <a href="{{ route('orders.create') }}" class="btn btn-sm btn-primary">
                                <i class="ri-add-line align-bottom me-1"></i> Add Order
                            </a>
                        </div>
                    </div>
                    <div class="card-body border border-dashed border-end-0 border-start-0">
                            <div class="filter-shell">
                            <div class="row g-3 align-items-end">

                                <div class="col-md-6 col-xl-4">
                                    <label class="filter-label" for="flt_search-bar-options">Search</label>
                                    <div class="search-shell">
                                        <i class="ri-search-line search-icon"></i>
                                        <input type="text" class="form-control" id="flt_search-bar-options" name="flt_search"
                                            placeholder="Search for Order Number, Customer Name, Amount">
                                    </div>
                                </div>

                                <div class="col-md-6 col-xl-4">
                                    <label class="filter-label" for="flt_dateRange">Order Dates</label>
                                    <input type="text" class="form-control datepicker-range" id="flt_dateRange" name="flt_dateRange" data-provider="flatpickr" data-date-format="d M, Y" data-range-date="true" placeholder="Select Order Dates">
                                </div>

                                {{-- <div class="col">
                                    <select name="flt_paymentMethod" class="form-control" id="flt_paymentMethod" data-choices data-choices-removeItem>
                                        <option value="">Select Payment Status</option>
                                        <option value="paid">Paid</option>
                                        <option value="unpaid">Un-Paid</option>
                                    </select>
                                </div> --}}

                                <div class="col-md-6 col-xl-4">
                                    <label class="filter-label" for="flt_order_statusMethod">Order Status</label>
                                    <select name="flt_order_statusMethod" class="form-control" id="flt_order_statusMethod" data-choices data-choices-removeItem>
                                        <option value="">Select Order Status</option>
                                        @if(count($order_status_list) >0 )
                                        @foreach ($order_status_list as $id=>$name)
                                             <option value="{{ $id}}">{{ $name }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>

                            </div>
                            </div>

                    </div>

                    <div class="card-body">
                        <div class="table-wrap">
                        <table id="paymentsList" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 150px;">Sr No</th>
                                    <th data-ordering="false">Order ID</th>
                                    <th>Customer</th>
                                    <th>Order Date</th>
                                    <th>Amount</th>
                                    {{-- <th>Payment Status</th> --}}
                                    <th>Order Status</th>
                                    <th>Action</th>

                                </tr>
                            </thead>
                            <tbody></tbody>

                        </table>
                        </div>
                    </div>


                </div>

            </div>
            <!--end col-->
        </div>
        <!--end row-->

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

        var table = $('#paymentsList').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            paging: true,
            ajax: {
                url: "{{ route('orders.index') }}",
                data: function (d) {
                    d.search = $('input[name="flt_search"]').val();
                    d.dateRange = $('input[name="flt_dateRange"]').val();
                    d.paymentMethod = $('select[name="flt_paymentMethod"]').val();
                    d.order_statusMethod = $('select[name="flt_order_statusMethod"]').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                },
                { data: 'order_number', name: 'order_number' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'date', name: 'date' },
                { data: 'grand_total', name: 'grand_total' },
                // { data: 'payment_status', name: 'payment_status' },
                { data: 'order_status', name: 'status' },
                { data: 'action',name: 'action',orderable: false,searchable: false}
            ],
        });


        table.on('xhr', function (e, settings, json) {
            var api = table;

            var pageTotal = api
                .column(2, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return parseFloat(b.toString().replace(/[\$,]/g, '')) + a;
                }, 0);

            console.log(pageTotal);

        });

        $('#flt_search-bar-options').on('keyup', function () {
            table.draw();
        });

        $('#flt_dateRange, #flt_paymentMethod,#flt_order_statusMethod').on('change', function () {
            table.draw();
        });

    });
</script>
@endsection
