@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Razorpay Transactions</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('setting.razorpay.index') }}" class="btn btn-sm btn-outline-primary">Gateway Settings</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Total</div><h5 class="mb-0">{{ $summary['total'] }}</h5></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Draft (Half Form)</div><h5 class="mb-0">{{ $summary['draft'] }}</h5></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Submitted, Not Paid</div><h5 class="mb-0">{{ $summary['order_created'] }}</h5></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Failed</div><h5 class="mb-0 text-danger">{{ $summary['failed'] }}</h5></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Paid</div><h5 class="mb-0 text-success">{{ $summary['paid'] }}</h5></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Activated</div><h5 class="mb-0 text-success">{{ $summary['activated'] }}</h5></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Cancelled</div><h5 class="mb-0 text-warning">{{ $summary['cancelled'] }}</h5></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Payment Failed</div><h5 class="mb-0 text-danger">{{ $summary['payment_failed'] }}</h5></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Stale Draft (&gt;1h)</div><h5 class="mb-0 text-warning">{{ $summary['stale_draft'] }}</h5></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Stale Submitted (&gt;1h)</div><h5 class="mb-0 text-warning">{{ $summary['stale_submitted'] }}</h5></div></div></div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">All Records</h5>
                <form method="GET" class="d-flex gap-2">
                    <select class="form-select form-select-sm" name="status">
                        <option value="">All Status</option>
                        @foreach(['draft','pending','order_created','paid','activated','failed','cancelled','payment_failed'] as $st)
                            <option value="{{ $st }}" {{ $status === $st ? 'selected' : '' }}>{{ strtoupper($st) }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-primary" type="submit">Filter</button>
                </form>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Company</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment Ref</th>
                            <th>Issue</th>
                            <th>Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            @php $meta = (array) $row->meta; @endphp
                            <tr>
                                <td>#{{ $row->id }}</td>
                                <td>{{ $row->name }}</td>
                                <td>{{ $row->email }}</td>
                                <td>{{ $row->phone ?: '-' }}</td>
                                <td>{{ $row->company_name }}</td>
                                <td>{{ $row->plan_id }}</td>
                                <td>{{ number_format((float) $row->amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ strtoupper($row->status) }}</span>
                                </td>
                                <td>{{ $row->razorpay_payment_id ?: $row->razorpay_order_id ?: '-' }}</td>
                                <td class="small text-danger">
                                    {{ $meta['activation_error'] ?? ($meta['verify'] ?? '-') }}
                                </td>
                                <td>{{ $row->updated_at }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="text-center text-muted">No transaction records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $rows->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
