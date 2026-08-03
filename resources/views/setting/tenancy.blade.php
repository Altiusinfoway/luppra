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
    .settings-suite .mini-panel {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: .85rem .95rem;
        background: #f8fafc;
        height: 100%;
    }
    .settings-suite .nested-panel {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #f8fafc;
        padding: 1rem;
    }
    .settings-suite .status-banner {
        border: 1px solid #dce4ee;
        border-radius: 18px;
        padding: 1rem 1.15rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }
    .settings-suite .status-banner.status-success {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border-color: #bbf7d0;
        color: #166534;
    }
    .settings-suite .status-banner.status-danger {
        background: linear-gradient(135deg, #fef2f2 0%, #fff1f2 100%);
        border-color: #fecdd3;
        color: #be123c;
    }
    .settings-suite .status-banner.status-warning {
        background: linear-gradient(135deg, #fffbeb 0%, #fff7d6 100%);
        border-color: #fde68a;
        color: #92400e;
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
                                <span class="hero-eyebrow">Configuration</span>
                                <h1 class="hero-title">Tenant Management</h1>
                                <p class="hero-subtitle mb-0">Create tenants, inspect health, review subscriptions, and manage multi-tenant operations from the same modern admin shell as the refreshed CRM.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Tenant Management</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-4">
                <div class="card settings-shell">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Create New Tenant</h5>
                    </div>
                    <div class="card-body">
                        @if(session('error'))
                            <div class="status-banner status-danger mb-3">{{ session('error') }}</div>
                        @endif
                        @if(session('success'))
                            <div class="status-banner status-success mb-3">{{ session('success') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="status-banner status-danger mb-3">
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('setting.tenancy.store') }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Tenant Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Slug (optional)</label>
                                <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" placeholder="e.g. ravi-agro">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Database (optional)</label>
                                <input type="text" name="database" class="form-control" value="{{ old('database') }}" placeholder="e.g. tenant_ravi_agro">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Primary Domain (optional)</label>
                                <input type="text" name="domain" class="form-control" value="{{ old('domain') }}" placeholder="e.g. ravi.local">
                            </div>

                            <div class="nested-panel mb-2">
                                <h6 class="mb-2">Company Admin User</h6>
                                <div class="mb-2">
                                    <label class="form-label">Admin Name</label>
                                    <input type="text" name="admin_name" class="form-control" value="{{ old('admin_name') }}" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Admin Email</label>
                                    <input type="email" name="admin_email" class="form-control" value="{{ old('admin_email') }}" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Admin Phone (optional)</label>
                                    <input type="text" name="admin_phone" class="form-control" value="{{ old('admin_phone') }}" placeholder="e.g. 919876543210">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="admin_password" class="form-control" required>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" name="admin_password_confirmation" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                {{-- <label class="form-label">Template Company User ID</label>
                                <input type="number" name="template_company_id" class="form-control" value="{{ old('template_company_id') }}" placeholder="e.g. 2"> --}}
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="with_seed" value="1" id="with_seed" {{ old('with_seed') ? 'checked' : '' }}>
                                <label class="form-check-label" for="with_seed">Seed master data after create</label>
                            </div>
                            <button class="btn btn-primary w-100" type="submit">Create Tenant</button>
                        </form>
                    </div>
                </div>

                <div class="card settings-shell">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Quick Navigation</h5>
                    </div>
                    <div class="card-body">
                        <input type="text" id="tenantSearch" class="form-control form-control-sm mb-2"
                            placeholder="Search tenant by name / slug / db">
                        <div class="d-grid gap-2" id="tenantQuickNav">
                            @forelse($tenantRows as $row)
                                @php $tenant = $row['tenant']; @endphp
                                <a href="#tenant-{{ $tenant->id }}" class="btn btn-sm btn-light text-start tenant-quick-link"
                                    data-tenant-search="{{ strtolower($tenant->name.' '.$tenant->slug.' '.$tenant->database) }}">
                                    #{{ $tenant->id }} - {{ $tenant->name }}
                                </a>
                            @empty
                                <div class="text-muted small">No tenants found.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card settings-shell">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">All Tenants</h5>
                        <span class="badge bg-info-subtle text-info">Total: {{ count($tenantRows) }}</span>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="tenantAccordion">
                            @forelse($tenantRows as $row)
                                @php
                                    $tenant = $row['tenant'];
                                    $status = $row['status'];
                                    $subscription = $row['subscription'];
                                    $planLimit = optional($subscription?->plan)->whatsapp_limit;
                                @endphp
                                <div class="accordion-item mb-2 tenant-card"
                                    id="tenant-{{ $tenant->id }}"
                                    data-tenant-search="{{ strtolower($tenant->name.' '.$tenant->slug.' '.$tenant->database) }}">
                                    <h2 class="accordion-header" id="heading-{{ $tenant->id }}">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapse-{{ $tenant->id }}"
                                            aria-expanded="false" aria-controls="collapse-{{ $tenant->id }}">
                                            <div class="w-100 d-flex align-items-center justify-content-between pe-2">
                                                <div>
                                                    <div class="fw-semibold">#{{ $tenant->id }} {{ $tenant->name }}</div>
                                                    <div class="small text-muted">{{ $tenant->slug }} | <code>{{ $tenant->database }}</code></div>
                                                </div>
                                                <div class="text-end">
                                                    @if(!$status['db_ok'])
                                                        <span class="badge bg-danger">DB Fail</span>
                                                    @elseif(count($status['missing_tables']) > 0)
                                                        <span class="badge bg-warning text-dark">Missing {{ count($status['missing_tables']) }}</span>
                                                    @else
                                                        <span class="badge bg-success">Healthy</span>
                                                    @endif
                                                    @if($tenant->is_active)
                                                        <span class="badge bg-success-subtle text-success">Active</span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse-{{ $tenant->id }}" class="accordion-collapse collapse"
                                        aria-labelledby="heading-{{ $tenant->id }}" data-bs-parent="#tenantAccordion">
                                        <div class="accordion-body">
                                            <ul class="nav nav-pills nav-custom nav-danger nav-justified mb-3" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link active" data-bs-toggle="tab"
                                                        data-bs-target="#t-overview-{{ $tenant->id }}" type="button" role="tab">Overview</button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" data-bs-toggle="tab"
                                                        data-bs-target="#t-subscription-{{ $tenant->id }}" type="button" role="tab">Subscription</button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" data-bs-toggle="tab"
                                                        data-bs-target="#t-users-{{ $tenant->id }}" type="button" role="tab">Users</button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" data-bs-toggle="tab"
                                                        data-bs-target="#t-ops-{{ $tenant->id }}" type="button" role="tab">Operations</button>
                                                </li>
                                            </ul>

                                            <div class="tab-content">
                                                <div class="tab-pane fade show active" id="t-overview-{{ $tenant->id }}" role="tabpanel">
                                                    <div class="row g-2">
                                                        <div class="col-md-3"><div class="mini-panel small">Leads: <strong>{{ $status['leads'] ?? '-' }}</strong></div></div>
                                                        <div class="col-md-3"><div class="mini-panel small">Quotes: <strong>{{ $status['quotes'] ?? '-' }}</strong></div></div>
                                                        <div class="col-md-3"><div class="mini-panel small">Orders: <strong>{{ $status['orders'] ?? '-' }}</strong></div></div>
                                                        <div class="col-md-3"><div class="mini-panel small">Users: <strong>{{ $row['users_count'] }}</strong></div></div>
                                                    </div>
                                                    @if(!empty($status['error']))
                                                        <div class="status-banner status-danger mt-2 mb-0 py-2">{{ $status['error'] }}</div>
                                                    @elseif(count($status['missing_tables']) > 0)
                                                        <div class="status-banner status-warning mt-2 mb-0 py-2">
                                                            Missing tables: {{ \Illuminate\Support\Str::limit(implode(', ', $status['missing_tables']), 200) }}
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="tab-pane fade" id="t-subscription-{{ $tenant->id }}" role="tabpanel">
                                                    <form method="POST" action="{{ route('setting.tenancy.subscription.save', $tenant->id) }}">
                                                        @csrf
                                                        <div class="row g-2">
                                                            <div class="col-md-3">
                                                                <label class="form-label mb-1">Plan</label>
                                                                <select name="plan_id" class="form-select form-select-sm" required>
                                                                    <option value="">Select Plan</option>
                                                                    @foreach($plans as $plan)
                                                                        <option value="{{ $plan->id }}" {{ (int) optional($subscription)->plan_id === (int) $plan->id ? 'selected' : '' }}>
                                                                            {{ $plan->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <label class="form-label mb-1">Status</label>
                                                                <select name="status" class="form-select form-select-sm" required>
                                                                    @foreach(['trialing','active','expired','canceled'] as $st)
                                                                        <option value="{{ $st }}" {{ optional($subscription)->status === $st ? 'selected' : '' }}>
                                                                            {{ ucfirst($st) }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <label class="form-label mb-1">Starts</label>
                                                                <input type="date" name="starts_at" class="form-control form-control-sm"
                                                                    value="{{ optional(optional($subscription)->starts_at)->format('Y-m-d') }}">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <label class="form-label mb-1">Ends</label>
                                                                <input type="date" name="ends_at" class="form-control form-control-sm"
                                                                    value="{{ optional(optional($subscription)->ends_at)->format('Y-m-d') }}">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label mb-1">Amount</label>
                                                                <input type="number" step="0.01" min="0" name="amount" class="form-control form-control-sm"
                                                                    value="{{ optional($subscription)->amount }}">
                                                            </div>
                                                            <div class="col-md-9">
                                                                <label class="form-label mb-1">Payment Ref / Notes</label>
                                                                <div class="row g-2">
                                                                    <div class="col-md-4">
                                                                        <input type="text" name="payment_ref" class="form-control form-control-sm"
                                                                            placeholder="Payment Ref" value="{{ optional($subscription)->payment_ref }}">
                                                                    </div>
                                                                    <div class="col-md-8">
                                                                        <input type="text" name="notes" class="form-control form-control-sm"
                                                                            placeholder="Notes" value="{{ optional($subscription)->notes }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label mb-1">WhatsApp Usage</label>
                                                                <div class="form-control form-control-sm bg-light">
                                                                    {{ $row['usage_whatsapp_month'] }} / {{ $planLimit ?: 'Unlimited' }}
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <button class="btn btn-sm btn-primary" type="submit">Save Subscription</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>

                                                <div class="tab-pane fade" id="t-users-{{ $tenant->id }}" role="tabpanel">
                                                    <form method="POST" action="{{ route('setting.tenancy.assign-users', $tenant->id) }}">
                                                        @csrf
                                                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                                            <label class="form-label mb-0">Map Users</label>
                                                            <button type="button" class="btn btn-sm btn-outline-primary tenant-users-select-all" data-tenant-id="{{ $tenant->id }}">Select All</button>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary tenant-users-clear-all" data-tenant-id="{{ $tenant->id }}">Clear</button>
                                                            <button type="button" class="btn btn-sm btn-outline-dark tenant-users-unmapped" data-tenant-id="{{ $tenant->id }}">Only Unmapped</button>
                                                            <button type="button" class="btn btn-sm btn-outline-info tenant-users-show-all" data-tenant-id="{{ $tenant->id }}">Show All</button>
                                                        </div>
                                                        <input type="text"
                                                            class="form-control form-control-sm mb-2 tenant-users-search"
                                                            data-tenant-id="{{ $tenant->id }}"
                                                            placeholder="Search user by name / email / role">
                                                        <div class="border rounded p-2 tenant-users-box" style="max-height: 260px; overflow-y: auto;">
                                                            @foreach($users as $user)
                                                                @php
                                                                    $isMappedToTenant = (int) $user->tenant_id === (int) $tenant->id;
                                                                    $isUnmapped = empty($user->tenant_id);
                                                                    $searchText = strtolower('#'.$user->id.' '.$user->name.' '.$user->email.' '.$user->type);
                                                                    $isVisibleUser = $isMappedToTenant || $isUnmapped;
                                                                @endphp
                                                                @if(!$isVisibleUser)
                                                                    @continue
                                                                @endif
                                                                <div class="form-check mb-2 tenant-user-row"
                                                                    data-tenant-id="{{ $tenant->id }}"
                                                                    data-is-unmapped="{{ $isUnmapped ? '1' : '0' }}"
                                                                    data-user-search="{{ $searchText }}">
                                                                    <input class="form-check-input tenant-user-checkbox"
                                                                        type="checkbox"
                                                                        name="user_ids[]"
                                                                        value="{{ $user->id }}"
                                                                        id="tenant-{{ $tenant->id }}-user-{{ $user->id }}"
                                                                        {{ $isMappedToTenant ? 'checked' : '' }}>
                                                                    <label class="form-check-label small w-100"
                                                                        for="tenant-{{ $tenant->id }}-user-{{ $user->id }}">
                                                                        <span class="fw-semibold">#{{ $user->id }} - {{ $user->name }}</span>
                                                                        <span class="text-muted">({{ $user->email }})</span>
                                                                        <span class="badge bg-light text-dark">{{ $user->type }}</span>
                                                                        @if($isUnmapped)
                                                                            <span class="badge bg-warning-subtle text-warning">Unmapped</span>
                                                                        @elseif(!$isMappedToTenant)
                                                                            <span class="badge bg-info-subtle text-info">Tenant #{{ $user->tenant_id }}</span>
                                                                        @endif
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <div class="mt-2 small text-muted">
                                                            Showing only current tenant + unmapped users. Other-tenant users are hidden to reduce confusion.
                                                        </div>
                                                        <div class="mt-2">
                                                            <label class="form-label mb-1">Move from another tenant (optional)</label>
                                                            <input type="text" name="transfer_user_ids" class="form-control form-control-sm"
                                                                placeholder="Enter user IDs, comma separated. Example: 21, 34">
                                                        </div>
                                                        <button class="btn btn-sm btn-dark" type="submit">Update User Mapping</button>
                                                    </form>
                                                </div>

                                                <div class="tab-pane fade" id="t-ops-{{ $tenant->id }}" role="tabpanel">
                                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                                        <form method="POST" action="{{ route('setting.tenancy.provision', $tenant->id) }}">
                                                            @csrf
                                                            <button class="btn btn-sm btn-outline-primary" type="submit">Provision + Sync</button>
                                                        </form>
                                                        <form method="POST" action="{{ route('setting.tenancy.health', $tenant->id) }}">
                                                            @csrf
                                                            <button class="btn btn-sm btn-outline-success" type="submit">Run Health</button>
                                                        </form>
                                                        <form method="POST" action="{{ route('setting.tenancy.toggle-status', $tenant->id) }}">
                                                            @csrf
                                                            @if($tenant->is_active)
                                                                <button class="btn btn-sm btn-outline-danger" type="submit">Deactivate</button>
                                                            @else
                                                                <button class="btn btn-sm btn-outline-success" type="submit">Activate</button>
                                                            @endif
                                                        </form>
                                                        <form method="POST" action="{{ route('setting.tenancy.switch-context') }}">
                                                            @csrf
                                                            <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                                                            <button class="btn btn-sm btn-outline-dark" type="submit">Switch Context</button>
                                                        </form>
                                                    </div>
                                                    <form method="POST" action="{{ route('setting.tenancy.seed', $tenant->id) }}" class="row g-2">
                                                        @csrf
                                                        <div class="col-md-6">
                                                            {{-- <input type="number" class="form-control form-control-sm" name="template_company_id" placeholder="Template Company ID (optional)"> --}}
                                                        </div>
                                                        <div class="col-md-6">
                                                            <button class="btn btn-sm btn-outline-secondary" type="submit">Seed Masters</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-5">No tenants found.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('tenantSearch');
    if (!searchInput) return;

    const cards = document.querySelectorAll('.tenant-card');
    const quickLinks = document.querySelectorAll('.tenant-quick-link');

    const applyFilter = () => {
        const q = (searchInput.value || '').trim().toLowerCase();

        cards.forEach((card) => {
            const hay = (card.getAttribute('data-tenant-search') || '').toLowerCase();
            card.style.display = hay.includes(q) ? '' : 'none';
        });

        quickLinks.forEach((link) => {
            const hay = (link.getAttribute('data-tenant-search') || '').toLowerCase();
            link.style.display = hay.includes(q) ? '' : 'none';
        });
    };

    searchInput.addEventListener('input', applyFilter);

    document.querySelectorAll('.tenant-users-search').forEach((input) => {
        input.addEventListener('input', function () {
            const tenantId = this.getAttribute('data-tenant-id');
            const q = (this.value || '').trim().toLowerCase();
            document.querySelectorAll('.tenant-user-row[data-tenant-id="' + tenantId + '"]').forEach((row) => {
                const hay = (row.getAttribute('data-user-search') || '').toLowerCase();
                row.style.display = hay.includes(q) ? '' : 'none';
            });
        });
    });

    document.querySelectorAll('.tenant-users-select-all').forEach((btn) => {
        btn.addEventListener('click', function () {
            const tenantId = this.getAttribute('data-tenant-id');
            document.querySelectorAll('.tenant-user-row[data-tenant-id="' + tenantId + '"] .tenant-user-checkbox').forEach((cb) => {
                if (cb.closest('.tenant-user-row').style.display !== 'none') {
                    cb.checked = true;
                }
            });
        });
    });

    document.querySelectorAll('.tenant-users-clear-all').forEach((btn) => {
        btn.addEventListener('click', function () {
            const tenantId = this.getAttribute('data-tenant-id');
            document.querySelectorAll('.tenant-user-row[data-tenant-id="' + tenantId + '"] .tenant-user-checkbox').forEach((cb) => {
                if (cb.closest('.tenant-user-row').style.display !== 'none') {
                    cb.checked = false;
                }
            });
        });
    });

    document.querySelectorAll('.tenant-users-unmapped').forEach((btn) => {
        btn.addEventListener('click', function () {
            const tenantId = this.getAttribute('data-tenant-id');
            document.querySelectorAll('.tenant-user-row[data-tenant-id="' + tenantId + '"]').forEach((row) => {
                const isUnmapped = row.getAttribute('data-is-unmapped') === '1';
                row.style.display = isUnmapped ? '' : 'none';
            });
        });
    });

    document.querySelectorAll('.tenant-users-show-all').forEach((btn) => {
        btn.addEventListener('click', function () {
            const tenantId = this.getAttribute('data-tenant-id');
            document.querySelectorAll('.tenant-user-row[data-tenant-id="' + tenantId + '"]').forEach((row) => {
                row.style.display = '';
            });
        });
    });
});
</script>
@endsection
