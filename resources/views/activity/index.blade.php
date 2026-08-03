@extends('layouts.app')

@section('page-css')
<style>
.activity-suite {
    background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
}

.activity-suite .hero-shell,
.activity-suite .shell-card {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
}

.activity-suite .hero-shell {
    background:
        radial-gradient(circle at top right, rgba(99, 102, 241, 0.14), transparent 30%),
        radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
        linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}

.activity-suite .hero-eyebrow {
    display: inline-flex;
    align-items: center;
    padding: 7px 12px;
    border-radius: 999px;
    border: 1px solid #c7d2fe;
    background: rgba(255, 255, 255, 0.86);
    color: #4338ca;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.activity-suite .summary-card {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.86);
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
}

.activity-suite .summary-card .label {
    display: block;
    margin-bottom: 8px;
    color: #64748b;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.activity-suite .summary-card h3 {
    margin: 0;
    font-size: 1.7rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: #0f172a;
}

.activity-suite .toolbar-shell {
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: #f8fafc;
    padding: 14px 16px;
}

.activity-suite .table-wrap {
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    overflow: hidden;
    background: #fff;
}

.activity-suite .activity-modal-shell {
    border: 1px solid rgba(255, 255, 255, 0.82);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 20px 48px rgba(15, 23, 42, 0.12);
}

.activity-suite .activity-modal-shell .modal-header {
    background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
    border-bottom: 1px solid #e2e8f0;
}

.activity-suite .activity-modal-shell .modal-body {
    background: #ffffff;
}

.activity-suite .activity-summary-box,
.activity-suite .activity-properties-box {
    border: 1px solid #e2e8f0 !important;
    border-radius: 16px;
    background: #f8fafc !important;
}
</style>
@endsection

@section('content')
<div class="page-content activity-suite">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <span class="hero-eyebrow">Audit Trail</span>
                                <h2 class="mt-3 mb-2">Activity Logs</h2>
                                <p class="text-muted mb-0">Filter and inspect system changes with a quieter audit surface that matches the rest of the refreshed dashboard.</p>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript:void(0);">Audit</a></li>
                                        <li class="breadcrumb-item active">Activity Logs</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Audit</span>
                        <h3>{{ $activityLogUsers->count() }}</h3>
                        <p class="text-muted mb-0 mt-2">Users currently represented in the log filters, giving you a quick sense of audit coverage across the workspace.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Scope</span>
                        <h3>{{ count($activityLogModules) }}</h3>
                        <p class="text-muted mb-0 mt-2">Modules already flowing into the activity feed, now surfaced in the same KPI style as the newer admin dashboards.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Actions</span>
                        <h3>{{ count($activityLogActions) }}</h3>
                        <p class="text-muted mb-0 mt-2">Tracked event actions available for filtering while investigating operational changes and workflow history.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shell-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Filters</h5>
                    </div>
                    <div class="card-body">
                        <form id="activityLogFilters">
                            <div class="row g-3 align-items-end toolbar-shell">
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
                <div class="card shell-card">
                    <div class="card-header">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h5 class="card-title mb-1">Activity Log List</h5>
                                <p class="text-muted mb-0">Each row shows who made the change, what was changed, and which record it affected.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table-wrap">
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
        <div class="modal-content activity-modal-shell">
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
                    <div id="activityModalDescription" class="border rounded p-3 bg-light-subtle activity-summary-box">-</div>
                </div>

                <div id="activityModalPropertiesWrapper" class="d-none">
                    <label class="form-label text-muted mb-1">What Changed</label>
                    <div id="activityModalProperties" class="border rounded p-3 bg-body activity-properties-box"></div>
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
