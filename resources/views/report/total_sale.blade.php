@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Total Sales</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('reports.total_sale') }}">Total Sales</a></li>
                            <li class="breadcrumb-item active">List</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Total Sales Report</h5>
                    </div>

                    <div class="card-body">

                        <!-- Date Filter -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label>Start Date</label>
                                <input type="date" id="start_date" class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label>End Date</label>
                                <input type="date" id="end_date" class="form-control">
                            </div>

                            <div class="col-md-3 mt-4">
                                <button class="btn btn-primary" id="filterBtn" style="margin-top:6px;">
                                    <i class="ri-search-line"></i> Filter
                                </button>

                                <button class="btn btn-secondary" id="resetBtn" style="margin-top:6px;">
                                    <i class="ri-refresh-line"></i> Reset
                                </button>
                            </div>
                        </div>

                        <div class="row g-3 mb-3" id="kpi-row"></div>
                        <div class="card border">
                            <div class="card-header py-2"><strong id="chart-title">Sales Trend</strong></div>
                            <div class="card-body">
                                <canvas id="report-chart" height="90"></canvas>
                            </div>
                        </div>

                        <table id="total_sales_list" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
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
            html += `<div class="col-md-4"><div class="card mb-0"><div class="card-body"><p class="text-muted mb-1">${kpi.label}</p><h5 class="mb-0">${kpi.value}</h5></div></div></div>`;
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
