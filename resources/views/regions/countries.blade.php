@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Countries Management</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Administration</a></li>
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Regions</a></li>
                            <li class="breadcrumb-item active">Countries</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->


        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Countries List</h5>
                    </div>
                    <div class="card-body">
                        <!-- Toolbar -->
                        <div class="row g-3 mb-4">
                            <div class="col-sm-4 col-md-6">
                                {{-- <div class="search-box">
                                    <input type="text" class="form-control search" placeholder="Search countries...">
                                    <i class="ri-search-line search-icon"></i>
                                </div> --}}
                            </div>
                            <div class="col-sm-8 col-md-6">
                                <div class="d-flex justify-content-sm-end gap-2">


                                    <a href="javascript:void(0);"
                                        class="btn btn-success"
                                        data-size="md"
                                        data-url="{{ route('regions.countries.create') }}"
                                        data-ajax-popup="true"
                                        data-bs-original-title="{{__('Add New Country')}}"><i class="ri-barcode-box-line align-bottom me-1"></i> Add Country</a>

                                    {{--
                                    <button class="btn btn-success add-btn" data-bs-toggle="modal" data-bs-target="#addCountryModal">
                                        <i class="ri-add-line align-bottom me-1"></i> Add Country
                                    </button>
                                    <button class="btn btn-soft-secondary">
                                        <i class="ri-file-download-line align-bottom me-1"></i> Export
                                    </button> --}}
                                </div>
                            </div>
                        </div>

                        <!-- Countries Table -->
                        <div class="-table-responsive">
                            <table class="table table-bordered table-nowrap align-middle mb-0" id="countryTbl">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 50px;">
                                            Sr. No
                                        </th>
                                        <th scope="col">Country Name</th>
                                        <th scope="col">Country Code</th>
                                         <th scope="col">Status</th>
                                        <th scope="col" style="width: 120px;">Action</th>
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
$(function () {
    var table = $('#countryTbl').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('regions.countries.index') }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex' ,orderable: false, searchable: false},
            {data: 'name', name: 'name'},
            {data: 'code', name: 'code'},
             {data: 'status_nm', name: 'status_nm'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ]
    });

});


</script>
@endsection
