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

        .marketplace-accounts-suite .accounts-table {
            min-width: 980px;
        }

        .marketplace-accounts-suite .accounts-table thead th {
            background: #f8fafc;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            vertical-align: middle;
        }

        .marketplace-accounts-suite .accounts-table td {
            vertical-align: middle;
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .marketplace-accounts-suite .accounts-table .manage-cell {
            min-width: 430px;
        }

        .marketplace-accounts-suite .account-manage-form .form-control,
        .marketplace-accounts-suite .account-manage-form .form-select {
            min-height: 38px;
        }

        .marketplace-accounts-suite .account-manage-form .form-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 6px;
        }

        .marketplace-accounts-suite .account-manage-grid {
            display: grid;
            grid-template-columns: minmax(120px, 1.1fr) minmax(140px, 1.4fr) minmax(100px, .9fr);
            gap: 10px;
            align-items: end;
        }

        .marketplace-accounts-suite .account-manage-actions {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        .marketplace-accounts-suite .account-manage-actions .btn {
            min-width: 96px;
        }

        .marketplace-accounts-suite .account-pill {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 700;
        }

        .marketplace-accounts-suite .manage-panel {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #fbfdff;
            padding: 12px;
        }

        .marketplace-accounts-suite .table-account-name {
            font-weight: 600;
            color: #0f172a;
        }

        .marketplace-accounts-suite .table-account-meta {
            margin-top: 4px;
            font-size: 12px;
            color: #64748b;
        }

        @media (max-width: 1199.98px) {
            .marketplace-accounts-suite .account-manage-grid {
                grid-template-columns: 1fr;
            }

            .marketplace-accounts-suite .account-manage-actions {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $platformOptions = collect($platformSuggestions ?? $supportedPlatforms ?? collect())->filter()->values();
    @endphp

    <div class="page-content marketplace-accounts-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="hero-card mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-8">
                                    <span class="eyebrow">Marketplace Accounts</span>
                                    <h2 class="mt-3 mb-2">Marketplace Accounts</h2>
                                    <p class="text-muted mb-0">Create reusable seller accounts for any marketplace and manage separate stock buckets under each account.</p>
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
                                    <select name="platform" class="form-select js-platform-select" data-custom-target="new-custom-platform" required>
                                        <option value="">Select platform</option>
                                        @foreach ($platformOptions as $platformOption)
                                            <option value="{{ $platformOption }}">{{ ucwords($platformOption) }}</option>
                                        @endforeach
                                        <option value="__custom__">Add New Platform</option>
                                    </select>
                                </div>
                                <div class="col-12 d-none" id="new-custom-platform">
                                    <label class="form-label">New Platform Name</label>
                                    <input type="text" name="custom_platform" class="form-control" placeholder="Enter platform name">
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
                                <table class="table table-striped align-middle mb-0 accounts-table">
                                    <thead>
                                        <tr>
                                            <th>Platform</th>
                                            <th>Account Name</th>
                                            <th>Listings</th>
                                            <th>Status</th>
                                            <th style="width: 390px;">Manage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($accounts as $account)
                                            @php
                                                $editPlatformOptions = $platformOptions->contains($account->platform)
                                                    ? $platformOptions
                                                    : $platformOptions->prepend($account->platform)->unique()->values();
                                            @endphp
                                            <tr>
                                                <td><span class="account-pill">{{ ucfirst($account->platform) }}</span></td>
                                                <td>
                                                    <div class="table-account-name">{{ $account->name }}</div>
                                                    <div class="table-account-meta">{{ ucfirst($account->platform) }} seller account</div>
                                                </td>
                                                <td>{{ number_format((int) ($account->listings_count ?? 0)) }}</td>
                                                <td>
                                                    <span class="badge {{ $account->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                                        {{ $account->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td class="manage-cell">
                                                    <div class="manage-panel">
                                                        <form action="{{ route('products.marketplace.accounts.update', $account->id) }}" method="POST" class="account-manage-form">
                                                            @csrf
                                                            @method('PATCH')
                                                            <div class="account-manage-grid">
                                                                <div>
                                                                    <label class="form-label small text-muted mb-1">Platform</label>
                                                                    <select name="platform" class="form-select form-select-sm js-platform-select" data-custom-target="edit-custom-platform-{{ $account->id }}" required>
                                                                        @foreach ($editPlatformOptions as $platformOption)
                                                                            <option value="{{ $platformOption }}" {{ $account->platform === $platformOption ? 'selected' : '' }}>
                                                                                {{ ucwords($platformOption) }}
                                                                            </option>
                                                                        @endforeach
                                                                        <option value="__custom__">Add New Platform</option>
                                                                    </select>
                                                                </div>
                                                                <div>
                                                                    <label class="form-label small text-muted mb-1">Account Name</label>
                                                                    <input type="text" name="name" value="{{ $account->name }}" class="form-control form-control-sm" required>
                                                                </div>
                                                                <div>
                                                                    <label class="form-label small text-muted mb-1">Status</label>
                                                                    <select name="is_active" class="form-select form-select-sm">
                                                                        <option value="1" {{ $account->is_active ? 'selected' : '' }}>Active</option>
                                                                        <option value="0" {{ !$account->is_active ? 'selected' : '' }}>Inactive</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="row g-2 mt-1">
                                                                <div class="col-12 col-lg-7 d-none" id="edit-custom-platform-{{ $account->id }}">
                                                                    <label class="form-label small text-muted mb-1">New Platform Name</label>
                                                                    <input type="text" name="custom_platform" class="form-control form-control-sm" placeholder="Enter platform name">
                                                                </div>
                                                            </div>
                                                            <div class="account-manage-actions">
                                                                <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                                            </div>
                                                        </form>
                                                        <form action="{{ route('products.marketplace.accounts.destroy', $account->id) }}" method="POST" onsubmit="return confirm('Delete this marketplace account?');" class="mt-2">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">No marketplace accounts created yet.</td>
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

    <script>
        (function () {
            const toggleCustomPlatform = function (select) {
                const targetId = select.getAttribute('data-custom-target');
                if (!targetId) {
                    return;
                }

                const target = document.getElementById(targetId);
                if (!target) {
                    return;
                }

                const showCustom = select.value === '__custom__';
                target.classList.toggle('d-none', !showCustom);

                const input = target.querySelector('input[name="custom_platform"]');
                if (input) {
                    input.required = showCustom;
                    if (!showCustom) {
                        input.value = '';
                    }
                }
            };

            document.querySelectorAll('.js-platform-select').forEach(function (select) {
                toggleCustomPlatform(select);
                select.addEventListener('change', function () {
                    toggleCustomPlatform(select);
                });
            });
        })();
    </script>
@endsection
