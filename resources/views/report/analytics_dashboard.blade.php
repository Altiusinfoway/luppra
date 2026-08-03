@extends('layouts.app')

@section('page-css')
<style>
.report-suite{background:linear-gradient(180deg,rgba(248,250,252,.72) 0%,rgba(245,247,251,0) 100%)}
.report-suite .hero-shell,.report-suite .shell-card,.report-suite .chart-card,.report-suite .kpi-card{border:1px solid rgba(255,255,255,.8);border-radius:24px;background:rgba(255,255,255,.9);box-shadow:0 18px 40px rgba(15,23,42,.06)}
.report-suite .hero-shell{background:radial-gradient(circle at top right, rgba(99,102,241,.14), transparent 30%),radial-gradient(circle at left center, rgba(59,130,246,.12), transparent 30%),linear-gradient(135deg,#ffffff 0%,#f8fafc 100%)}
.report-suite .hero-eyebrow{display:inline-flex;align-items:center;padding:7px 12px;border-radius:999px;border:1px solid #c7d2fe;background:rgba(255,255,255,.86);color:#4338ca;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
</style>
@endsection

@section('content')
<div class="page-content report-suite">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <span class="hero-eyebrow">Dynamic Analytics</span>
                                <h2 class="mt-3 mb-2">{{ $reportTitle }}</h2>
                                <p class="text-muted mb-0">Switch reporting periods, inspect KPI shifts, and review detailed trends from a unified analytics surface.</p>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="javascript:void(0)">Reports</a></li>
                                        <li class="breadcrumb-item active">{{ $reportTitle }}</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shell-card mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label mb-1">Period</label>
                        <select class="form-select" id="period-filter">
                            <option value="yearly">Yearly</option>
                            <option value="monthly" selected>Monthly</option>
                            <option value="weekly">Weekly</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-1">Reference Date</label>
                        <input type="date" class="form-control" id="reference-date">
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary" id="apply-report-filter">Apply</button>
                    </div>
                    <div class="col-md-3 text-md-end text-muted" id="period-label"></div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3" id="kpi-row"></div>

        <div class="card chart-card border-0 mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0" id="chart-title">Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="report-chart" height="110"></canvas>
            </div>
        </div>

        <div class="card shell-card">
            <div class="card-header">
                <h5 class="card-title mb-0">Detailed Listing</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0" id="report-table">
                        <thead></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function () {
        const endpoint = @json($reportEndpoint);
        const periodEl = document.getElementById('period-filter');
        const dateEl = document.getElementById('reference-date');
        const applyBtn = document.getElementById('apply-report-filter');
        const kpiRow = document.getElementById('kpi-row');
        const periodLabel = document.getElementById('period-label');
        const chartTitle = document.getElementById('chart-title');
        const tableHead = document.querySelector('#report-table thead');
        const tableBody = document.querySelector('#report-table tbody');
        const ctx = document.getElementById('report-chart').getContext('2d');
        let chartInstance = null;

        dateEl.value = new Date().toISOString().split('T')[0];

        function renderKpis(kpis) {
            kpiRow.innerHTML = '';
            (kpis || []).forEach(function (kpi) {
                const col = document.createElement('div');
                col.className = 'col-md-3';
                col.innerHTML = `
                    <div class="card kpi-card mb-0 border-0">
                        <div class="card-body">
                            <p class="text-muted mb-1">${escapeHtml(kpi.label || '')}</p>
                            <h5 class="mb-0">${escapeHtml(kpi.value || '0')}</h5>
                        </div>
                    </div>
                `;
                kpiRow.appendChild(col);
            });
        }

        function renderTable(table) {
            const columns = table?.columns || [];
            const rows = table?.rows || [];

            tableHead.innerHTML = '';
            tableBody.innerHTML = '';

            const trHead = document.createElement('tr');
            columns.forEach(function (col) {
                const th = document.createElement('th');
                th.textContent = col.label || '';
                trHead.appendChild(th);
            });
            tableHead.appendChild(trHead);

            rows.forEach(function (row) {
                const tr = document.createElement('tr');
                columns.forEach(function (col) {
                    const td = document.createElement('td');
                    td.textContent = row[col.key] ?? '';
                    tr.appendChild(td);
                });
                tableBody.appendChild(tr);
            });
        }

        function renderChart(chartData) {
            if (chartInstance) {
                chartInstance.destroy();
            }

            chartTitle.textContent = chartData?.title || 'Trend';

            chartInstance = new Chart(ctx, {
                type: chartData?.type || 'line',
                data: {
                    labels: chartData?.labels || [],
                    datasets: [{
                        label: chartData?.title || 'Value',
                        data: chartData?.values || [],
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.18)',
                        borderWidth: 2,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        function loadReport() {
            const params = new URLSearchParams({
                period: periodEl.value,
                reference_date: dateEl.value
            });

            fetch(endpoint + '?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                periodLabel.textContent = data.period_label || '';
                renderKpis(data.kpis || []);
                renderChart(data.chart || {});
                renderTable(data.table || { columns: [], rows: [] });
            })
            .catch(function () {
                periodLabel.textContent = 'Failed to load report';
            });
        }

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        applyBtn.addEventListener('click', loadReport);
        loadReport();
    })();
</script>
@endsection
