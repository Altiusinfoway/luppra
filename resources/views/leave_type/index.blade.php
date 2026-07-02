@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Leave Type</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('leave-types.index') }}">Leave Type</a></li>
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Leave Type List</h5>

                        @can('create leave type')
                        <a href="{{ route('leave-types.create') }}" class="btn btn-sm btn-success" id="addproduct-btn">
                            <i class="ri-add-line align-bottom me-1"></i> Add Leave Type
                        </a>
                        @endcan
                    </div>
                    <div class="card-body">
                        <table id="departmentList" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th data-ordering="false">Sr No</th>
                                    <th data-ordering="false">Name</th>
                                    <th>Action</th>
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
    var table = $('#departmentList').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('leave-types.index') }}",
            data: function (d) {
            }
        },
        columns: [
            {  data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false },
            { data: 'name', name: 'name' },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ]
    });
});
</script>
@endsection

