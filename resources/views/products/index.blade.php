@extends('layouts.app')

@section('page-css')
    <style>
        .products-dashboard {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .products-dashboard .hero-panel {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 28px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.16), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
                linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
        }

        .products-dashboard .eyebrow {
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

        .products-dashboard .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.84);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .products-dashboard .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .products-dashboard .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .products-dashboard h1 {
            font-size: clamp(2rem, 3vw, 2.85rem);
            font-weight: 800;
            letter-spacing: -0.04em;
        }

        .products-dashboard .toolbar-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 22px;
        }

        .products-dashboard .filter-shell {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 1rem;
        }

        .products-dashboard .filter-label {
            display: block;
            margin-bottom: 0.35rem;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .products-dashboard #productStaticTable tbody tr.product-list-row,
        .products-dashboard #productList tbody tr.product-list-row {
            position: relative;
            transition: background-color .18s ease, box-shadow .18s ease;
        }

        .products-dashboard #productStaticTable tbody tr.product-list-row > td,
        .products-dashboard #productList tbody tr.product-list-row > td {
            position: relative;
            background: #ffffff !important;
            border-top-color: #e2e8f0 !important;
            border-bottom-color: #e2e8f0 !important;
            transition: background-color .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease;
        }

        .products-dashboard #productStaticTable.table-striped > tbody > tr.product-list-row:nth-of-type(odd) > *,
        .products-dashboard #productList.table-striped > tbody > tr.product-list-row:nth-of-type(odd) > * {
            background: #f8fbff !important;
        }

        .products-dashboard #productStaticTable tbody tr.product-list-row > td:nth-child(3),
        .products-dashboard #productList tbody tr.product-list-row > td:nth-child(3) {
            font-weight: 700;
            color: #0f172a;
        }

        .products-dashboard #productStaticTable tbody tr.product-list-row > td:first-child,
        .products-dashboard #productList tbody tr.product-list-row > td:first-child {
            border-left: 5px solid #93c5fd !important;
            padding-left: 1.15rem !important;
        }

        .products-dashboard #productStaticTable tbody tr.product-list-row:hover,
        .products-dashboard #productList tbody tr.product-list-row:hover,
        .products-dashboard #productList tbody tr.product-list-row:focus-within {
            box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.12);
        }

        .products-dashboard #productStaticTable tbody tr.product-list-row:hover > td,
        .products-dashboard #productList tbody tr.product-list-row:hover > td,
        .products-dashboard #productList tbody tr.product-list-row:focus-within > td {
            background: #f8fbff !important;
            color: #0f172a !important;
            border-top-color: #dbeafe !important;
            border-bottom-color: #dbeafe !important;
        }

        .products-dashboard #productStaticTable tbody tr.product-list-row:hover > td:first-child,
        .products-dashboard #productList tbody tr.product-list-row:hover > td:first-child,
        .products-dashboard #productList tbody tr.product-list-row:focus-within > td:first-child {
            border-left-color: #1d4ed8 !important;
        }

        .products-dashboard #productStaticTable tbody tr.product-list-row:hover .badge,
        .products-dashboard #productList tbody tr.product-list-row:hover .badge,
        .products-dashboard #productList tbody tr.product-list-row:focus-within .badge {
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.12);
        }

        .products-dashboard #productStaticTable tbody tr.product-list-row:hover > td:nth-child(3),
        .products-dashboard #productStaticTable tbody tr.product-list-row:hover > td:nth-child(4),
        .products-dashboard #productList tbody tr.product-list-row:hover > td:nth-child(3),
        .products-dashboard #productList tbody tr.product-list-row:hover > td:nth-child(4),
        .products-dashboard #productList tbody tr.product-list-row:focus-within > td:nth-child(3),
        .products-dashboard #productList tbody tr.product-list-row:focus-within > td:nth-child(4) {
            color: #1d4ed8 !important;
        }

        .products-dashboard #productStaticTable tbody tr.product-list-row:hover .stock-input,
        .products-dashboard #productList tbody tr.product-list-row:hover .stock-input,
        .products-dashboard #productList tbody tr.product-list-row:focus-within .stock-input {
            box-shadow: 0 0 0 3px rgba(191, 219, 254, 0.8);
        }

        .products-dashboard #productStaticTable tbody tr.product-list-row .flex-shrink-0,
        .products-dashboard #productList tbody tr.product-list-row .flex-shrink-0 {
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
            border: 1px solid transparent;
        }

        .products-dashboard #productStaticTable tbody tr.product-list-row:hover .flex-shrink-0,
        .products-dashboard #productList tbody tr.product-list-row:hover .flex-shrink-0,
        .products-dashboard #productList tbody tr.product-list-row:focus-within .flex-shrink-0 {
            transform: scale(1.02);
            border-color: #60a5fa;
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.12);
        }

        .product-list-row .stock-input {
            position: relative;
            z-index: 2;
        }

        .product-list-row .product-action-cell {
            width: 68px;
            text-align: center;
        }

        .product-list-row .product-action-toggle {
            width: 38px;
            height: 38px;
            border-radius: 12px !important;
            border: 1px solid rgba(203, 213, 225, 0.9) !important;
            background: rgba(255, 255, 255, 0.94) !important;
            color: #475569 !important;
            padding: 0 !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-content products-dashboard">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card hero-panel mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-7">
                                    <span class="eyebrow">Catalog Dashboard</span>
                                    <h1 class="mt-3 mb-2">Products</h1>
                                    <p class="text-muted mb-0 fs-15">
                                        Manage your master SKUs, inspect marketplace listing coverage, and jump into each product's marketplace workspace from one clean catalog view.
                                    </p>
                                </div>
                                <div class="col-lg-5">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                                            <li class="breadcrumb-item active">Products</li>
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
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Master Products</span>
                            <h3>{{ number_format($productSummary['total_products'] ?? 0) }}</h3>
                            <p class="text-muted mb-0 mt-2">Internal products available across your catalog.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Marketplace Listings</span>
                            <h3>{{ number_format($productSummary['total_listings'] ?? 0) }}</h3>
                            <p class="text-muted mb-0 mt-2">Amazon and Flipkart child listings linked to master SKUs.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Total Stock</span>
                            <h3>{{ number_format((float) ($productSummary['total_stock'] ?? 0)) }}</h3>
                            <p class="text-muted mb-0 mt-2">Master-stock inventory still remains the source of truth.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Average Price</span>
                            <h3>Rs. {{ number_format((float) ($productSummary['average_price'] ?? 0), 2) }}</h3>
                            <p class="text-muted mb-0 mt-2">Quick pricing pulse across the current catalog.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card toolbar-card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between flex-wrap gap-3 align-items-start">
                                <div>
                                    <h5 class="card-title mb-1">Product List</h5>
                                    <p class="text-muted mb-0">Use the action menu to open the master product's marketplace view.</p>
                                </div>
                                <div class="d-flex justify-content-end gap-3 flex-wrap">
                                    @can('create product & service')
                                        <div>
                                            <a href="javascript:void(0);" class="btn btn-sm btn-primary open-product-modal" data-size="lg"
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
                            <div class="filter-shell mb-3">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-6 col-xl-4">
                                        <label class="filter-label" for="product-search">Search Products</label>
                                        <input type="text" class="form-control" id="product-search"
                                            placeholder="Search by name, SKU, price, or GST...">
                                    </div>
                                </div>
                            </div>
                            <table id="productStaticTable"
                                class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                style="width:100%">
                                <thead>

                                    <tr>
                                        <th data-ordering="false" style="width: 50px">Sr No</th>
                                        <th data-ordering="false" style="width: 80px">image</th>
                                        <th data-ordering="false">Name</th>
                                        <th data-ordering="false">SKU Code</th>
                                        <th data-ordering="false">Marketplace Listings</th>
                                        <th>MRP</th>
                                        <th>GST</th>
                                        <th>Stock Qty</th>
                                        <th data-ordering="false" style="width: 70px;">Actions</th>
                                    </tr>

                                </thead>
                                <tbody>
                                    @foreach($products as $index => $product)
                                        <tr class="main-row product-list-row">
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="flex-shrink-0 bg-light rounded p-1" style="width: 50px; height: 50px;">
                                                    <img src="{{ $product->image }}" alt="Products" style="width: 100%; height: 100%; object-fit: cover">
                                                </div>
                                            </td>
                                            <td>{{ $product->name }}</td>
                                            <td>{{ $product->sku_code }}</td>
                                            <td>
                                                @if(empty($marketplaceEnabled))
                                                    <span class="badge bg-light text-muted">Disabled</span>
                                                @elseif(($product->marketplace_listings_count ?? 0) === 0)
                                                    <a href="{{ route('products.marketplace', $product->id) }}" class="badge bg-light text-muted text-decoration-none">No listings</a>
                                                @else
                                                    <a href="{{ route('products.marketplace', $product->id) }}" class="badge bg-primary-subtle text-primary text-decoration-none">
                                                        {{ (int) $product->marketplace_listings_count }} listings
                                                    </a>
                                                @endif
                                            </td>
                                            <td>{{ number_format((float) $product->price, 2) }}</td>
                                            <td>{{ $product?->getGstSlabMaster?->rate ?? 0 }}</td>
                                            <td>
                                                <input
                                                    type="number"
                                                    class="form-control stock-input"
                                                    data-product-id="{{ $product->id }}"
                                                    value="{{ $product->stock_qty ?? 0 }}"
                                                    min="0"
                                                    style="width:120px;"
                                                >
                                            </td>
                                            <td class="product-action-cell">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm product-action-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Product actions">
                                                        <i class="ri-more-2-fill"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('products.marketplace', $product->id) }}">
                                                                <i class="ri-eye-line align-bottom me-2 text-muted"></i> View Product
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('products.activity', $product->id) }}">
                                                                <i class="ri-history-line align-bottom me-2 text-muted"></i> Product Activity
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
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

        <div id="pendingProductList" style="
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

@section('page-script')
    <script>
        let pendingStockUpdates = {};

        function neutralizeLegacyProductDataTable() {
            if (!window.jQuery || !$.fn || !$.fn.DataTable) {
                return;
            }

            $.fn.dataTable.ext.errMode = 'none';

            const $productTable = $('#productStaticTable');

            $productTable.off('error.dt.legacyGuard').on('error.dt.legacyGuard', function (e, settings, techNote, message) {
                console.warn('Legacy product DataTable prevented:', message);
                e.preventDefault();
                return false;
            });

            if ($.fn.DataTable.isDataTable($productTable)) {
                $productTable.DataTable().destroy();
            }
        }

        $(function () {
            neutralizeLegacyProductDataTable();
            setTimeout(neutralizeLegacyProductDataTable, 250);
            setTimeout(neutralizeLegacyProductDataTable, 1000);

            $('#product-search').on('keyup change', function () {
                const keyword = $(this).val().toString().trim().toLowerCase();

                $('#productStaticTable tbody tr.product-list-row').each(function () {
                    const rowText = $(this).text().toLowerCase();
                    const matches = keyword === '' || rowText.indexOf(keyword) !== -1;
                    $(this).toggle(matches);
                });
            });
        });

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
                }

            });

        });
    </script>
@endsection
