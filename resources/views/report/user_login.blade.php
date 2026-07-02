@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <!-- Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4>User Login Report</h4>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card">
            <div class="card-header">
                <h5>User Login Report</h5>
            </div>

            <div class="card-body">
                <div class="row mb-3">

                    <!-- User Filter -->
                    <div class="col-md-3">
                        <label>User</label>
                        <select id="user_id" class="form-control">
                            <option value="">-- All Users --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Start Date -->
                    <div class="col-md-3">
                        <label>Start Date</label>
                        <input type="date" id="start_date" class="form-control">
                    </div>

                    <!-- End Date -->
                    <div class="col-md-3">
                        <label>End Date</label>
                        <input type="date" id="end_date" class="form-control">
                    </div>

                    <!-- Buttons -->
                    <div class="col-md-3">
                        <label>&nbsp;</label><br>
                        <button class="btn btn-primary" id="filterBtn">
                            <i class="ri-search-line"></i> Filter
                        </button>
                        <button class="btn btn-secondary" id="resetBtn">
                            <i class="ri-refresh-line"></i> Reset
                        </button>
                    </div>

                </div>

                <div class="row g-3 mb-3" id="kpi-row"></div>
                <div class="card border mb-3">
                    <div class="card-header py-2"><strong id="chart-title">Web vs App Login</strong></div>
                    <div class="card-body">
                        <canvas id="report-chart" height="90"></canvas>
                    </div>
                </div>

                <!-- Datatable -->
                <table id="user_login_table" class="table table-bordered table-striped align-middle w-100">
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
            html += `<div class="col-md-3"><div class="card mb-0"><div class="card-body"><p class="text-muted mb-1">${kpi.label}</p><h5 class="mb-0">${kpi.value}</h5></div></div></div>`;
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
