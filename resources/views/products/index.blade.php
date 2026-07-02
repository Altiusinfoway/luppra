@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Products</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Products</a></li>
                                <li class="breadcrumb-item active">List</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Varying Modal Content -->
                <div class="col-lg-12">
                    <div class="card">

                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title  mb-0">Product List</h5>


                                <div class="d-flex justify-content-end gap-3">
                                    @can('create product & service')
                                        <div>
                                            <a href="javascript:void(0);" class="btn btn-sm btn-success open-product-modal" data-size="lg"
                                                data-url="{{ route('products.create') }}" data-ajax-popup="true"
                                                data-bs-original-title="{{ __('Add Product') }}"><i
                                                    class="ri-add-line align-bottom me-1"></i> Add Product</a>
                                        </div>
                                    @endcan
                                    <div class="dropdown d-inline-block">
                                        <button class="btn btn-sm btn-info  dropdown" type="button"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ri-file-download-line align-bottom "> Import</i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item remove-item-btn"
                                                    href="{{ route('products.upload_excel_product') }}">
                                                    <i class="ri-octagon-fill align-bottom me-2 text-muted"></i> Upload data
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="card-body">


                            <table id="productList"
                                class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                style="width:100%">
                                <thead>

                                    <tr>
                                        <th data-ordering="false" style="width: 50px">Sr No</th>
                                        <th data-ordering="false" style="width: 80px">image</th>
                                        <th data-ordering="false">Name</th>
                                        <th data-ordering="false">SKU Code</th>
                                        <th>MRP</th>
                                        <th>GST</th>
                                        <th>Stock Qty</th>
                                        <th>Status</th>

                                    </tr>

                                </thead>
                                <tbody></tbody>
                            </table>

                        </div>
                    </div>
                </div><!--end col-->
            </div>
        </div>
    </div>

        <div id="stockUpdatePanel"
        style="
        display:none;
        position:fixed;
        left:50%;
        bottom:20px;
        transform:translateX(-50%);
        width:450px;
        z-index:9999;
        background:#fff;
        border:1px solid #ddd;
        border-radius:10px;
        padding:15px;
        box-shadow:0 0 15px rgba(0,0,0,.15);
        ">

        <h5>Stock Updates</h5>

        <p>
            Products:
            <strong id="productCount">0</strong>
        </p>

        <div id="productList" style="
            max-height:200px;
            overflow-y:auto;
         ">
        </div>

        <button class="btn btn-success btn-sm w-100 mt-2" id="saveStockBtn">
            Update All Stocks
        </button>

    </div>
    </div>

    <div id="importStockModal"
        style="
        display:none;
        position:fixed;
        top:50%;
        left:50%;
        transform:translate(-50%,-50%);
        width:500px;
        background:#fff;
        z-index:10000;
        border-radius:10px;
        padding:20px;
        box-shadow:0 0 20px rgba(0,0,0,.2);
     ">

        <h5>Import Product Stock</h5>
        <span class="text-danger"> Note :- id,qty column required in excel..</span><br>
        <a href="{{ asset('excel_product_stock/sample_product_stock.xlsx') }}" class="">
            <i class="ri-download-line"></i>
            Download Sample Format
        </a>

        <input type="file" id="stockExcelFile" class="form-control mt-3" accept=".xlsx,.xls">

        <div class="mt-3">
            <button type="button" class="btn btn-success" id="uploadStockExcel">
                Upload & Update Stock
            </button>

            <button type="button" class="btn btn-secondary" id="closeImportModal">
                Close
            </button>
        </div>

    </div>

    <div id="importStockBackdrop"
        style="
        display:none;
        position:fixed;
        top:0;
        left:0;
        width:100%;
        height:100%;
        background:rgba(0,0,0,.3);
        z-index:9999;
     ">
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

            var table = $('#productList').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('products.index') }}",
                    data: function(d) {
                        d.name = $('#search-task-options').val();
                        d.state_filter = $('#state_filter').val();
                        d.city_filter = $('#city_filter').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'image',
                        name: 'image'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'sku_code',
                        name: 'sku_code',
                        searchable: true
                    },
                    {
                        data: 'price',
                        name: 'price',
                        searchable: true
                    },
                     {
                        data: 'gst_val',
                        name: 'gst_val'
                    },
                    {
                        data: 'qty_val',
                        name: 'qty_val'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                columnDefs: [
                    { targets: [2], className: 'text-wrap text-break' }
                ]
            });

            $('#search-task-options').on('keyup', function() {
                table.draw();
            });

            $('#state_filter').on('change', function() {
                table.draw();
            });

            $('#city_filter').on('change', function() {
                table.draw();
            });
        });
    </script>
    <script>
        let pendingStockUpdates = {};

        $(document).on('change', '.stock-input', function() {

            let productId = $(this).data('product-id');

            let qty = $(this).val();

            pendingStockUpdates[productId] = qty;

            refreshStockPanel();

        });

        function refreshStockPanel() {
            let ids = Object.keys(pendingStockUpdates);

            let count = ids.length;

            if (count == 0) {
                $('#stockUpdatePanel').hide();
                return;
            }

            $('#stockUpdatePanel').show();

            $('#productCount').text(count);

            let html = '';

            /*
                ids.forEach(function(id){

                    html += `
                    <div class="border-bottom py-1">
                        Product ID : ${id}
                        <br>
                        Qty : ${pendingStockUpdates[id]}
                    </div>
                `;

                });
                */

            table.ajax.reload(null, false);
        }

        $('#saveStockBtn').click(function() {

            let products = [];

            Object.keys(pendingStockUpdates).forEach(function(id) {

                products.push({
                    id: id,
                    qty: pendingStockUpdates[id]
                });

            });

            $.ajax({

                url: '{{ route('products.bulk_stock_update') }}',

                type: 'POST',

                data: {
                    _token: '{{ csrf_token() }}',
                    products: products
                },

                success: function(response) {

                    pendingStockUpdates = {};

                    $('#stockUpdatePanel').hide();

                    show_toastr('success', 'Stock Updated Successfully.');
                    table.ajax.reload(null, false);

                }

            });

        });
    </script>
@endsection
