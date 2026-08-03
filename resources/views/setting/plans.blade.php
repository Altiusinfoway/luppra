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
    .module-chip {
        border: 1px solid #dce1ea;
        border-radius: 999px;
        padding: .35rem .7rem;
        font-size: .82rem;
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        background: #f8fafc;
    }
    .module-chip input[type="checkbox"] {
        margin-top: 0;
    }
    .settings-suite .table-wrap {
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        overflow: hidden;
        background: #fff;
    }
    .settings-suite .edit-row-shell {
        background: #f8fafc !important;
    }
    .settings-suite .edit-row-shell td {
        border-top: 1px solid #e2e8f0;
        padding: 18px;
    }
    .settings-suite .edit-form-shell {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #ffffff;
        padding: 14px;
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
                                <h1 class="hero-title">SaaS Plans</h1>
                                <p class="hero-subtitle mb-0">Create and manage subscription plans, limits, and enabled modules inside the same modern admin experience as the refreshed CRM.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                        <li class="breadcrumb-item">Setting</li>
                                        <li class="breadcrumb-item active">Plans</li>
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
                        <h5 class="card-title mb-0">Create Plan</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('setting.plans.store') }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Plan Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Code</label>
                                <input type="text" class="form-control" name="code" placeholder="STARTER / PRO">
                            </div>
                            <div class="row g-2">
                                <div class="col-6 mb-2">
                                    <label class="form-label">Price</label>
                                    <input type="number" step="0.01" min="0" class="form-control" name="price" value="0" required>
                                </div>
                                <div class="col-6 mb-2">
                                    <label class="form-label">Cycle</label>
                                    <select class="form-select" name="billing_cycle" required>
                                        <option value="monthly">Monthly</option>
                                        <option value="quarterly">Quarterly</option>
                                        <option value="yearly">Yearly</option>
                                        <option value="one_time">One-time</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-4 mb-2">
                                    <label class="form-label">Trial</label>
                                    <input type="number" class="form-control" min="0" name="trial_days" value="0">
                                </div>
                                <div class="col-4 mb-2">
                                    <label class="form-label">Users</label>
                                    <input type="number" class="form-control" min="1" name="user_limit" placeholder="Unlimited">
                                </div>
                                <div class="col-4 mb-2">
                                    <label class="form-label">WA Limit</label>
                                    <input type="number" class="form-control" min="1" name="whatsapp_limit" placeholder="Unlimited">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label d-block">Modules</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($availableModules as $moduleKey => $moduleLabel)
                                        <label class="module-chip">
                                            <input type="checkbox" name="modules[]" value="{{ $moduleKey }}">
                                            <span>{{ $moduleLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-6 mb-2">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" class="form-control" min="0" name="sort_order" value="0">
                                </div>
                                <div class="col-6 d-flex align-items-end mb-2">
                                    <div class="form-check">
                                        <input type="hidden" name="is_active" value="0">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="new_plan_active" checked>
                                        <label class="form-check-label" for="new_plan_active">Active</label>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-success w-100" type="submit">Save Plan</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card settings-shell">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Plan List</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table-wrap">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Cycle</th>
                                    <th>Limits</th>
                                    <th>Modules</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($plans as $plan)
                                    <tr>
                                        @php
                                            $planModules = collect($plan->modules ?? [])->map(fn($m) => strtolower((string) $m))->all();
                                        @endphp
                                        <td>
                                            <div class="fw-semibold">{{ $plan->name }}</div>
                                            <small class="text-muted">{{ $plan->code ?: '-' }}</small>
                                        </td>
                                        <td>{{ number_format((float) $plan->price, 2) }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $plan->billing_cycle)) }}</td>
                                        <td>
                                            <div>Users: {{ $plan->user_limit ?: 'Unlimited' }}</div>
                                            <div>WA: {{ $plan->whatsapp_limit ?: 'Unlimited' }}</div>
                                            <div>Trial: {{ $plan->trial_days }} days</div>
                                        </td>
                                        <td>
                                            @if(is_array($plan->modules) && count($plan->modules))
                                                @if(in_array('*', $planModules, true))
                                                    All Modules
                                                @else
                                                    {{ implode(', ', $plan->modules) }}
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($plan->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary mb-1" data-bs-toggle="collapse" data-bs-target="#edit-plan-{{ $plan->id }}">
                                                Edit
                                            </button>
                                            <form method="POST" action="{{ route('setting.plans.delete', $plan->id) }}" onsubmit="return confirm('Delete this plan?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <tr class="collapse edit-row-shell" id="edit-plan-{{ $plan->id }}">
                                        <td colspan="7">
                                            <div class="edit-form-shell">
                                            <form method="POST" action="{{ route('setting.plans.update', $plan->id) }}">
                                                @csrf
                                                <div class="row g-2">
                                                    <div class="col-md-2">
                                                        <input class="form-control form-control-sm" name="name" value="{{ $plan->name }}" required>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input class="form-control form-control-sm" name="code" value="{{ $plan->code }}">
                                                    </div>
                                                    <div class="col-md-1">
                                                        <input class="form-control form-control-sm" type="number" step="0.01" min="0" name="price" value="{{ (float) $plan->price }}" required>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <select class="form-select form-select-sm" name="billing_cycle" required>
                                                            @foreach(['monthly','quarterly','yearly','one_time'] as $cycle)
                                                                <option value="{{ $cycle }}" {{ $plan->billing_cycle === $cycle ? 'selected' : '' }}>
                                                                    {{ ucfirst(str_replace('_', ' ', $cycle)) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-1">
                                                        <input class="form-control form-control-sm" type="number" min="0" name="trial_days" value="{{ $plan->trial_days }}">
                                                    </div>
                                                    <div class="col-md-1">
                                                        <input class="form-control form-control-sm" type="number" min="1" name="user_limit" value="{{ $plan->user_limit }}">
                                                    </div>
                                                    <div class="col-md-1">
                                                        <input class="form-control form-control-sm" type="number" min="1" name="whatsapp_limit" value="{{ $plan->whatsapp_limit }}">
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @foreach($availableModules as $moduleKey => $moduleLabel)
                                                                @php
                                                                    $isChecked = $moduleKey === 'all'
                                                                        ? in_array('*', $planModules, true)
                                                                        : in_array($moduleKey, $planModules, true);
                                                                @endphp
                                                                <label class="module-chip">
                                                                    <input type="checkbox" name="modules[]" value="{{ $moduleKey }}" {{ $isChecked ? 'checked' : '' }}>
                                                                    <span>{{ $moduleLabel }}</span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div class="col-md-1">
                                                        <input class="form-control form-control-sm" type="number" min="0" name="sort_order" value="{{ $plan->sort_order }}">
                                                    </div>
                                                    <div class="col-md-1 d-flex align-items-center">
                                                        <div class="form-check">
                                                            <input type="hidden" name="is_active" value="0">
                                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $plan->is_active ? 'checked' : '' }}>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button class="btn btn-sm btn-primary w-100" type="submit">Update</button>
                                                    </div>
                                                </div>
                                            </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No plans found.</td>
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
