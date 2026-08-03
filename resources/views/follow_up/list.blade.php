@extends('layouts.app')

@section('page-css')
    <style>
        .workflow-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .workflow-suite .hero-shell,
        .workflow-suite .toolbar-shell,
        .workflow-suite .table-shell {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
        }

        .workflow-suite .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.76);
            border: 1px solid #dbeafe;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
    </style>
@endsection

@section('content')
    <div class="page-content workflow-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-8">
                                    <span class="hero-eyebrow">Follow-Up Workspace</span>
                                    <h1 class="mb-3">Follow-Up List</h1>
                                    <p class="text-muted mb-0">Track upcoming, expired, and reported follow-up activity in the same workflow dashboard language as the refreshed lead pipeline.</p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="#">Follow-Up</a></li>
                                            <li class="breadcrumb-item active">List</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

<div class="card toolbar-shell mb-4">
    <div class="card-body">

        <form id="lead-filter-form"
              class="d-flex flex-nowrap align-items-end gap-2 overflow-auto">

            <div style="min-width:160px">
                {{ Form::date('start_date', null, [
                    'class' => 'form-control',
                    'id' => 'start_date',
                ]) }}
            </div>

            <div style="min-width:160px">
                {{ Form::date('end_date', null, [
                    'class' => 'form-control',
                    'id' => 'end_date',
                ]) }}
            </div>

            @if (\Auth::user()->type == 'company')
                <div style="min-width:200px">
                    <select name="sales_user" class="form-control">
                        <option value="">Select Sales User</option>
                        @foreach ($get_sales_user as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div style="min-width:200px">
                <select name="lead_status" class="form-control">
                    <option value="">Select Lead Status</option>
                    @foreach ($status_list as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="min-width:120px">
                <button type="submit" class="btn btn-primary w-100">
                    Search
                </button>
            </div>

        </form>

         @if (\Auth::user()->type == 'Sales')
        <div class="text-end mt-3">
            <a href="{{ route('follow-ups.create', $dynamic_slug) }}"
               class="btn btn-success">
                <i class="ri-add-line me-1"></i> Add Follow-Up
            </a>
        </div>
        @endif

    </div>
</div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card table-shell">
                        <div class="card-body">

                            <div class="row align-items-center mb-3">
                                <div class="col-md-6">
                                    <h5 class="card-title m-0">Follow-Up List</h5>
                                </div>
                            </div>

                            <!-- Tabs -->
                            <ul class="nav nav-tabs mb-3" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#tab_upcomming" role="tab">
                                        Upcoming
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tab_expired" role="tab">
                                        Expired
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tab_not_interested" role="tab">
                                        Not Interested
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tab_follow_up_report" role="tab">
                                        Follow-up Report
                                    </a>
                                </li>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content text-muted">

                                <!-- UPCOMING -->
                                <div class="tab-pane fade show active" id="tab_upcomming" role="tabpanel">
                                    <div class="table-responsive">
                                    <table id="tbl_upcomming"
                                        class="table table-bordered table-responsive  table-striped align-middle"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Sr No.</th>
                                                <th>Leads Details</th>
                                                <th>Follow-Up</th>
                                                <th>Lead Source</th>
                                                <th>Lead Status</th>
                                                <th>Contact Us</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                    </div>
                                </div>

                                <!-- EXPIRED -->
                                <div class="tab-pane fade" id="tab_expired" role="tabpanel">
                                    <div class="table-responsive">
                                    <table id="tbl_expired"
                                        class="table table-bordered  table-striped align-middle"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Sr No.</th>
                                                <th>Leads Details</th>
                                                <th>Follow-Up</th>
                                                <th>Lead Source</th>
                                                <th>Lead Status</th>
                                                <th>Contact Us</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                    </div>
                                </div>

                                <!-- not interested --- -->
                                <div class="tab-pane fade " id="tab_not_interested" role="tabpanel">
                                    <div class="table-responsive">
                                    <table id="tbl_not_interested"
                                        class="table table-bordered  table-striped align-middle"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Sr No.</th>
                                                <th>Leads Details</th>
                                                <th>Follow-Up</th>
                                                <th>Lead Source</th>
                                                <th>Lead Status</th>
                                                <th>Contact Us</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                    </div>
                                </div>

                                <!-- follow up reports ---- -->
                                <div class="tab-pane fade" id="tab_follow_up_report" role="tabpanel">
                                    <div class="table-responsive">
                                    <table id="tbl_follow_up_report"
                                        class="table table-bordered  table-striped align-middle"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Sr No.</th>
                                                <th>Leads Details</th>
                                                <th>Follow-Up</th>
                                                <th>Lead Source</th>
                                                <th>Lead Status</th>
                                                <th>Contact Us</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
@endsection

@section('page-script')
    <script>
       let upcomingTable = null;
let expiredTable = null;
let notInterestedTable = null;
let followUpReportTable = null;

     function initUpcomingTable() {
    if (upcomingTable) return;

    upcomingTable = $('#tbl_upcomming').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('follow-ups.list','upcomming') }}",
            data: function (d) {
                d.start_date  = $('#start_date').val();
                d.end_date    = $('#end_date').val();
                d.sales_user  = $('select[name="sales_user"]').val();
                d.lead_status = $('select[name="lead_status"]').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false },
            { data: 'lead_all_detail' },
            { data: 'follow_up_date' },
            { data: 'sources' },
            { data: 'lead_status' },
            { data: 'cust_phone' },
            { data: 'action', orderable: false, searchable: false }
        ],
                initComplete: function(settings, json) {

					enableConfirmationOn('change',"need-confirmation","You want to change status?", function(url, data){

						getAjax(url, function(response){
							if(response.success == 'true'){
								show_toastr('success',response.message);
							} else {
								show_toastr('error',response.message);
							}
							table.ajax.reload();
						});
					});
				},
    });
}
        function initExpiredTable() {
    if (expiredTable) return;

    expiredTable = $('#tbl_expired').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('follow-ups.list','expired') }}",
            data: function (d) {
                d.start_date  = $('#start_date').val();
                d.end_date    = $('#end_date').val();
                d.sales_user  = $('select[name="sales_user"]').val();
                d.lead_status = $('select[name="lead_status"]').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false },
            { data: 'lead_all_detail' },
            { data: 'follow_up_date' },
            { data: 'sources' },
            { data: 'lead_status' },
            { data: 'cust_phone' },
            { data: 'action', orderable: false, searchable: false }
        ],
                initComplete: function(settings, json) {

					enableConfirmationOn('change',"need-confirmation","You want to change status?", function(url, data){

						getAjax(url, function(response){
							if(response.success == 'true'){
								show_toastr('success',response.message);
							} else {
								show_toastr('error',response.message);
							}
							table.ajax.reload();
						});
					});
				},
    });
}

       function initNotInterestedTable() {
    if (notInterestedTable) return;

    notInterestedTable = $('#tbl_not_interested').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('follow-ups.list','notinterested') }}",
            data: function (d) {
                d.start_date  = $('#start_date').val();
                d.end_date    = $('#end_date').val();
                d.sales_user  = $('select[name="sales_user"]').val();
                d.lead_status = $('select[name="lead_status"]').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false },
            { data: 'lead_all_detail' },
            { data: 'follow_up_date' },
            { data: 'sources' },
            { data: 'lead_status' },
            { data: 'cust_phone' },
            { data: 'action', orderable: false, searchable: false }
        ],
                initComplete: function(settings, json) {

					enableConfirmationOn('change',"need-confirmation","You want to change status?", function(url, data){

						getAjax(url, function(response){
							if(response.success == 'true'){
								show_toastr('success',response.message);
							} else {
								show_toastr('error',response.message);
							}
							table.ajax.reload();
						});
					});
				},
    });
}function initFollowUpReportTable() {
    if (followUpReportTable) return;

    followUpReportTable = $('#tbl_follow_up_report').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('follow-ups.list','all') }}",
            data: function (d) {
                d.start_date  = $('#start_date').val();
                d.end_date    = $('#end_date').val();
                d.sales_user  = $('select[name="sales_user"]').val();
                d.lead_status = $('select[name="lead_status"]').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false },
            { data: 'lead_all_detail' },
            { data: 'follow_up_date' },
            { data: 'sources' },
            { data: 'lead_status' },
            { data: 'cust_phone' },
            { data: 'action', orderable: false, searchable: false }
        ],
                initComplete: function(settings, json) {

					enableConfirmationOn('change',"need-confirmation","You want to change status?", function(url, data){

						getAjax(url, function(response){
							if(response.success == 'true'){
								show_toastr('success',response.message);
							} else {
								show_toastr('error',response.message);
							}
							table.ajax.reload();
						});
					});
				},
    });
}
$(document).ready(function () {
    initUpcomingTable(); // default active tab
});

$('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
    let target = $(e.target).attr('href');

    if (target === '#tab_upcomming') {
        initUpcomingTable();
    }
    if (target === '#tab_expired') {
        initExpiredTable();
    }
    if (target === '#tab_not_interested') {
        initNotInterestedTable();
    }
    if (target === '#tab_follow_up_report') {
        initFollowUpReportTable();
    }
});

$('#lead-filter-form').on('submit', function (e) {
    e.preventDefault();

    let activeTab = $('.nav-tabs .nav-link.active').attr('href');

    if (activeTab === '#tab_upcomming' && upcomingTable) {
        upcomingTable.ajax.reload();
    }
    if (activeTab === '#tab_expired' && expiredTable) {
        expiredTable.ajax.reload();
    }
    if (activeTab === '#tab_not_interested' && notInterestedTable) {
        notInterestedTable.ajax.reload();
    }
    if (activeTab === '#tab_follow_up_report' && followUpReportTable) {
        followUpReportTable.ajax.reload();
    }
});
    </script>
@endsection
