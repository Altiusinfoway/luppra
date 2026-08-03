@extends('layouts.app')

@section('page-css')
<style>
    .accounts-suite {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
    }
    .accounts-suite .hero-shell {
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 28px;
        background:
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
            radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
            linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
        margin-bottom: 1.25rem;
    }
    .accounts-suite .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.76);
        border: 1px solid #dbeafe;
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .accounts-suite .hero-title {
        font-size: clamp(2rem, 3vw, 2.7rem);
        line-height: 1.05;
        letter-spacing: -0.04em;
        font-weight: 800;
        margin: 1rem 0 .45rem;
        color: #0f172a;
    }
    .accounts-suite .hero-subtitle {
        color: #64748b;
        max-width: 720px;
        font-size: .98rem;
    }
    .accounts-suite .hero-action-btn {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border-radius: 14px;
        font-weight: 700;
        padding: .7rem 1rem;
        border: 1px solid #dbeafe;
        background: rgba(255, 255, 255, 0.92);
        color: #0f172a;
        transition: all .18s ease-in-out;
    }
    .accounts-suite .hero-action-btn:hover {
        transform: translateY(-1px);
        background: #ffffff;
        color: #0f172a;
    }
    .accounts-suite .hero-action-primary {
        background: linear-gradient(135deg, #0f766e 0%, #2563eb 100%);
        border-color: transparent;
        color: #ffffff;
    }
    .accounts-suite .hero-action-primary:hover {
        color: #ffffff;
    }
    .accounts-suite .summary-card {
        height: 100%;
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.88);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
    }
    .accounts-suite .summary-card .label {
        color: #64748b;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: .08em;
        font-weight: 800;
        margin-bottom: .45rem;
    }
    .accounts-suite .summary-card h3 {
        margin: 0;
        font-size: 1.7rem;
        line-height: 1.1;
        letter-spacing: -0.03em;
        font-weight: 800;
        color: #0f172a;
    }
    .accounts-suite .analytics-card {
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
        overflow: hidden;
        height: 100%;
    }
    .accounts-suite .analytics-card .card-header {
        background: transparent;
        border-bottom: 1px solid #e2e8f0;
        padding: 1rem 1.25rem;
    }
    .accounts-suite .analytics-card .card-title {
        color: #0f172a;
        font-weight: 700;
    }
    .accounts-suite .analytics-card .card-body {
        padding: 1.1rem 1.25rem 1.25rem;
    }
    .accounts-suite .table-shell {
        border: 1px solid #dce4ee;
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
    }
    .accounts-suite .table-shell table {
        margin-bottom: 0;
    }
    .accounts-suite .table-shell thead th {
        background: #f8fafc;
        color: #334155;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .06em;
        border-bottom-color: #dce4ee;
    }
    .accounts-suite .table-shell tbody tr:hover td {
        background: #f8fbff;
    }
    .accounts-suite .amount-pill {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        min-width: 96px;
        padding: 5px 10px;
        border-radius: 999px;
        font-weight: 700;
        background: #f1f5f9;
        color: #0f172a;
    }
    .accounts-suite .amount-pill.is-credit {
        background: #f0fdf4;
        color: #15803d;
    }
    .accounts-suite .amount-pill.is-debit {
        background: #fef2f2;
        color: #b91c1c;
    }
</style>
@endsection

@section('content')
<div class="page-content accounts-suite">
    <div class="container-fluid">
        <div class="hero-shell">
            <div class="card-body p-4 p-lg-5">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <span class="hero-eyebrow">Finance Dashboard</span>
                        <h1 class="hero-title">Accounts Dashboard</h1>
                        <p class="hero-subtitle mb-0">Track invoicing, cashflow, receivables, and payables with the same clean overview style used across the refreshed app.</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="d-flex justify-content-lg-end flex-wrap gap-2">
                            <a href="{{ route('accounts.customers') }}" class="hero-action-btn">Customer Dashboard</a>
                            <a href="{{ route('invoices.index') }}" class="hero-action-btn hero-action-primary">View Invoices</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-2">
            <div class="col-md-3">
                <div class="summary-card"><div class="card-body">
                    <div class="label">Total Invoiced</div>
                    <h3>&#8377;{{ number_format((float)($kpis['total_invoiced'] ?? 0), 2) }}</h3>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="summary-card"><div class="card-body">
                    <div class="label">Collected</div>
                    <h3 class="text-success">&#8377;{{ number_format((float)($kpis['total_collected'] ?? 0), 2) }}</h3>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="summary-card"><div class="card-body">
                    <div class="label">Paid Out</div>
                    <h3 class="text-danger">&#8377;{{ number_format((float)($kpis['total_paid_out'] ?? 0), 2) }}</h3>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="summary-card"><div class="card-body">
                    <div class="label">Net Cashflow</div>
                    @php($net = (float)($kpis['net_cashflow'] ?? 0))
                    <h3 class="{{ $net >= 0 ? 'text-success' : 'text-danger' }}">&#8377;{{ number_format($net, 2) }}</h3>
                </div></div>
            </div>
        </div>

        <div class="row g-3 mb-2">
            <div class="col-md-3">
                <div class="summary-card"><div class="card-body">
                    <div class="label">Receivables</div>
                    <h3>&#8377;{{ number_format((float)($kpis['receivable_total'] ?? 0), 2) }}</h3>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="summary-card"><div class="card-body">
                    <div class="label">Payables</div>
                    <h3>&#8377;{{ number_format((float)($kpis['payable_total'] ?? 0), 2) }}</h3>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="summary-card"><div class="card-body">
                    <div class="label">Invoice Outstanding</div>
                    <h3>&#8377;{{ number_format((float)($kpis['invoice_outstanding'] ?? 0), 2) }}</h3>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="summary-card"><div class="card-body">
                    <div class="label">Overdue (30+ Days)</div>
                    <h3 class="text-danger">&#8377;{{ number_format((float)($kpis['overdue_amount'] ?? 0), 2) }}</h3>
                </div></div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="analytics-card">
                    <div class="card-header"><h6 class="card-title mb-0">Cashflow Trend (Last 6 Months)</h6></div>
                    <div class="card-body">
                        <canvas id="accountsCashflowChart" height="130"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="analytics-card">
                    <div class="card-header"><h6 class="card-title mb-0">Recent Transactions</h6></div>
                    <div class="card-body">
                        <div class="table-shell">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Party</th>
                                    <th>Type</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($recent_transactions as $trx)
                                    <tr>
                                        <td>{{ \App\Models\Utility::getDateFormated($trx['payment_date']) }}</td>
                                        <td>{{ $trx['entity_name'] }}</td>
                                        <td>
                                            <span class="badge {{ $trx['payment_type'] === 'credit' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                {{ strtoupper($trx['payment_type']) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="amount-pill {{ $trx['payment_type'] === 'credit' ? 'is-credit' : 'is-debit' }}">
                                                &#8377;{{ number_format((float)$trx['amount'], 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">No transactions found.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-lg-6">
                <div class="analytics-card">
                    <div class="card-header"><h6 class="card-title mb-0">Top Receivables</h6></div>
                    <div class="card-body">
                        <div class="table-shell">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                <thead><tr><th>Customer</th><th class="text-end">Due</th><th class="text-end">Paid</th></tr></thead>
                                <tbody>
                                @forelse($top_receivables as $row)
                                    <tr>
                                        <td>{{ $row->company_name ?: $row->name }}</td>
                                        <td class="text-end"><span class="amount-pill is-debit">&#8377;{{ number_format((float)$row->due_amount, 2) }}</span></td>
                                        <td class="text-end"><span class="amount-pill is-credit">&#8377;{{ number_format((float)$row->paid_amount, 2) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">No due customers.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="analytics-card">
                    <div class="card-header"><h6 class="card-title mb-0">Top Payables</h6></div>
                    <div class="card-body">
                        <div class="table-shell">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                <thead><tr><th>Vendor</th><th class="text-end">Due</th><th class="text-end">Paid</th></tr></thead>
                                <tbody>
                                @forelse($top_payables as $row)
                                    <tr>
                                        <td>{{ $row->company_name ?: $row->name }}</td>
                                        <td class="text-end"><span class="amount-pill is-debit">&#8377;{{ number_format((float)$row->due_amount, 2) }}</span></td>
                                        <td class="text-end"><span class="amount-pill is-credit">&#8377;{{ number_format((float)$row->paid_amount, 2) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">No due vendors.</td></tr>
                                @endforelse
                                </tbody>
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

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(() => {
    const el = document.getElementById('accountsCashflowChart');
    if (!el) return;

    const labels = @json($cashflow_labels);
    const credit = @json($cashflow_credit);
    const debit = @json($cashflow_debit);

    new Chart(el, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Credit', data: credit, backgroundColor: '#22c55e' },
                { label: 'Debit', data: debit, backgroundColor: '#ef4444' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: function(value) { return 'Rs ' + value; } }
                }
            }
        }
    });
})();
</script>
@endsection
