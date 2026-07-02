@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Income & Expense Report</h4>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card mb-0">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Income</p>
                        <h5 class="mb-0">Rs. {{ number_format($total_income, 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-0">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Expense</p>
                        <h5 class="mb-0">Rs. {{ number_format($total_expense, 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-0">
                    <div class="card-body">
                        <p class="text-muted mb-1">Net Profit</p>
                        <h5 class="mb-0 {{ $net_profit >= 0 ? 'text-success' : 'text-danger' }}">Rs. {{ number_format($net_profit, 2) }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h5 class="card-title mb-0">Income vs Expense</h5></div>
            <div class="card-body">
                <canvas id="incomeExpenseChart" height="90"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Detailed Summary</h5></div>
            <div class="card-body">
                <table class="table table-bordered table-striped mb-0">
                    <tr>
                        <th>Total Income (Sales)</th>
                        <td>Rs. {{ number_format($total_income, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Total Expense (Salary)</th>
                        <td>Rs. {{ number_format($total_expense, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Net Profit</th>
                        <td class="{{ $net_profit >= 0 ? 'text-success' : 'text-danger' }}">
                            Rs. {{ number_format($net_profit, 2) }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ieCtx = document.getElementById('incomeExpenseChart').getContext('2d');
    new Chart(ieCtx, {
        type: 'bar',
        data: {
            labels: ['Income', 'Expense'],
            datasets: [{
                data: [{{ (float) $total_income }}, {{ (float) $total_expense }}],
                backgroundColor: ['rgba(25,135,84,0.25)', 'rgba(220,53,69,0.25)'],
                borderColor: ['#198754', '#dc3545'],
                borderWidth: 2
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
</script>
@endsection

