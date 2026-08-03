@extends('layouts.app')

@section('page-css')
<style>
.regions-suite{background:linear-gradient(180deg,rgba(248,250,252,.72) 0%,rgba(245,247,251,0) 100%)}
.regions-suite .hero-shell,.regions-suite .shell-card{border:1px solid rgba(255,255,255,.8);border-radius:24px;background:rgba(255,255,255,.9);box-shadow:0 18px 40px rgba(15,23,42,.06)}
.regions-suite .hero-shell{background:radial-gradient(circle at top right, rgba(59,130,246,.16), transparent 30%),radial-gradient(circle at left center, rgba(99,102,241,.12), transparent 30%),linear-gradient(135deg,#ffffff 0%,#f8fafc 100%)}
.regions-suite .hero-eyebrow{display:inline-flex;align-items:center;padding:7px 12px;border-radius:999px;border:1px solid #bfdbfe;background:rgba(255,255,255,.86);color:#1d4ed8;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.regions-suite .metric-tile{height:100%;border:1px solid rgba(255,255,255,.78);border-radius:20px;background:rgba(255,255,255,.84);box-shadow:0 12px 28px rgba(15,23,42,.05)}
.regions-suite .metric-tile .label{display:block;margin-bottom:8px;color:#64748b;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.regions-suite .metric-tile h3{margin:0;font-size:1.8rem;font-weight:800;letter-spacing:-.03em}
.regions-suite .toolbar-strip{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;padding:1rem 1.1rem 1.4rem;border-bottom:1px solid rgba(226,232,240,.8)}
.regions-suite .table-note{max-width:620px}
.regions-suite .table-shell-wrap{padding:0 1.1rem 1.1rem}
.regions-suite .table-responsive{border:1px solid #e2e8f0;border-radius:20px;overflow:hidden;background:#fff}
.regions-suite table thead th{background:#f8fafc!important}
</style>
@endsection

@section('content')
<div class="page-content regions-suite">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <span class="hero-eyebrow">Region Admin</span>
                                <h2 class="mt-3 mb-2">States Management</h2>
                                <p class="text-muted mb-0">Manage state records from the same lighter administration shell used across the refreshed back office.</p>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Administration</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Regions</a></li>
                                        <li class="breadcrumb-item active">States</li>
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
                <div class="card metric-tile">
                    <div class="card-body">
                        <span class="label">Region Layer</span>
                        <h3>States</h3>
                        <p class="text-muted mb-0 mt-2">Mid-level geography records that connect countries with city and address data.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card metric-tile">
                    <div class="card-body">
                        <span class="label">Management Mode</span>
                        <h3>Server Sync</h3>
                        <p class="text-muted mb-0 mt-2">Data stays searchable and sortable while keeping the lighter dashboard treatment.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card shell-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">States List</h5>
                    </div>
                    <div class="card-body">
                        <div class="toolbar-strip">
                            <div class="table-note">
                                <h6 class="mb-1">State Registry</h6>
                                <p class="text-muted mb-0">Manage state records with a cleaner toolbar, softer table framing, and the same dashboard language used throughout the refreshed app.</p>
                            </div>
                            <a href="javascript:void(0);"
                                class="btn btn-success"
                                data-size="md"
                                data-url="{{ route('regions.states.create') }}"
                                data-ajax-popup="true"
                                data-bs-original-title="{{__('Add New State')}}"><i class="ri-add-line align-bottom me-1"></i> Add State</a>
                        </div>

                        <div class="table-shell-wrap">
                        <div class="table-responsive">
                            <table class="table table-bordered table-nowrap align-middle mb-0" id="stateTbl">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 50px;">
                                            Sr. No
                                        </th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Country Name</th>
                                        <th scope="col">Status</th>
                                        <th scope="col" style="width: 120px;">Action</th>
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
$(function () {
    var table = $('#stateTbl').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('regions.states.index') }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex' ,orderable: false, searchable: false},
            {data: 'name', name: 'name'},
            {data: 'country_name', name: 'country_name'},
            {data: 'status_nm', name: 'status_nm'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ]
    });

});


</script>
@endsection
