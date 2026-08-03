@extends('layouts.app')

@section('page-css')
<style>
.spanko-suite {
    background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
}

.spanko-suite .hero-shell,
.spanko-suite .shell-card {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
}

.spanko-suite .hero-shell {
    background:
        radial-gradient(circle at top right, rgba(139, 92, 246, 0.16), transparent 30%),
        radial-gradient(circle at left center, rgba(59, 130, 246, 0.12), transparent 30%),
        linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}

.spanko-suite .hero-eyebrow {
    display: inline-flex;
    align-items: center;
    padding: 7px 12px;
    border-radius: 999px;
    border: 1px solid #ddd6fe;
    background: rgba(255, 255, 255, 0.86);
    color: #6d28d9;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.spanko-suite .summary-card {
    border: 1px solid rgba(255, 255, 255, 0.78);
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.84);
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
}

.spanko-suite .summary-card .label {
    display: block;
    margin-bottom: 8px;
    color: #64748b;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
}

.spanko-suite .summary-card h3 {
    margin: 0;
    font-size: 1.7rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: #0f172a;
}

.spanko-suite .toolbar-shell {
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: #f8fafc;
    padding: 14px 16px;
    margin: 0 1rem;
}

.spanko-suite .filter-shell {
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: #ffffff;
    padding: 16px;
}

.spanko-suite .filter-label {
    display: block;
    margin-bottom: 8px;
    color: #64748b;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
}

.spanko-suite .table-wrap {
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    overflow: hidden;
    background: #fff;
}

.spanko-suite .table-wrap thead th {
    background: #f8fafc !important;
}
</style>
@endsection

@section('content')
<div class="page-content spanko-suite">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="card hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <span class="hero-eyebrow">Call Intelligence</span>
                                <h2 class="mt-3 mb-2">Spanko Section</h2>
                                <p class="text-muted mb-0">Filter connected and not-connected calling performance with the same lighter dashboard treatment used across analytics screens.</p>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('spanko.index') }}">Spanko</a></li>
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
                        <span class="label">Call Analytics</span>
                        <h3>Connected vs Missed</h3>
                        <p class="text-muted mb-0 mt-2">Review outreach quality in the same KPI-first dashboard pattern used across the refreshed product and sales screens.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Filters</span>
                        <h3>Product + Date</h3>
                        <p class="text-muted mb-0 mt-2">Keep product, status, and date filters grouped into one cleaner overview surface for call-performance analysis.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card shell-card">
                    <div class="card-header">
                        <div class="toolbar-shell d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <h5 class="card-title mb-1">Spanko Overview</h5>
                                <p class="text-muted mb-0">Filter calling performance and compare connected outcomes from one compact analytics control bar.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="filter-shell mb-3">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label for="product_filter" class="filter-label">Product</label>
                                    <select name="product_filter" class="form-control" id="product_filter">
                                        <option value="0">Select Product</option>
                                        @foreach($product_list as $id => $name)
                                            <option value="{{ $id }}" {{ $id == request('product_filter') ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label for="sale_filter" class="filter-label">Designation</label>
                                    <select name="sale_filter" class="form-control" id="sale_filter">
                                        <option value="0">Select Designation</option>
                                        <option value="sale" {{ request('sale_filter') == 'sale' ? 'selected' : '' }}>Sale</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label for="status_filter" class="filter-label">Status</label>
                                    <select name="status_filter" class="form-control" id="status_filter">
                                        <option value="">Select Status</option>
                                        <option value="1" {{ request('status_filter') == '1' ? 'selected' : '' }}>Connected</option>
                                        <option value="0" {{ request('status_filter') == '0' ? 'selected' : '' }}>Not Connected</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label for="spanko_start_date" class="filter-label">Start Date</label>
                                    <input type="date" name="start_date_filter" id="spanko_start_date" class="form-control datepicker-range flatpickr-input start_date_filter"
                                        value="{{ request('start_date_filter') }}" placeholder="Start Date" data-provider="flatpickr" data-range="true">
                                </div>

                                <div class="col-md-2">
                                    <label for="spanko_end_date" class="filter-label">End Date</label>
                                    <input type="date" name="end_date_filter" id="spanko_end_date" class="form-control datepicker-range flatpickr-input end_date_filter"
                                        value="{{ request('end_date_filter') }}" placeholder="End Date" data-provider="flatpickr" data-range="true">
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive table-wrap">
                            <table id="spankoList" class="table table-bordered dt-responsive nowrap table-striped align-middle mb-0" style="width:100%">
                                <thead>

                                    <tr>
                                       <th style="width: 80px;">Sr No.</th>
                                        <th data-ordering="false">Name</th>
                                        <th>Products</th>
                                        <th>Amount</th>
                                        <th>Total Connected Call</th>
                                        <th>Total Not Connected Call</th>
                                    </tr>

                                </thead>
                                <tbody></tbody>
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

    var table = $('#spankoList').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('spanko.index') }}",
            data: function (d) {
                d.product_filter = $('#product_filter').val();
                d.sale_filter = $('#sale_filter').val();
                d.status_filter = $('#status_filter').val();
                d.start_date_filter = $('.start_date_filter').val();
                d.end_date_filter = $('.end_date_filter').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'products', name: 'products' },
            { data: 'amount', name: 'amount' },
            { data: 'total_connected', name: 'total_connected' },
            { data: 'total_not_connected', name: 'total_not_connected' },
        ]
    });
    $('#product_filter').on('change', function () {
        table.draw();
    });

    $('#sale_filter').on('change', function () {
        table.draw();
    });

    $('#status_filter').on('change', function () {
        table.draw();
    });
    $('.end_date_filter').on('change', function () {
        table.draw();
    });
     $('.start_date_filter').on('change', function () {
        table.draw();
    });
});
</script>
@endsection
