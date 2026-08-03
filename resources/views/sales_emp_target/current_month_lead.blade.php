@extends('layouts.app')

@section('page-css')
<style>
.employee-target-suite {
    background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
}

.employee-target-suite .hero-shell,
.employee-target-suite .shell-card {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
}

.employee-target-suite .hero-shell {
    background:
        radial-gradient(circle at top right, rgba(14, 165, 233, 0.14), transparent 30%),
        radial-gradient(circle at left center, rgba(139, 92, 246, 0.14), transparent 30%),
        linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}

.employee-target-suite .hero-eyebrow {
    display: inline-flex;
    align-items: center;
    padding: 7px 12px;
    border-radius: 999px;
    border: 1px solid #dbeafe;
    background: rgba(255, 255, 255, 0.86);
    color: #1d4ed8;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}
</style>
@endsection

@section('content')
<div class="page-content employee-target-suite">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="card hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <span class="hero-eyebrow">Lead Performance</span>
                                <h2 class="mt-3 mb-2">Lead List</h2>
                                <p class="text-muted mb-0">Review the leads contributing to the selected monthly target from a cleaner performance detail screen.</p>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('sales-employee-targets.index',['all_months',$user_id]) }}">Sales Employee Target</a></li>
                                         <li class="breadcrumb-item"><a href="{{ route('sales-employee-targets.index',['current_month',$user_id]) }}">Monthly Target</a></li>
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
            <div class="col-lg-12">
                <div class="card shell-card">
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
