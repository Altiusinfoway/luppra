@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Designation </h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('designations.index') }}">Designation</a></li>
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
                        <h5 class="card-title mb-0">Designation List</h5>
                        @can('create designation')
                        <a href="{{ route('designations.create') }}" class="btn btn-sm btn-success" id="addproduct-btn">
                            <i class="ri-add-line align-bottom me-1"></i> Add Designation
                        </a>
                        @endcan
                    </div>
                    <div class="card-body">
                        {{-- <table id="example" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Sr No.</th>
                                    <th data-ordering="false">Name</th>
                                    <th data-ordering="false">Department Name</th>
                                    <th style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($designation_list as $list)
                                <tr class="main-row">
                                    <td>{{ $list['id'] }}</td>
                                    <td>
                                        <h6>{{ $list['name'] }}</h6>
                                    </td>
                                    <td>{{ $list->departments->name ?? '' }}</td>
                                    <td>
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-more-fill align-middle"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a href="{{ route('designations.edit',$list['id']) }}" class="dropdown-item edit-item-btn"><i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit</a></li>
                                                <li>
                                                    <a class="dropdown-item remove-item-btn"
                                                    data-delete-popup="true"
                                                    data-bs-original-title="You are about to delete a Designation ?"  data-bs-original-description="Deleting your Designation will remove all of your information from our database."
                                                    data-original-title=""
                                                    data-url="{{ route('designations.delete',[$list['id']]) }}"
                                                    data-method="DELETE"
                                                    data-cb="afterDelete"
                                                    href="javascript:void(0)">
                                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach

                            </tbody>
                        </table> --}}

                        <table id="designationList" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                            <thead>

                                <tr>
                                    <th data-ordering="false" style="width: 50px">Sr No</th>
                                    <th data-ordering="false" style="width: 100px">Name</th>
                                    <th data-ordering="false">Department Name</th>
                                    <th style="width: 50px">Action</th>
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

    var table = $('#designationList').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('designations.index') }}",
            data: function (d) {
            }
        },
        columns: [
            {  data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false },
            { data: 'name', name: 'name' },
            { data: 'department_id', name: 'department_id' },
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
