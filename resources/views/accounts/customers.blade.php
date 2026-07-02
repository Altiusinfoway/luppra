@extends('layouts.app')

@section('page-css')
<style>
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
<div class="page-content">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Customer Accounts Dashboard</h4>
                    <p class="text-muted mb-0">Customer-wise receivables, collections and ledger access.</p>
                </div>
                <a href="{{ route('accounts.index') }}" class="btn btn-outline-primary btn-sm">Back to Accounts Dashboard</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('accounts.customers') }}" class="row g-2 mb-3">
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

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
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
                                    <td>{{ $row['name'] }}</td>
                                    <td class="text-end">&#8377;{{ number_format((float)$row['total_invoiced'], 2) }}</td>
                                    <td class="text-end text-success">&#8377;{{ number_format((float)$row['total_collected'], 2) }}</td>
                                    <td class="text-end {{ (float)$row['receivable'] > 0 ? 'text-danger' : '' }}">&#8377;{{ number_format((float)$row['receivable'], 2) }}</td>
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

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">
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
