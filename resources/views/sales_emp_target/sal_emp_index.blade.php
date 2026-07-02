@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Sales Employee Target</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            @php
                                if($dynamic_slug == 'current_month')
                                {
                                   $usr=null;
                                   $slg='all_months';
                                }
                                else {
                                    $usr=$user_id;
                                    $slg=$dynamic_slug;
                                }
                            @endphp
                            <li class="breadcrumb-item"><a href="{{ route('sales-employee-targets.index',[$slg,$usr]) }}">Sales Employee Target</a></li>
                            <li class="breadcrumb-item active">List </li>
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
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center mt-2">
                        <h5 class="card-title  mb-0">Sales Employee Target</h5>

                            <div class="row mt-2 align-items-center g-2">
                            </div>

                            @if(!$exists)
                            <div>
                                <a href="{{ route('sales-employee-targets.create') }}" class="btn btn-sm btn-success" id="addproduct-btn">
                                    <i class="ri-add-line align-bottom me-1"></i> Add Target
                                </a>
                            </div>
                            @endif

                        </div>
                    </div>
                    <div class="card-body">

                        <table id="SalesTargetList" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                            <thead>

                                <tr>
                                    <th data-ordering="false">Sr No</th>
                                    <th data-ordering="false">Sales Person Name</th>
                                     @if($dynamic_slug == 'current_month')
                                    <th data-ordering="false">Month</th>
                                    @endif

                                    @if($dynamic_slug == 'all_months')
                                    <th data-ordering="false">Total Target Collection</th>
                                    @else
                                    <th data-ordering="false" >Total Target</th>
                                    @endif

                                    <th data-ordering="false">Total Collection</th>
                                     <th data-ordering="false">Performance (%)</th>
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

    var table = $('#SalesTargetList').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('sales-employee-targets.index',[$dynamic_slug,$user_id]) }}",
            data: function (d) {
                d.name = $('#search-task-options').val();
                d.state_filter = $('#state_filter').val();
                d.city_filter = $('#city_filter').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'user_name', name: 'user_name' },
            @if($dynamic_slug == 'current_month')
             { data: 'month_name', name: 'month_name'},
            @endif
            { data: 'total_target', name: 'total_target'},

            { data: 'achieve_amt', name: 'achieve_amt' },
             { data: 'performance_per', name: 'performance_per' },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ]
    });

    $('#search-task-options').on('keyup', function () {
        table.draw();
    });

    $('#state_filter').on('change', function () {
        table.draw();
    });

    $('#city_filter').on('change', function () {
        table.draw();
    });
});
</script>
@endsection
