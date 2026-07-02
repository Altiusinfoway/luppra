@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Spanko Section</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('spanko.index') }}">Spanko</a></li>
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
                    <h5 class="card-title m-2">Spanko List</h5>

                     <div class="card-header d-flex justify-content-between align-items-center">

                        <div class="d-flex align-items-center w-100 justify-content-between">

                            <div class="d-flex gap-2">
                                <div class="col-md-4">
                                  <select name="product_filter" class="form-control" id="product_filter">
                                    <option value="0">Select Product</option>
                                    @foreach($product_list as $id => $name)
                                        <option value="{{ $id }}" {{ $id == request('product_filter') ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                  </select>
                                </div>

                                <div class="col-md-4">
                                    <select name="sale_filter" class="form-control">
                                        <option value="0">Select Designation</option>
                                        <option value="sale" {{ request('sale_filter') == 'sale' ? 'selected' : '' }}>Sale</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <select name="status_filter" class="form-control" id="status_filter">
                                        <option value="">Select Status</option>
                                        <option value="1" {{ request('status_filter') == '1' ? 'selected' : '' }}>Connected</option>
                                        <option value="0" {{ request('status_filter') == '0' ? 'selected' : '' }}>Not Connected</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <input type="date" name="start_date_filter" class="form-control datepicker-range flatpickr-input start_date_filter"
                                        value="{{ request('start_date_filter') }}" placeholder="Start Date" id="datepicker-range" data-provider="flatpickr" data-range="true">
                                </div>

                                <div class="col-md-3">
                                    <input type="date" name="end_date_filter" class="form-control datepicker-range flatpickr-input end_date_filter"
                                        value="{{ request('end_date_filter') }}" placeholder="End Date" id="datepicker-range" data-provider="flatpickr" data-range="true">
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card-body">
                        <table id="spankoList" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
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
