@extends('layouts.app')

@section('page-css')
<style>
.finance-report-suite {
    background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
}

.finance-report-suite .hero-shell,
.finance-report-suite .shell-card,
.finance-report-suite .kpi-card {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
}

.finance-report-suite .hero-shell {
    background:
        radial-gradient(circle at top right, rgba(16, 185, 129, 0.14), transparent 30%),
        radial-gradient(circle at left center, rgba(239, 68, 68, 0.12), transparent 30%),
        linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}

.finance-report-suite .hero-eyebrow {
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

.finance-report-suite .kpi-card .metric-label {
    color: #64748b;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.finance-report-suite .kpi-card h5 {
    font-size: 1.55rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: #0f172a;
}

.finance-report-suite .table-wrap {
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    overflow: hidden;
    background: #fff;
}
</style>
@endsection

@section('content')
<div class="page-content finance-report-suite">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <span class="hero-eyebrow">Finance Overview</span>
                                <h2 class="mt-3 mb-2">Income & Expense Report</h2>
                                <p class="text-muted mb-0">Compare revenue and payroll costs with a cleaner executive summary view that matches the refreshed admin experience.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card kpi-card mb-0 border-0">
                    <div class="card-body">
                        <p class="metric-label mb-1">Total Income</p>
                        <h5 class="mb-0">Rs. {{ number_format($total_income, 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card kpi-card mb-0 border-0">
                    <div class="card-body">
                        <p class="metric-label mb-1">Total Expense</p>
                        <h5 class="mb-0">Rs. {{ number_format($total_expense, 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card kpi-card mb-0 border-0">
                    <div class="card-body">
                        <p class="metric-label mb-1">Net Profit</p>
                        <h5 class="mb-0 {{ $net_profit >= 0 ? 'text-success' : 'text-danger' }}">Rs. {{ number_format($net_profit, 2) }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shell-card mb-3">
            <div class="card-header"><h5 class="card-title mb-0">Income vs Expense</h5></div>
            <div class="card-body">
                <canvas id="incomeExpenseChart" height="90"></canvas>
            </div>
        </div>

        <div class="card shell-card">
            <div class="card-header"><h5 class="card-title mb-0">Detailed Summary</h5></div>
            <div class="card-body">
                <div class="table-responsive table-wrap">
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
