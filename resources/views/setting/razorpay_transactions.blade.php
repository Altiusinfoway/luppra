@extends('layouts.app')

@section('page-css')
<style>
    .settings-suite {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
    }
    .settings-suite .hero-shell,
    .settings-suite .settings-shell {
        border: 1px solid rgba(255, 255, 255, 0.78);
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
    }
    .settings-suite .hero-shell {
        border-radius: 28px;
        background:
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
            radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
            linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
        margin-bottom: 1rem;
    }
    .settings-suite .settings-shell {
        border-radius: 22px;
    }
    .settings-suite .hero-eyebrow {
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
    .settings-suite .hero-title {
        font-size: clamp(2rem, 3vw, 2.7rem);
        line-height: 1.05;
        letter-spacing: -0.04em;
        font-weight: 800;
        margin: 1rem 0 .45rem;
        color: #0f172a;
    }
    .settings-suite .hero-subtitle {
        color: #64748b;
    }
    .settings-suite .summary-card {
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.84);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
    }
    .settings-suite .summary-card .label {
        display: block;
        margin-bottom: 8px;
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .settings-suite .summary-card h3 {
        margin: 0;
        font-size: 1.7rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #0f172a;
    }
    .settings-suite .toolbar-shell {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #f8fafc;
        padding: 14px 16px;
    }
    .settings-suite .table-wrap {
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        overflow: hidden;
        background: #fff;
    }
</style>
@endsection

@section('content')
<div class="page-content settings-suite">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="hero-shell">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-8">
                                <span class="hero-eyebrow">Payments</span>
                                <h1 class="hero-title">Razorpay Transactions</h1>
                                <p class="hero-subtitle mb-0">Review payment statuses, stale drafts, failed attempts, and gateway activity from the same refreshed admin shell as the settings area.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end gap-2">
                                    <a href="{{ route('setting.razorpay.index') }}" class="btn btn-sm btn-outline-primary">Gateway Settings</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-3"><div class="card summary-card"><div class="card-body"><span class="label">Total</span><h3>{{ $summary['total'] }}</h3></div></div></div>
            <div class="col-md-3"><div class="card summary-card"><div class="card-body"><span class="label">Draft</span><h3>{{ $summary['draft'] }}</h3></div></div></div>
            <div class="col-md-3"><div class="card summary-card"><div class="card-body"><span class="label">Submitted</span><h3>{{ $summary['order_created'] }}</h3></div></div></div>
            <div class="col-md-3"><div class="card summary-card"><div class="card-body"><span class="label">Failed</span><h3 class="text-danger">{{ $summary['failed'] }}</h3></div></div></div>
            <div class="col-md-3"><div class="card summary-card"><div class="card-body"><span class="label">Paid</span><h3 class="text-success">{{ $summary['paid'] }}</h3></div></div></div>
            <div class="col-md-3"><div class="card summary-card"><div class="card-body"><span class="label">Activated</span><h3 class="text-success">{{ $summary['activated'] }}</h3></div></div></div>
            <div class="col-md-3"><div class="card summary-card"><div class="card-body"><span class="label">Cancelled</span><h3 class="text-warning">{{ $summary['cancelled'] }}</h3></div></div></div>
            <div class="col-md-3"><div class="card summary-card"><div class="card-body"><span class="label">Pay Failed</span><h3 class="text-danger">{{ $summary['payment_failed'] }}</h3></div></div></div>
            <div class="col-md-3"><div class="card summary-card"><div class="card-body"><span class="label">Stale Draft</span><h3 class="text-warning">{{ $summary['stale_draft'] }}</h3></div></div></div>
            <div class="col-md-3"><div class="card summary-card"><div class="card-body"><span class="label">Stale Submitted</span><h3 class="text-warning">{{ $summary['stale_submitted'] }}</h3></div></div></div>
        </div>

        <div class="card settings-shell">
            <div class="card-header">
                <div class="toolbar-shell d-flex justify-content-between align-items-center flex-wrap gap-3">
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
            </div>
            <div class="card-body">
                <div class="table-responsive table-wrap">
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
</div>
@endsection
