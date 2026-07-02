@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Accounts Dashboard</h4>
                    <p class="text-muted mb-0">Track invoicing, cashflow, receivables and payables in one place.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('accounts.customers') }}" class="btn btn-outline-secondary btn-sm">Customer Dashboard</a>
                    {{-- <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm">Add Transaction</a> --}}
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-primary btn-sm">View Invoices</a>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-2">
            <div class="col-md-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Total Invoiced</div>
                    <h5 class="mb-0">&#8377;{{ number_format((float)($kpis['total_invoiced'] ?? 0), 2) }}</h5>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Collected</div>
                    <h5 class="mb-0 text-success">&#8377;{{ number_format((float)($kpis['total_collected'] ?? 0), 2) }}</h5>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Paid Out</div>
                    <h5 class="mb-0 text-danger">&#8377;{{ number_format((float)($kpis['total_paid_out'] ?? 0), 2) }}</h5>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Net Cashflow</div>
                    @php($net = (float)($kpis['net_cashflow'] ?? 0))
                    <h5 class="mb-0 {{ $net >= 0 ? 'text-success' : 'text-danger' }}">&#8377;{{ number_format($net, 2) }}</h5>
                </div></div>
            </div>
        </div>

        <div class="row g-3 mb-2">
            <div class="col-md-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Receivables</div>
                    <h5 class="mb-0">&#8377;{{ number_format((float)($kpis['receivable_total'] ?? 0), 2) }}</h5>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Payables</div>
                    <h5 class="mb-0">&#8377;{{ number_format((float)($kpis['payable_total'] ?? 0), 2) }}</h5>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Invoice Outstanding</div>
                    <h5 class="mb-0">&#8377;{{ number_format((float)($kpis['invoice_outstanding'] ?? 0), 2) }}</h5>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Overdue (30+ Days)</div>
                    <h5 class="mb-0 text-danger">&#8377;{{ number_format((float)($kpis['overdue_amount'] ?? 0), 2) }}</h5>
                </div></div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header"><h6 class="card-title mb-0">Cashflow Trend (Last 6 Months)</h6></div>
                    <div class="card-body">
                        <canvas id="accountsCashflowChart" height="130"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header"><h6 class="card-title mb-0">Recent Transactions</h6></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
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
                                        <td class="text-end">&#8377;{{ number_format((float)$trx['amount'], 2) }}</td>
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

        <div class="row g-3 mt-1">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header"><h6 class="card-title mb-0">Top Receivables</h6></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead><tr><th>Customer</th><th class="text-end">Due</th><th class="text-end">Paid</th></tr></thead>
                                <tbody>
                                @forelse($top_receivables as $row)
                                    <tr>
                                        <td>{{ $row->company_name ?: $row->name }}</td>
                                        <td class="text-end text-danger">&#8377;{{ number_format((float)$row->due_amount, 2) }}</td>
                                        <td class="text-end text-success">&#8377;{{ number_format((float)$row->paid_amount, 2) }}</td>
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
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header"><h6 class="card-title mb-0">Top Payables</h6></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead><tr><th>Vendor</th><th class="text-end">Due</th><th class="text-end">Paid</th></tr></thead>
                                <tbody>
                                @forelse($top_payables as $row)
                                    <tr>
                                        <td>{{ $row->company_name ?: $row->name }}</td>
                                        <td class="text-end text-danger">&#8377;{{ number_format((float)$row->due_amount, 2) }}</td>
                                        <td class="text-end text-success">&#8377;{{ number_format((float)$row->paid_amount, 2) }}</td>
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
