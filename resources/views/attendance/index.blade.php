@extends('layouts.app')

@section('content')
<style>
.table-group-header {
    background-color: #405189;
    font-weight: bold;
    text-align: left;
    color:rgb(255, 255, 255);
}

</style>
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Attendance Section</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('attendances.index') }}">Attendance</a></li>
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
                        <h5 class="card-title mb-0">Attendance List</h5>

                        <div class="d-flex align-items-center">

                            @if($show_check_in)
                            <div>
                                <form method="post" action="{{ route('attendances.store') }}">
                                    @csrf
                                    <button type="submit" name="action" value="check_in" class="btn btn-success">
                                        <i class="ri-login-circle-line align-bottom me-1"></i> Check In
                                    </button>
                                </form>
                            </div>
                            @endif

                            @if($show_check_out && $latest_attendance)
                            <div class="ms-4">
                                <form method="post" action="{{ route('attendances.attendance-update', $latest_attendance->id) }}">
                                    @csrf
                                    <button type="submit" name="action" value="check_out" class="btn btn-danger">
                                        <i class="ri-logout-circle-line align-bottom me-1"></i> Check Out
                                    </button>
                                </form>
                            </div>
                            @endif

                        </div>

                    </div>
                    <div>
                        @if (session('error_msg'))
                            <div class="alert alert-danger col-md-4 m-4" id="error_model">
                                {{ session('error_msg') }}
                            </div>
                        @endif
                    </div>
                    <div class="card-body">

                        <table id="attendanceList" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                            <thead>

                                <tr>
                                    <th>Sr No</th>
                                    @if(Auth::user()->type == 'company')
                                        <th>Employee</th>
                                    @endif
                                    <th>Date</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Total Hours</th>
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
<script>
    $(document).ready(function(){
        setTimeout(function(){
            $('#error_model').fadeOut(1000);
        }, 3000);
    });
</script>
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

    var table = $('#attendanceList').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('attendances.index') }}",
            data: function (d) {
            }
        },
        columns: [
            { data: 'sr_no', name: 'sr_no', orderable: false },
            @if(Auth::user()->type == 'company')
                { data: 'emp_name', name: 'emp_name', orderable: false },
            @endif
            { data: 'date', name: 'date', orderable: false },
            { data: 'check_in', name: 'check_in', orderable: false },
            { data: 'check_out', name: 'check_out', orderable: false },
            { data: 'total_hours', name: 'total_hours', orderable: false },
        ],
        createdRow: function(row, data, dataIndex) {
            if (data.row_type === 'header') {
                $(row).addClass('table-group-header')
                $('td', row).attr('colspan', 6).html(`<div style="text-align:center;">${data.date}</div>`);
                $('td:gt(0)', row).remove();
            } else if (data.row_type === 'total') {
                $(row).addClass('table-total-row');
            }
        }

    });
});
</script>
@endsection
