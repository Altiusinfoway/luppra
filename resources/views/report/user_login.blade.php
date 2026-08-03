@extends('layouts.app')

@section('page-css')
<style>
.report-suite{background:linear-gradient(180deg,rgba(248,250,252,.72) 0%,rgba(245,247,251,0) 100%)}
.report-suite .hero-shell,.report-suite .shell-card,.report-suite .chart-card,.report-suite .kpi-card{border:1px solid rgba(255,255,255,.8);border-radius:24px;background:rgba(255,255,255,.9);box-shadow:0 18px 40px rgba(15,23,42,.06)}
.report-suite .hero-shell{background:radial-gradient(circle at top right, rgba(59,130,246,.16), transparent 30%),radial-gradient(circle at left center, rgba(16,185,129,.12), transparent 30%),linear-gradient(135deg,#ffffff 0%,#f8fafc 100%)}
.report-suite .hero-eyebrow{display:inline-flex;align-items:center;padding:7px 12px;border-radius:999px;border:1px solid #bfdbfe;background:rgba(255,255,255,.86);color:#1d4ed8;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.report-suite .filter-shell{border:1px solid #e2e8f0;border-radius:20px;background:linear-gradient(180deg,rgba(248,250,252,.92),rgba(255,255,255,.98));padding:1rem}
.report-suite .filter-label{display:block;margin-bottom:.45rem;color:#475569;font-size:.78rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.report-suite .kpi-card .metric-label{color:#64748b;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.report-suite .kpi-card h5{font-size:1.55rem;font-weight:800;letter-spacing:-.03em;color:#0f172a}
.report-suite .table-wrap{border:1px solid #e2e8f0;border-radius:20px;overflow:hidden;background:#fff}
</style>
@endsection

@section('content')
<div class="page-content report-suite">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="card hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <span class="hero-eyebrow">Access Analytics</span>
                        <h2 class="mt-3 mb-2">User Login Report</h2>
                        <p class="text-muted mb-0">Analyze login activity across web and app sessions using the same lighter reporting shell as the rest of the dashboard.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shell-card">
            <div class="card-header">
                <h5>User Login Report</h5>
            </div>

            <div class="card-body">
                <div class="filter-shell mb-3">
                <div class="row g-3 align-items-end">

                    <!-- User Filter -->
                    <div class="col-md-3">
                        <label class="filter-label">User</label>
                        <select id="user_id" class="form-control">
                            <option value="">-- All Users --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Start Date -->
                    <div class="col-md-3">
                        <label class="filter-label">Start Date</label>
                        <input type="date" id="start_date" class="form-control">
                    </div>

                    <!-- End Date -->
                    <div class="col-md-3">
                        <label class="filter-label">End Date</label>
                        <input type="date" id="end_date" class="form-control">
                    </div>

                    <!-- Buttons -->
                    <div class="col-md-3">
                        <label class="filter-label">&nbsp;</label>
                        <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-primary" id="filterBtn">
                            <i class="ri-search-line"></i> Filter
                        </button>
                        <button class="btn btn-light" id="resetBtn">
                            <i class="ri-refresh-line"></i> Reset
                        </button>
                        </div>
                    </div>

                </div>
                </div>

                <div class="row g-3 mb-3" id="kpi-row"></div>
                <div class="card chart-card border-0 mb-3">
                    <div class="card-header py-2"><strong id="chart-title">Web vs App Login</strong></div>
                    <div class="card-body">
                        <canvas id="report-chart" height="90"></canvas>
                    </div>
                </div>

                <!-- Datatable -->
                <div class="table-responsive table-wrap">
                <table id="user_login_table" class="table table-bordered table-striped align-middle w-100 mb-0">
                    <thead>
                        <tr>
                            <th>Sr No</th>
                            <th>User Name</th>
                            <th>Login Time</th>
                            <th>Logout Time</th>
                            <th>Web/ App</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function () {
    let chartInstance = null;

    let table = $('#user_login_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('reports.user_login_report') }}",
            data: function (d) {
                d.user_id    = $('#user_id').val();
                d.start_date = $('#start_date').val();
                d.end_date   = $('#end_date').val();
            }
        },
        columns: [
            { data: "DT_RowIndex", name: "DT_RowIndex", orderable: false },
            { data: "user_name", name: "user_name" },
            { data: "login_time", name: "login_time" },
            { data: "logout_time", name: "logout_time" },
            { data: "is_web_app_detail" , name:"is_web_app_detail"},
        ]
    });

    $('#filterBtn').click(function () {
        table.ajax.reload();
        loadSummary();
    });

    $('#resetBtn').click(function () {
        $('#user_id').val("");
        $('#start_date').val("");
        $('#end_date').val("");
        table.ajax.reload();
        loadSummary();
    });

    function loadSummary() {
        $.ajax({
            url: "{{ route('reports.user_login_report') }}",
            data: {
                summary: 1,
                user_id: $('#user_id').val(),
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val()
            },
            success: function (res) {
                renderKpis(res.kpis || []);
                renderChart(res.chart || {});
            }
        });
    }

    function renderKpis(kpis) {
        let html = '';
        (kpis || []).forEach(function (kpi) {
            html += `<div class="col-md-3"><div class="card kpi-card mb-0 border-0"><div class="card-body"><p class="metric-label mb-1">${kpi.label}</p><h5 class="mb-0">${kpi.value}</h5></div></div></div>`;
        });
        $('#kpi-row').html(html);
    }

    function renderChart(chart) {
        const ctx = document.getElementById('report-chart').getContext('2d');
        if (chartInstance) {
            chartInstance.destroy();
        }
        $('#chart-title').text(chart.title || 'Web vs App Login');
        chartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chart.labels || [],
                datasets: [{
                    data: chart.values || [],
                    backgroundColor: ['#0d6efd', '#198754']
                }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });
    }

    loadSummary();
});
</script>
@endsection
