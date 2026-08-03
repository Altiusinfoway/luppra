@extends('layouts.app')

@section('page-css')
    <style>
        .roles-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .roles-suite .hero-shell,
        .roles-suite .table-shell {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
        }

        .roles-suite .hero-eyebrow {
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

        .roles-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.84);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .roles-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .roles-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .roles-suite .section-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.05);
        }

        .roles-suite .permission-cell {
            min-width: 180px;
        }

        .roles-suite .permission-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
        }

        .roles-suite .permission-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 0.75rem;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: 0.84rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .roles-suite .roles-table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
        }

        .roles-suite .roles-table thead th {
            background: #f8fafc !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-content roles-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-8">
                                    <span class="hero-eyebrow">Access Management</span>
                                    <h1 class="mb-3">Roles</h1>
                                    <p class="text-muted mb-0">Review role definitions and permission coverage in a cleaner access-control workspace.</p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
                                            <li class="breadcrumb-item active">List</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Role Count</span>
                            <h3>{{ $roles->count() }}</h3>
                            <p class="text-muted mb-0 mt-2">All available role definitions currently visible in your access-control workspace.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Permission Timeline</span>
                            <h3>{{ $permissionActivityTimeline->count() }}</h3>
                            <p class="text-muted mb-0 mt-2">Recent permission or role events surfaced alongside the role listing.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card table-shell">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title  mb-0">Roles List</h5>
                                {{-- @can('create role') --}}
                                    <div>
                                        <a href="{{ route('roles.create') }}" class="btn btn-sm btn-success" id="addproduct-btn"><i
                                                class="ri-add-line align-bottom me-1"></i> Add Role</a>
                                    </div>
                                {{-- @endcan --}}
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="section-card">
                                <div class="card-body p-0">
                                    <div class="roles-table-wrap">
                                        <div class="table-responsive">
                                            <table class="table datatable roles-table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Role') }} </th>
                                                        <th>{{ __('Permissions') }} </th>
                                                        <th width="150">{{ __('Action') }} </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($roles as $role)
                                                        @if ($role->name != 'client' && !(Auth::user()->type == 'company' && $role->name == 'company'))
                                                            <tr class="font-style main-row">
                                                                <td class="Role fw-semibold">{{ $role->name }}</td>
                                                                <td class="Permission permission-cell">
                                                                    <div class="permission-grid">
                                                                        @foreach ($role->permissions()->pluck('name') as $permissionName)
                                                                            <span class="permission-pill">{{ $permissionName }}</span>
                                                                        @endforeach
                                                                    </div>
                                                                </td>
                                                                <td class="Action">
                                                                    @can('edit role')
                                                                        <div class="action-btn">
                                                                            <a href="{{ route('roles.edit', $role->id) }}"
                                                                                class="btn btn-sm align-items-center bg-info text-white">
                                                                                <i class="mdi mdi-pencil text-white me-1"></i>
                                                                                Edit
                                                                            </a>
                                                                        </div>
                                                                    @endcan
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--end col-->
            </div><!--end row-->

            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="card section-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Permission Activity History</h5>
                        </div>
                        <div class="card-body">
                            @include('activity._timeline', [
                                'activities' => $permissionActivityTimeline,
                                'emptyMessage' => 'No permission activity found.',
                            ])
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- container-fluid -->
    </div>
@endsection
