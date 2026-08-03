@extends('layouts.app')

@section('page-css')
<style>
    .accounts-customers-suite {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
    }
    .accounts-customers-suite .hero-shell,
    .accounts-customers-suite .surface-card {
        border: 1px solid rgba(255, 255, 255, 0.78);
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
    }
    .accounts-customers-suite .hero-shell {
        border-radius: 28px;
        background:
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
            radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
            linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
        margin-bottom: 1rem;
    }
    .accounts-customers-suite .surface-card {
        border-radius: 22px;
    }
    .accounts-customers-suite .hero-eyebrow {
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
    .accounts-customers-suite .hero-title {
        font-size: clamp(2rem, 3vw, 2.7rem);
        line-height: 1.05;
        letter-spacing: -0.04em;
        font-weight: 800;
        margin: 1rem 0 .45rem;
        color: #0f172a;
    }
    .accounts-customers-suite .hero-subtitle {
        color: #64748b;
    }
    .accounts-customers-suite .filter-panel {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #f8fafc;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    .accounts-customers-suite .summary-strip {
        color: #64748b;
        font-size: 0.84rem;
    }
    .accounts-customers-suite .table-shell {
        border: 1px solid #dce4ee;
        border-radius: 20px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.04);
    }
    .accounts-customers-suite .table-shell table {
        margin-bottom: 0;
    }
    .accounts-customers-suite .table-shell thead th {
        background: #f8fafc;
        color: #334155;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .06em;
        border-bottom-color: #dce4ee;
    }
    .accounts-customers-suite .table-shell tbody tr {
        transition: background-color .18s ease, transform .18s ease;
    }
    .accounts-customers-suite .table-shell tbody tr:hover {
        background: #f8fbff;
    }
    .accounts-customers-suite .table-shell tbody tr:hover td {
        background: #f8fbff;
    }
    .accounts-customers-suite .customer-name {
        font-weight: 700;
        color: #0f172a;
    }
    .accounts-customers-suite .receivable-pill {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        min-width: 110px;
        padding: 6px 10px;
        border-radius: 999px;
        font-weight: 700;
        background: #f1f5f9;
        color: #0f172a;
    }
    .accounts-customers-suite .receivable-pill.is-due {
        background: #fef2f2;
        color: #b91c1c;
    }
    .accounts-customers-suite .receivable-pill.is-clear {
        background: #f0fdf4;
        color: #15803d;
    }
    .accounts-customers-suite .table-summary {
        border-top: 1px solid #e2e8f0;
        margin-top: 1rem;
        padding-top: 1rem;
    }
    .accounts-customer-pagination .pagination {
        margin-bottom: 0;
        gap: 4px;
        align-items: center;
    }
    .accounts-customer-pagination .page-item .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        line-height: 1.05;
        min-width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        border-radius: 6px;
    }
    .accounts-customer-pagination .page-item:first-child .page-link,
    .accounts-customer-pagination .page-item:last-child .page-link {
        min-width: 64px;
    }
</style>
@endsection

@section('content')
<div class="page-content accounts-customers-suite">
    <div class="container-fluid">
        <div class="hero-shell">
            <div class="card-body p-4 p-lg-5">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <span class="hero-eyebrow">Customer Finance</span>
                        <h1 class="hero-title">Customer Accounts Dashboard</h1>
                        <p class="hero-subtitle mb-0">Review receivables, collections, and ledger access with the same structured dashboard style used across the refreshed finance module.</p>
                    </div>
                    <div class="col-lg-4 d-flex justify-content-lg-end">
                        <a href="{{ route('accounts.index') }}" class="btn btn-outline-primary btn-sm">Back to Accounts Dashboard</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card surface-card">
            <div class="card-body">
                <div class="filter-panel">
                    <form method="GET" action="{{ route('accounts.customers') }}" class="row g-2">
                        <div class="col-md-4">
                        <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Search customer name...">
                        </div>
                        <div class="col-md-2">
                        <select name="due_filter" class="form-select">
                            <option value="all" {{ ($filters['due_filter'] ?? 'all') === 'all' ? 'selected' : '' }}>All Due Status</option>
                            <option value="due" {{ ($filters['due_filter'] ?? '') === 'due' ? 'selected' : '' }}>Due Customers</option>
                            <option value="clear" {{ ($filters['due_filter'] ?? '') === 'clear' ? 'selected' : '' }}>No Due</option>
                        </select>
                        </div>
                        <div class="col-md-2">
                        <select name="payment_filter" class="form-select">
                            <option value="all" {{ ($filters['payment_filter'] ?? 'all') === 'all' ? 'selected' : '' }}>All Payments</option>
                            <option value="last_30_days" {{ ($filters['payment_filter'] ?? '') === 'last_30_days' ? 'selected' : '' }}>Paid in Last 30 Days</option>
                            <option value="no_payment" {{ ($filters['payment_filter'] ?? '') === 'no_payment' ? 'selected' : '' }}>No Payment Yet</option>
                        </select>
                        </div>
                        <div class="col-md-2">
                        <select name="sort_by" class="form-select">
                            <option value="receivable_desc" {{ ($filters['sort_by'] ?? 'receivable_desc') === 'receivable_desc' ? 'selected' : '' }}>Sort: Highest Due</option>
                            <option value="collected_desc" {{ ($filters['sort_by'] ?? '') === 'collected_desc' ? 'selected' : '' }}>Sort: Highest Collected</option>
                            <option value="invoiced_desc" {{ ($filters['sort_by'] ?? '') === 'invoiced_desc' ? 'selected' : '' }}>Sort: Highest Invoiced</option>
                            <option value="name_asc" {{ ($filters['sort_by'] ?? '') === 'name_asc' ? 'selected' : '' }}>Sort: Name A-Z</option>
                        </select>
                        </div>
                        <div class="col-md-1">
                        <select name="per_page" class="form-select">
                            <option value="10" {{ (int)($filters['per_page'] ?? 10) === 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ (int)($filters['per_page'] ?? 10) === 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ (int)($filters['per_page'] ?? 10) === 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ (int)($filters['per_page'] ?? 10) === 100 ? 'selected' : '' }}>100</option>
                        </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">Apply</button>
                        <a href="{{ route('accounts.customers') }}" class="btn btn-light w-100">Reset</a>
                        </div>
                    </form>
                </div>

                <div class="table-shell">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th class="text-end">Total Invoiced</th>
                                    <th class="text-end">Collected</th>
                                    <th class="text-end">Receivable</th>
                                    <th>Last Payment</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                    <tr>
                                        <td>
                                            <div class="customer-name">{{ $row['name'] }}</div>
                                        </td>
                                        <td class="text-end">&#8377;{{ number_format((float)$row['total_invoiced'], 2) }}</td>
                                        <td class="text-end text-success fw-semibold">&#8377;{{ number_format((float)$row['total_collected'], 2) }}</td>
                                        <td class="text-end">
                                            <span class="receivable-pill {{ (float)$row['receivable'] > 0 ? 'is-due' : 'is-clear' }}">
                                                &#8377;{{ number_format((float)$row['receivable'], 2) }}
                                            </span>
                                        </td>
                                        <td>{{ $row['last_payment_date'] ? \App\Models\Utility::getDateFormated($row['last_payment_date']) : '-' }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('accounts.customers.ledger', $row['id']) }}" class="btn btn-sm btn-primary">View Ledger</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No customers found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="table-summary d-flex justify-content-between align-items-center mt-3">
                    <small class="summary-strip">
                        Showing {{ $rows->firstItem() ?? 0 }} to {{ $rows->lastItem() ?? 0 }} of {{ $rows->total() }} customers
                    </small>
                    <div class="accounts-customer-pagination">
                        {{ $rows->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
