@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Activity Logs</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Audit</a></li>
                            <li class="breadcrumb-item active">Activity Logs</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Filters</h5>
                    </div>
                    <div class="card-body">
                        <form id="activityLogFilters">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-2">
                                    <label for="activity_date_from" class="form-label">Date From</label>
                                    <input type="date" id="activity_date_from" name="date_from" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <label for="activity_date_to" class="form-label">Date To</label>
                                    <input type="date" id="activity_date_to" name="date_to" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <label for="activity_user_id" class="form-label">User</label>
                                    <select id="activity_user_id" name="user_id" class="form-control">
                                        <option value="">All Users</option>
                                        @foreach ($activityLogUsers as $activityUser)
                                            <option value="{{ $activityUser->id }}">{{ $activityUser->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="activity_module" class="form-label">Module</label>
                                    <select id="activity_module" name="module" class="form-control">
                                        <option value="">All Modules</option>
                                        @foreach ($activityLogModules as $activityModule)
                                            <option value="{{ $activityModule }}">{{ \Illuminate\Support\Str::headline($activityModule) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="activity_action" class="form-label">Action</label>
                                    <select id="activity_action" name="action" class="form-control">
                                        <option value="">All Actions</option>
                                        @foreach ($activityLogActions as $activityAction)
                                            <option value="{{ $activityAction }}">{{ \Illuminate\Support\Str::headline($activityAction) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="activity_event_key" class="form-label">Event Key</label>
                                    <input type="text" id="activity_event_key" name="event_key" class="form-control" placeholder="e.g. order.created">
                                </div>
                                <div class="col-md-10">
                                    <label for="activity_keyword" class="form-label">Keyword</label>
                                    <input type="text" id="activity_keyword" name="keyword" class="form-control" placeholder="Search description, subject, reference, IP">
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex gap-2">
                                        <button type="button" id="applyActivityFilters" class="btn btn-primary w-100">Apply</button>
                                        <button type="button" id="resetActivityFilters" class="btn btn-light w-100">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h5 class="card-title mb-1">Activity Log List</h5>
                                <p class="text-muted mb-0">Each row shows who made the change, what was changed, and which record it affected.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="activityLogsTable" class="table table-bordered dt-responsive nowrap table-striped align-middle w-100">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">Log ID</th>
                                        <th style="width: 180px;">Who / When</th>
                                        <th style="min-width: 320px;">What Happened</th>
                                        <th style="min-width: 220px;">Record</th>
                                        <th style="min-width: 280px;">Changes Made</th>
                                        <th>View</th>
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

<div class="modal fade" id="activityDetailsModal" tabindex="-1" aria-labelledby="activityDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activityDetailsModalLabel">Activity Summary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted mb-1">Changed By</label>
                        <div id="activityModalUser" class="fw-semibold">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted mb-1">Changed On</label>
                        <div id="activityModalDate" class="fw-semibold">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted mb-1">Source</label>
                        <div id="activityModalSource" class="fw-semibold">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted mb-1">Module</label>
                        <div id="activityModalModule" class="fw-semibold">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted mb-1">Action</label>
                        <div id="activityModalAction" class="fw-semibold">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted mb-1">Event Key</label>
                        <div id="activityModalEventKey" class="fw-semibold">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted mb-1">IP Address</label>
                        <div id="activityModalIp" class="fw-semibold">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted mb-1">Affected Record</label>
                        <div id="activityModalSubject" class="fw-semibold">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted mb-1">Linked Record</label>
                        <div id="activityModalReference" class="fw-semibold">-</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted mb-1">Summary</label>
                    <div id="activityModalDescription" class="border rounded p-3 bg-light-subtle">-</div>
                </div>

                <div id="activityModalPropertiesWrapper" class="d-none">
                    <label class="form-label text-muted mb-1">What Changed</label>
                    <div id="activityModalProperties" class="border rounded p-3 bg-body"></div>
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
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
@endsection

@section('page-script')
<script>
$(document).ready(function () {
    var table = $('#activityLogsTable').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        ordering: false,
        pageLength: 15,
        ajax: {
            url: "{{ route('activity-logs.index') }}",
            data: function (d) {
                d.date_from = $('#activity_date_from').val();
                d.date_to = $('#activity_date_to').val();
                d.user_id = $('#activity_user_id').val();
                d.module = $('#activity_module').val();
                d.action = $('#activity_action').val();
                d.event_key = $('#activity_event_key').val();
                d.keyword = $('#activity_keyword').val();
            }
        },
        columns: [
            { data: 'row_id', name: 'id' },
            { data: 'who_when_html', name: 'created_at', orderable: false, searchable: false },
            { data: 'activity_html', name: 'description', orderable: false, searchable: false },
            { data: 'record_html', name: 'subject_type', orderable: false, searchable: false },
            { data: 'changes_preview_html', name: 'properties', orderable: false, searchable: false },
            { data: 'action_buttons', name: 'action_buttons', orderable: false, searchable: false }
        ]
    });

    $('#applyActivityFilters').on('click', function () {
        table.draw();
    });

    $('#resetActivityFilters').on('click', function () {
        $('#activityLogFilters')[0].reset();
        table.draw();
    });

    $('#activity_keyword, #activity_event_key').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            table.draw();
        }
    });

    $('#activityLogsTable').on('click', '.js-view-activity', function () {
        var currentRow = $(this).closest('tr');
        var rowData = table.row(currentRow).data();

        if (!rowData && currentRow.hasClass('child')) {
            rowData = table.row(currentRow.prev()).data();
        }

        if (!rowData) {
            return;
        }

        $('#activityModalUser').text(rowData.user_name || 'System');
        $('#activityModalDate').text(rowData.created_at_full || rowData.created_at || '-');
        $('#activityModalSource').text(rowData.source_label || '-');
        $('#activityModalModule').text($('<div>').html(rowData.module_label || '').text() || '-');
        $('#activityModalAction').text($('<div>').html(rowData.action_label || '').text() || '-');
        $('#activityModalEventKey').text(rowData.event_key_display || '-');
        $('#activityModalIp').text(rowData.ip_address_display || '-');
        $('#activityModalSubject').text(rowData.subject_label || '-');
        $('#activityModalReference').text(rowData.reference_label || '-');
        $('#activityModalDescription').text(rowData.description_text || '-');

        if (rowData.properties_html) {
            $('#activityModalProperties').html(rowData.properties_html);
            $('#activityModalPropertiesWrapper').removeClass('d-none');
        } else {
            $('#activityModalProperties').empty();
            $('#activityModalPropertiesWrapper').addClass('d-none');
        }
    });
});
</script>
@endsection
