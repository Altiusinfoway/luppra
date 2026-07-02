@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Customer Ledger</h4>
                    <p class="text-muted mb-0">{{ $customer->company_name ?: $customer->name }}</p>
                </div>
                <a href="{{ route('accounts.customers') }}" class="btn btn-outline-primary btn-sm">Back to Customer Dashboard</a>
            </div>
        </div>

        <div class="row g-3 mb-2">
            <div class="col-md-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Total Due</div>
                    <h5 class="mb-0 text-danger">&#8377;{{ number_format((float)($customer->due_amount ?? 0), 2) }}</h5>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Total Paid</div>
                    <h5 class="mb-0 text-success">&#8377;{{ number_format((float)($customer->paid_amount ?? 0), 2) }}</h5>
                </div></div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="card-title mb-0">Invoices</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Invoice/Order No</th>
                                <th>Date</th>
                                <th class="text-end">Grand Total</th>
                                <th class="text-end">Remaining</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td>{{ str_replace('ORDER-', 'INVOICE-', $order->order_number) }}</td>
                                    <td>{{ \App\Models\Utility::getDateFormated($order->date) }}</td>
                                    <td class="text-end">&#8377;{{ number_format((float)$order->grand_total, 2) }}</td>
                                    <td class="text-end {{ (float)$order->remaining_payment > 0 ? 'text-danger' : 'text-success' }}">
                                        &#8377;{{ number_format((float)$order->remaining_payment, 2) }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                            {{ strtoupper((string)$order->payment_status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">No invoices found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0">Payment Ledger (How customer paid)</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Payment Mode</th>
                                <th>Transaction Ref</th>
                                <th>Bank</th>
                                <th>Description/Source</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payment_rows as $row)
                                <tr>
                                    <td>{{ \App\Models\Utility::getDateFormated($row['payment_date']) }}</td>
                                    <td>
                                        <span class="badge {{ $row['payment_type'] === 'credit' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                            {{ strtoupper($row['payment_type']) }}
                                        </span>
                                    </td>
                                    <td>{{ strtoupper($row['payment_method'] ?: '-') }}</td>
                                    <td>{{ $row['transaction_id'] ?: '-' }}</td>
                                    <td>{{ $row['bank_name'] ?: '-' }}</td>
                                    <td>{{ $row['description'] ?: '-' }}</td>
                                    <td class="text-end">&#8377;{{ number_format((float)$row['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">No payments found for this customer.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

