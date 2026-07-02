@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Lead List</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('sales-employee-targets.index',['all_months',$user_id]) }}">Sales Employee Target</a></li>
                             <li class="breadcrumb-item"><a href="{{ route('sales-employee-targets.index',['current_month',$user_id]) }}">Monthly Target</a></li>
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
                    <div class="card-header">
                        <h5 class="card-title  mb-0">All Leads</h5>
                        <div class="d-flex justify-content-between align-items-center mt-2">

                            <div class="row mt-2 align-items-center g-2">
                                {{-- <div class="col-md-6">
                                    <div class="search-box position-relative">
                                        <input type="text" class="form-control" id="search-task-options" placeholder="Search for name...">
                                        <i class="ri-search-line search-icon position-absolute"></i>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <select name="state_filter" id="state_filter" class="form-control">
                                        <option value="">All States</option>
                                        @foreach ($state_list as $slist)
                                            <option value="{{ $slist['state'] }}">{{ $slist['state'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                 <div class="col-md-3">
                                    <select name="city_filter" id="city_filter" class="form-control">
                                        <option value="">All City</option>
                                        @foreach ($city_list as $clist)
                                            <option value="{{ $clist['city'] }}">{{ $clist['city'] }}</option>
                                        @endforeach
                                    </select>
                                </div> --}}

                            </div>


                        </div>
                    </div>
                    <div class="card-body">

                        <table id="LeadMonthList" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                            <thead>

                                <tr>
                                    <th data-ordering="false">Sr No</th>
                                    <th data-ordering="false">Lead Detail</th>
                                    {{-- <th data-ordering="false">Contact Detail</th>
                                     <th data-ordering="false">Lead Source</th> --}}
                                    {{-- <th>Action</th> --}}
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

    var table = $('#LeadMonthList').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('sales-employee-targets.get_month_lead',[$user_id,$employee_id,$sales_target_assign_date]) }}",
            data: function (d) {
                d.name = $('#search-task-options').val();
                d.state_filter = $('#state_filter').val();
                d.city_filter = $('#city_filter').val();
            }
        },
        columns: [
            {  data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false },
            { data: 'lead_detail', name: 'lead_detail'},
            // { data: 'achieve_amt', name: 'achieve_amt' },
            //  { data: 'performance_per', name: 'performance_per' },
            // {
            //     data: 'action',
            //     name: 'action',
            //     orderable: false,
            //     searchable: false
            // }
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
