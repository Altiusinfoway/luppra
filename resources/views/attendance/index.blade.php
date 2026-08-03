@extends('layouts.app')

@section('page-css')
<style>
.attendance-suite {
    background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
}

.attendance-suite .hero-shell,
.attendance-suite .shell-card {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
}

.attendance-suite .hero-shell {
    background:
        radial-gradient(circle at top right, rgba(59, 130, 246, 0.14), transparent 30%),
        radial-gradient(circle at left center, rgba(14, 165, 233, 0.12), transparent 30%),
        linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}

.attendance-suite .hero-eyebrow {
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

.attendance-suite .table-group-header {
    background: #1e3a8a;
    font-weight: 700;
    text-align: left;
    color: #ffffff;
}

.attendance-suite .summary-card {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.86);
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
}

.attendance-suite .summary-card .label {
    display: block;
    margin-bottom: 8px;
    color: #64748b;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
}

.attendance-suite .summary-card h3 {
    margin: 0;
    font-size: 1.7rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: #0f172a;
}

.attendance-suite .toolbar-shell {
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: #f8fafc;
    padding: 14px 16px;
}

.attendance-suite .table-wrap {
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    overflow: hidden;
    background: #fff;
}

.attendance-suite .status-banner {
    border: 1px solid #fecaca;
    border-radius: 18px;
    padding: 1rem 1.1rem;
    background: linear-gradient(180deg, #fef2f2 0%, #fffafa 100%);
    color: #b91c1c;
    box-shadow: 0 12px 26px rgba(239, 68, 68, 0.08);
    margin: 1rem 1.5rem 0;
}

.attendance-suite .status-banner .banner-label {
    display: block;
    margin-bottom: 0.3rem;
    font-size: 0.76rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    opacity: .82;
}
</style>
@endsection

@section('content')
<div class="page-content attendance-suite">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <span class="hero-eyebrow">Attendance Tracking</span>
                                <h2 class="mt-3 mb-2">Attendance Section</h2>
                                <p class="text-muted mb-0">Track check-ins, check-outs, and daily working hours from a cleaner workforce dashboard aligned with the refreshed admin UI.</p>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('attendances.index') }}">Attendance</a></li>
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
                        <span class="label">Tracking</span>
                        <h3>Attendance</h3>
                        <p class="text-muted mb-0 mt-2">Check-ins and work duration are surfaced in the same dashboard-friendly pattern as other modules.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Actions</span>
                        <h3>Live</h3>
                        <p class="text-muted mb-0 mt-2">Keep check-in and check-out actions visually grouped with the daily log below.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card shell-card">
                    <div class="card-header">
                        <div class="toolbar-shell d-flex justify-content-between align-items-center flex-wrap gap-3">
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

                    </div>
                    <div>
                        @if (session('error_msg'))
                            <div class="status-banner" id="error_model">
                                <span class="banner-label">Attendance issue</span>
                                {{ session('error_msg') }}
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table-wrap">
                        <table id="attendanceList" class="table table-bordered dt-responsive nowrap table-striped align-middle mb-0" style="width:100%">
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
                </div>
            </div><!--end col-->
        </div><!--end row-->
    </div>
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
