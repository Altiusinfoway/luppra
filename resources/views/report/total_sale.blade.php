@extends('layouts.app')

@section('page-css')
<style>
.report-suite {
    background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
}

.report-suite .hero-shell,
.report-suite .shell-card,
.report-suite .chart-card,
.report-suite .kpi-card {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
}

.report-suite .hero-shell {
    background:
        radial-gradient(circle at top right, rgba(16, 185, 129, 0.14), transparent 30%),
        radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
        linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}

.report-suite .hero-eyebrow {
    display: inline-flex;
    align-items: center;
    padding: 7px 12px;
    border-radius: 999px;
    border: 1px solid #d1fae5;
    background: rgba(255, 255, 255, 0.86);
    color: #047857;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.report-suite .filter-shell {
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    background: linear-gradient(180deg, rgba(248, 250, 252, 0.92), rgba(255, 255, 255, 0.98));
    padding: 1rem;
}

.report-suite .filter-label {
    display: block;
    margin-bottom: .45rem;
    color: #475569;
    font-size: .78rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.report-suite .kpi-card .metric-label {
    color: #64748b;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.report-suite .kpi-card h5 {
    font-size: 1.55rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: #0f172a;
}

.report-suite .chart-card .card-header,
.report-suite .shell-card .card-header {
    background: transparent !important;
}

.report-suite .table-wrap {
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    overflow: hidden;
    background: #fff;
}
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
                                <span class="hero-eyebrow">Revenue Analytics</span>
                                <h2 class="mt-3 mb-2">Total Sales</h2>
                                <p class="text-muted mb-0">Track sales volume, review trendlines, and filter revenue activity from a cleaner analytics dashboard.</p>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('reports.total_sale') }}">Total Sales</a></li>
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

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Total Sales Report</h5>
                    </div>

                    <div class="card-body">

                        <div class="filter-shell mb-3">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="filter-label">Start Date</label>
                                <input type="date" id="start_date" class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label class="filter-label">End Date</label>
                                <input type="date" id="end_date" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex gap-2 flex-wrap justify-content-md-end">
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
                        <div class="card chart-card border-0">
                            <div class="card-header py-2"><strong id="chart-title">Sales Trend</strong></div>
                            <div class="card-body">
                                <canvas id="report-chart" height="90"></canvas>
                            </div>
                        </div>

                        <div class="table-responsive table-wrap mt-3">
                        <table id="total_sales_list" class="table table-bordered dt-responsive nowrap table-striped align-middle mb-0" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Sr No</th>
                                    <th>Customer Name</th>
                                    <th>Order Number</th>
                                    <th>Grand Total</th>
                                    <th>Date</th>
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

    let table = $('#total_sales_list').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('reports.total_sale') }}",
            data: function (d) {
                d.start_date = $('#start_date').val();
                d.end_date   = $('#end_date').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'order_number', name: 'order_number' },
            { data: 'grand_total', name: 'grand_total' },
            { data: 'date', name: 'date' },
        ]
    });

    // Filter button
    $('#filterBtn').click(function () {
        table.ajax.reload();
        loadSummary();
    });

    // Reset button
    $('#resetBtn').click(function () {
        $('#start_date').val('');
        $('#end_date').val('');
        table.ajax.reload();
        loadSummary();
    });

    function loadSummary() {
        $.ajax({
            url: "{{ route('reports.total_sale') }}",
            data: {
                summary: 1,
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
            html += `<div class="col-md-4"><div class="card kpi-card mb-0 border-0"><div class="card-body"><p class="metric-label mb-1">${kpi.label}</p><h5 class="mb-0">${kpi.value}</h5></div></div></div>`;
        });
        $('#kpi-row').html(html);
    }

    function renderChart(chart) {
        const ctx = document.getElementById('report-chart').getContext('2d');
        if (chartInstance) {
            chartInstance.destroy();
        }
        $('#chart-title').text(chart.title || 'Sales Trend');
        chartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chart.labels || [],
                datasets: [{
                    data: chart.values || [],
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25,135,84,0.2)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    loadSummary();
});
</script>
@endsection
