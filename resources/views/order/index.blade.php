@extends('layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Orders</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Orders</a></li>
                            <li class="breadcrumb-item active">List</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card" id="orderList">
                    <div class="card-header border-0">
                        <div class="row align-items-center gy-3">
                            <div class="col-sm">
                                <h5 class="card-title mb-0">Order History</h5>
                            </div>
                            <div class="col-sm-auto">
                                <div class="d-flex gap-1 flex-wrap">
                                    {{-- <button type="button" class="btn btn-info"><i class="ri-file-download-line align-bottom me-1"></i> Import</button> --}}
                                    <button class="btn btn-soft-danger" id="remove-actions" onclick="deleteMultiple()" style="display: none;"><i class="ri-delete-bin-2-line"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body border border-dashed border-end-0 border-start-0">

                            <div class="row g-3 align-items-end">

                                <div class="col">
                                    <div class="position-relative">
                                        <i class="ri-search-line search-icon position-absolute top-50 end-0 translate-middle-y me-3"></i>
                                        <input type="text" class="form-control" id="flt_search-bar-options" name="flt_search"
                                            placeholder="Search for Order Number, Customer Name, Amount">
                                    </div>
                                </div>

                                <div class="col">
                                    <input type="text" class="form-control datepicker-range" id="flt_dateRange" name="flt_dateRange" data-provider="flatpickr" data-date-format="d M, Y" data-range-date="true" placeholder="Select Order Dates">
                                </div>

                                {{-- <div class="col">
                                    <select name="flt_paymentMethod" class="form-control" id="flt_paymentMethod" data-choices data-choices-removeItem>
                                        <option value="">Select Payment Status</option>
                                        <option value="paid">Paid</option>
                                        <option value="unpaid">Un-Paid</option>
                                    </select>
                                </div> --}}

                                <div class="col">
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

                    <div class="card-body">
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
