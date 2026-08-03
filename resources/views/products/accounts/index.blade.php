@extends('layouts.app')

@section('page-css')
    <style>
        .marketplace-accounts-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .marketplace-accounts-suite .hero-card,
        .marketplace-accounts-suite .shell-card,
        .marketplace-accounts-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 24px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.06);
        }

        .marketplace-accounts-suite .hero-card {
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
                linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        .marketplace-accounts-suite .eyebrow {
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
    </style>
@endsection

@section('content')
    <div class="page-content marketplace-accounts-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="hero-card mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-8">
                                    <span class="eyebrow">Marketplace Accounts</span>
                                    <h2 class="mt-3 mb-2">Amazon & Flipkart Accounts</h2>
                                    <p class="text-muted mb-0">Create reusable seller accounts like Flipkart 1, Flipkart 2, Amazon 1, and Amazon 2. Listings can then use the same SKU under different accounts with separate stock buckets.</p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
                                            <li class="breadcrumb-item active">Marketplace Accounts</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-xl-4">
                    <div class="shell-card">
                        <div class="card-body p-4">
                            <h5 class="mb-1">Add Account</h5>
                            <p class="text-muted mb-3">Create a reusable marketplace account for listings and quantity allocation.</p>

                            <form action="{{ route('products.marketplace.accounts.store') }}" method="POST" class="row g-3">
                                @csrf
                                <div class="col-12">
                                    <label class="form-label">Platform</label>
                                    <select name="platform" class="form-select" required>
                                        <option value="">Select Platform</option>
                                        @foreach ($supportedPlatforms as $platformKey => $platformLabel)
                                            <option value="{{ $platformKey }}">{{ $platformLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Account Name</label>
                                    <input type="text" name="name" class="form-control" placeholder="Flipkart 1 / Amazon 2" required>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="account_is_active" value="1" checked>
                                        <label class="form-check-label" for="account_is_active">Active account</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Save Account</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="shell-card">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                <div>
                                    <h5 class="mb-1">Saved Accounts</h5>
                                    <p class="text-muted mb-0">Use these in product marketplace listings.</p>
                                </div>
                                <span class="badge bg-light text-dark">{{ $accounts->count() }} accounts</span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Platform</th>
                                            <th>Account Name</th>
                                            <th>Status</th>
                                            <th style="width: 280px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($accounts as $account)
                                            <tr>
                                                <td>{{ ucfirst($account->platform) }}</td>
                                                <td>{{ $account->name }}</td>
                                                <td>
                                                    <span class="badge {{ $account->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                                        {{ $account->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <form action="{{ route('products.marketplace.accounts.update', $account->id) }}" method="POST" class="row g-2 align-items-center">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="col-md-4">
                                                            <select name="platform" class="form-select form-select-sm" required>
                                                                @foreach ($supportedPlatforms as $platformKey => $platformLabel)
                                                                    <option value="{{ $platformKey }}" {{ $account->platform === $platformKey ? 'selected' : '' }}>
                                                                        {{ $platformLabel }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <input type="text" name="name" value="{{ $account->name }}" class="form-control form-control-sm" required>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <select name="is_active" class="form-select form-select-sm">
                                                                <option value="1" {{ $account->is_active ? 'selected' : '' }}>On</option>
                                                                <option value="0" {{ !$account->is_active ? 'selected' : '' }}>Off</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2 d-flex gap-2">
                                                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                                        </div>
                                                    </form>
                                                    <form action="{{ route('products.marketplace.accounts.destroy', $account->id) }}" method="POST" class="mt-2" onsubmit="return confirm('Delete this marketplace account?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">No marketplace accounts created yet.</td>
                                            </tr>
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
