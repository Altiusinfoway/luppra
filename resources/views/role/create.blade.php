@extends('layouts.app')

@section('page-css')
    <style>
        .role-form-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .role-form-suite .hero-shell,
        .role-form-suite .form-shell {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
        }

        .role-form-suite .hero-eyebrow {
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

        .role-form-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.84);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .role-form-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .role-form-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .role-form-suite .form-shell {
            padding: 1.5rem !important;
        }

        .role-form-suite .role-intro {
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }

        .role-form-suite .tab-content table {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            background: #ffffff;
        }

        .role-form-suite .tab-content thead th {
            background: #f8fafc !important;
            color: #475569;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            vertical-align: middle;
        }

        .role-form-suite .tab-content tbody td {
            vertical-align: top;
        }

        .role-form-suite .tab-content tbody tr td:first-child,
        .role-form-suite .tab-content tbody tr td:nth-child(2) {
            background: rgba(248, 250, 252, 0.72);
        }

        .role-form-suite .custom-checkbox,
        .role-form-suite .custom-control.custom-checkbox {
            margin-bottom: 0.85rem;
            padding: 0.35rem 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #f8fafc;
        }

        .role-form-suite .custom-control-label,
        .role-form-suite label.ischeck {
            color: #334155;
            font-weight: 600;
        }

        .role-form-suite .tab-pane .form-group {
            overflow: hidden;
            border-radius: 22px;
        }

        .role-form-suite .submit-row {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid rgba(226, 232, 240, 0.8);
        }
    </style>
@endsection

@section('content')

    <div class="page-content role-form-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-8">
                                    <span class="hero-eyebrow">Access Management</span>
                                    <h1 class="mb-3">Create Role</h1>
                                    <p class="text-muted mb-0">Build a new role and assign module permissions using the same cleaner settings-style workspace.</p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
                                            <li class="breadcrumb-item active">Create</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-xxl-10 col-xl-12">
                    <div class="card form-shell p-4">
                        <!-- Bordered Tables -->
                        {{ Form::open(['url' => 'roles', 'method' => 'post', 'class' => 'needs-validation', 'novalidate']) }}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6 col-xl-3">
                                <div class="card summary-card h-100">
                                    <div class="card-body">
                                        <span class="label">Workflow</span>
                                        <h3>Create</h3>
                                        <p class="text-muted mb-0 mt-2">Set up a new role and shape its module access from one workspace.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <div class="card summary-card h-100">
                                    <div class="card-body">
                                        <span class="label">Access Areas</span>
                                        <h3>4 Tabs</h3>
                                        <p class="text-muted mb-0 mt-2">Staff, CRM, HRM, and Account permissions stay grouped for faster review.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="role-intro">
                            <h5 class="mb-1">Role Configuration</h5>
                            <p class="text-muted mb-0">Start with a role name, then use the permission matrices below to grant access by module and function.</p>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-12 col-md-12 col-sm-12 border-bottom py-3">
                                <div class="form-group">
                                    {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}<x-required></x-required>
                                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Role Name'), 'required' => 'required']) }}
                                    @error('name')
                                        <small class="invalid-name" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-12 border-bottom">
                                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="pills-staff-tab" data-bs-toggle="pill" href="#staff"
                                            role="tab" aria-controls="pills-home"
                                            aria-selected="true">{{ __('Staff') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-crm-tab" data-bs-toggle="pill" href="#crm"
                                            role="tab" aria-controls="pills-profile"
                                            aria-selected="false">{{ __('CRM') }}</a>
                                    </li>
                                    {{-- <li class="nav-item">
                                    <a class="nav-link" id="pills-project-tab" data-bs-toggle="pill" href="#project" role="tab" aria-controls="pills-contact" aria-selected="false">{{__('Project')}}</a>
                                    </li> --}}
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-hrmpermission-tab" data-bs-toggle="pill"
                                            href="#hrmpermission" role="tab" aria-controls="pills-contact"
                                            aria-selected="false">{{ __('HRM') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-account-tab" data-bs-toggle="pill" href="#account"
                                            role="tab" aria-controls="pills-contact"
                                            aria-selected="false">{{ __('Account') }}</a>
                                    </li>
                                    {{-- <li class="nav-item">
                                    <a class="nav-link" id="pills-account-tab" data-bs-toggle="pill" href="#pos" role="tab" aria-controls="pills-contact" aria-selected="false">{{__('POS')}}</a>
                                    </li> --}}
                                </ul>
                            </div>
                            <div class="col-lg-12">
                                <div class="tab-content" id="pills-tabContent">
                                    <div class="tab-pane fade show active" id="staff" role="tabpanel"
                                        aria-labelledby="pills-home-tab">
                                        @php
                                            $modules = [
                                                'user',
                                                'role',
                                                'category',
                                                'product & service',
                                                'company settings',
                                                'bank detail',
                                                'device',
                                                'bulk message',
                                            ];
                                            // if(\Auth::user()->type == 'company'){
                                            //     $modules[] = 'permission';
                                            // }
                                        @endphp
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                @if (!empty($permissions))
                                                    <table class="table table-bordered mb-0" id="dataTable-1">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th colspan = "3" class="text-center">
                                                                    {{ __('Assign General Permission to Roles') }}
                                                                </th>
                                                            </tr>
                                                            <tr>
                                                                <th>
                                                                    <input type="checkbox" class="form-check-input"
                                                                        name="staff_checkall" id="staff_checkall">
                                                                </th>
                                                                <th>{{ __('Module') }} </th>
                                                                <th colspan="4">{{ __('Permissions') }} </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($modules as $module)
                                                                <tr>
                                                                    <td><input type="checkbox"
                                                                            class="form-check-input ischeck staff_checkall"
                                                                            data-id="{{ str_replace(' ', '', str_replace('&', '', $module)) }}">
                                                                    </td>
                                                                    <td><label class="ischeck staff_checkall"
                                                                            data-id="{{ str_replace(' ', '', str_replace('&', '', $module)) }}">{{ ucfirst($module) }}</label>
                                                                    </td>
                                                                    <td>
                                                                        <div class="row">
                                                                            @if (in_array('view ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('view ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck staff_checkall isscheck_' . str_replace(' ', '', str_replace('&', '', $module)), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'View', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('add ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('add ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck staff_checkall isscheck_' . str_replace(' ', '', str_replace('&', '', $module)), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Add', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('move ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('move ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck staff_checkall isscheck_' . str_replace(' ', '', str_replace('&', '', $module)), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Move', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('manage ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('manage ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck staff_checkall isscheck_' . str_replace(' ', '', str_replace('&', '', $module)), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Manage', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('create ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('create ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck staff_checkall isscheck_' . str_replace(' ', '', str_replace('&', '', $module)), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Create', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('edit ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('edit ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck staff_checkall isscheck_' . str_replace(' ', '', str_replace('&', '', $module)), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Edit', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('delete ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('delete ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck staff_checkall isscheck_' . str_replace(' ', '', str_replace('&', '', $module)), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Delete', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('show ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('show ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck staff_checkall isscheck_' . str_replace(' ', '', str_replace('&', '', $module)), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Show', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif


                                                                            @if (in_array('send ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('send ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck staff_checkall isscheck_' . str_replace(' ', '', str_replace('&', '', $module)), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Send', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('create payment ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('create payment ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck staff_checkall isscheck_' . str_replace(' ', '', str_replace('&', '', $module)), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Create Payment', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('delete payment ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('delete payment ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck staff_checkall isscheck_' . str_replace(' ', '', str_replace('&', '', $module)), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Delete Payment', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('income ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('income ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck staff_checkall isscheck_' . str_replace(' ', '', str_replace('&', '', $module)), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Income', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('expense ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('expense ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck staff_checkall isscheck_' . str_replace(' ', '', str_replace('&', '', $module)), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Expense', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('income vs expense ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('income vs expense ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck staff_checkall isscheck_' . str_replace(' ', '', str_replace('&', '', $module)), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Income VS Expense', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('loss & profit ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('loss & profit ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck staff_checkall isscheck_' . str_replace(' ', '', str_replace('&', '', $module)), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Loss & Profit', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('tax ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('tax ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck staff_checkall isscheck_' . str_replace(' ', '', str_replace('&', '', $module)), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Tax', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('invoice ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('invoice ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck staff_checkall isscheck_' . str_replace(' ', '', str_replace('&', '', $module)), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Invoice', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('bill ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('bill ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck staff_checkall isscheck_' . str_replace(' ', '', str_replace('&', '', $module)), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Bill', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('duplicate ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('duplicate ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck staff_checkall isscheck_' . str_replace(' ', '', str_replace('&', '', $module)), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Duplicate', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('balance sheet ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('balance sheet ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck staff_checkall isscheck_' . str_replace(' ', '', str_replace('&', '', $module)), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Balance Sheet', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('ledger ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('ledger ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck staff_checkall isscheck_' . str_replace(' ', '', str_replace('&', '', $module)), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Ledger', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('trial balance ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('trial balance ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck staff_checkall isscheck_' . str_replace(' ', '', str_replace('&', '', $module)), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Trial Balance', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="crm" role="tabpanel"
                                        aria-labelledby="pills-profile-tab">
                                        @php
                                            $modules = [
                                                'lead',
                                                'lead stage',
                                                'source',
                                                'quote',
                                                'order',
                                                'invoice',
                                                'account',
                                                'advertisement',
                                            ]; //spanko
                                        @endphp
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                @if (!empty($permissions))
                                                    <table class="table table-bordered mb-0" id="dataTable-1">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th colspan = "3" class="text-center">
                                                                    {{ __('Assign CRM related Permission to Roles') }}
                                                                </th>
                                                            </tr>
                                                            <tr>
                                                                <th>
                                                                    <input type="checkbox"
                                                                        class="form-check-input custom_align_middle"
                                                                        name="crm_heckall" id="crm_checkall">
                                                                </th>
                                                                <th>{{ __('Module') }} </th>
                                                                <th>{{ __('Permissions') }} </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>

                                                            @foreach ($modules as $module)
                                                                <tr>
                                                                    <td><input type="checkbox"
                                                                            class="form-check-input ischeck crm_checkall"
                                                                            data-id="{{ str_replace(' ', '', $module) }}">
                                                                    </td>
                                                                    <td><label class="ischeck crm_checkall"
                                                                            data-id="{{ str_replace(' ', '', $module) }}">{{ ucfirst($module) }}</label>
                                                                    </td>
                                                                    <td>
                                                                        <div class="row">
                                                                            @if (in_array('view ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('view ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck crm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'View', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('add ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('add ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck crm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Add', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('move ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('move ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck crm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Move', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('manage ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('manage ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck crm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Manage', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('create ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('create ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck crm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Create', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('edit ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('edit ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck crm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Edit', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('delete ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('delete ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck crm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Delete', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('show ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('show ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck crm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Show', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('send ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('send ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck crm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Send', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('create payment ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('create payment ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck crm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Create Payment', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('delete payment ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('delete payment ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck crm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Delete Payment', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('income ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('income ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck crm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Income', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('expense ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('expense ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck crm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Expense', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('income vs expense ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('income vs expense ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck crm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Income VS Expense', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('loss & profit ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('loss & profit ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck crm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Loss & Profit', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('tax ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('tax ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck crm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Tax', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('invoice ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('invoice ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck crm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Invoice', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('bill ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('bill ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck crm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Bill', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('duplicate ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('duplicate ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck crm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Duplicate', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('balance sheet ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('balance sheet ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck crm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Balance Sheet', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('ledger ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('ledger ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck crm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Ledger', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('trial balance ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('trial balance ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck crm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Trial Balance', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="project" role="tabpanel"
                                        aria-labelledby="pills-contact-tab">
                                        @php
                                            $modules = ['project', 'activity'];
                                        @endphp
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                @if (!empty($permissions))
                                                    <table class="table table-bordered mb-0" id="dataTable-1">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th colspan = "3" class="text-center">
                                                                    {{ __('Assign Project related Permission to Roles') }}
                                                                </th>
                                                            </tr>
                                                            <tr>
                                                                <th>
                                                                    <input type="checkbox"
                                                                        class="form-check-input align-middle custom_align_middle"
                                                                        name="project_checkall" id="project_checkall">
                                                                </th>
                                                                <th>{{ __('Module') }} </th>
                                                                <th>{{ __('Permissions') }} </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>

                                                            @foreach ($modules as $module)
                                                                <tr>
                                                                    <td><input type="checkbox"
                                                                            class="form-check-input align-middle ischeck project_checkall"
                                                                            data-id="{{ str_replace(' ', '', $module) }}">
                                                                    </td>
                                                                    <td><label class="ischeck project_checkall"
                                                                            data-id="{{ str_replace(' ', '', $module) }}">{{ ucfirst($module) }}</label>
                                                                    </td>
                                                                    <td>
                                                                        <div class="row ">
                                                                            @if (in_array('view ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('view ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input  isscheck project_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'View', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('add ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('add ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck project_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Add', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('move ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('move ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck project_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Move', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('manage ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('manage ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck project_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Manage', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('create ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('create ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck project_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Create', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('edit ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('edit ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck project_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Edit', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('delete ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('delete ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck project_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Delete', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('show ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('show ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck project_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Show', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif


                                                                            @if (in_array('send ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('send ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck project_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Send', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('create payment ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('create payment ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck project_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Create Payment', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('delete payment ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('delete payment ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck project_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Delete Payment', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('income ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('income ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck project_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Income', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('expense ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('expense ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck project_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Expense', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('income vs expense ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('income vs expense ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck project_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Income VS Expense', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('loss & profit ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('loss & profit ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck project_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Loss & Profit', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('tax ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('tax ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck project_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Tax', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('invoice ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('invoice ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck project_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Invoice', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('bill ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('bill ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck project_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Bill', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('duplicate ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('duplicate ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck project_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Duplicate', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('balance sheet ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('balance sheet ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck project_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Balance Sheet', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('ledger ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('ledger ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck project_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Ledger', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('trial balance ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('trial balance ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck project_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Trial Balance', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="hrmpermission" role="tabpanel"
                                        aria-labelledby="pills-contact-tab">
                                        @php
                                            $modules = [
                                                'employee',
                                                'department',
                                                'designation',
                                                'holiday',
                                                'leave',
                                                'attendance',
                                                'sales target',
                                                'payroll',
                                                'working hours',
                                                'leave rule',
                                                'leave type',
                                                'sales_employee_target',
                                                'follow-up',
                                            ];
                                        @endphp

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                @if (!empty($permissions))
                                                    <table class="table table-bordered mb-0" id="dataTable-1">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th class="text-center" colspan="3">
                                                                    {{ __('Assign HRM related Permission to Roles') }}
                                                                </th>
                                                            </tr>
                                                            <tr>
                                                                <th>
                                                                    <input type="checkbox"
                                                                        class="form-check-input align-middle custom_align_middle"
                                                                        name="hrm_checkall" id="hrm_checkall">
                                                                </th>
                                                                <th>{{ __('Module') }} </th>
                                                                <th>{{ __('Permissions') }} </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($modules as $module)
                                                                <tr>
                                                                    <td><input type="checkbox"
                                                                            class="form-check-input align-middle ischeck hrm_checkall"
                                                                            data-id="{{ str_replace(' ', '', $module) }}">
                                                                    </td>
                                                                    <td><label class="ischeck hrm_checkall"
                                                                            data-id="{{ str_replace(' ', '', $module) }}">{{ ucfirst($module) }}</label>
                                                                    </td>
                                                                    <td>
                                                                        <div class="row ">

                                                                            @if (in_array('view ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('view ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck hrm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'View', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('add ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('add ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck hrm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Add', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('move ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('move ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck hrm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Move', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('manage ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('manage ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck hrm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Manage', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('create ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('create ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck hrm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Create', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('edit ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('edit ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck hrm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Edit', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('delete ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('delete ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck hrm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Delete', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('show ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('show ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck hrm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Show', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif


                                                                            @if (in_array('send ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('send ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck hrm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Send', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('create payment ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('create payment ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck hrm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Create Payment', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('delete payment ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('delete payment ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck hrm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Delete Payment', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('income ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('income ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck hrm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Income', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('expense ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('expense ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck hrm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Expense', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('income vs expense ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('income vs expense ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck hrm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Income VS Expense', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('loss & profit ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('loss & profit ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck hrm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Loss & Profit', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('tax ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('tax ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck hrm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Tax', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('invoice ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('invoice ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck hrm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Invoice', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('bill ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('bill ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck hrm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Bill', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('duplicate ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('duplicate ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck hrm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Duplicate', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('balance sheet ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('balance sheet ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck hrm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Balance Sheet', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('ledger ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('ledger ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck hrm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Ledger', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('trial balance ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('trial balance ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck hrm_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Trial Balance', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="account" role="tabpanel"
                                        aria-labelledby="pills-contact-tab">
                                        @php
                                            $modules = ['payment', 'vender', 'customer', 'transport', 'finance report']; //'attendance report',
                                        @endphp
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                @if (!empty($permissions))
                                                    <table class="table table-bordered mb-0" id="dataTable-1">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th class="text-center" colspan="3">
                                                                    {{ __('Assign Account related Permission to Roles') }}
                                                                </th>
                                                            </tr>
                                                            <tr>
                                                                <th>
                                                                    <input type="checkbox"
                                                                        class="form-check-input custom_align_middle"
                                                                        name="account_checkall" id="account_checkall">
                                                                </th>
                                                                <th>{{ __('Module') }} </th>
                                                                <th>{{ __('Permissions') }} </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>

                                                            @foreach ($modules as $module)
                                                                <tr>
                                                                    <td><input type="checkbox"
                                                                            class="form-check-input ischeck account_checkall"
                                                                            data-id="{{ str_replace(' ', '', $module) }}">
                                                                    </td>
                                                                    <td><label class="ischeck"
                                                                            data-id="{{ str_replace(' ', '', $module) }}">{{ ucfirst($module) }}</label>
                                                                    </td>
                                                                    <td>
                                                                        <div class="row ">

                                                                            @if (in_array('add ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('add ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Add', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('move ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('move ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Move', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('manage ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('manage ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Manage', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('create ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('create ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Create', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('edit ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('edit ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Edit', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('view ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('view ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'View', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('delete ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('delete ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Delete', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('show ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('show ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Show', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif


                                                                            @if (in_array('send ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('send ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Send', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('create payment ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('create payment ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Create Payment', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('delete payment ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('delete payment ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Delete Payment', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('income ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('income ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Income', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('expense ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('expense ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Expense', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('loss & profit ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('loss & profit ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Loss & Profit', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('tax ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('tax ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Tax', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('invoice ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('invoice ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Invoice', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('bill ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('bill ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Bill', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('duplicate ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('duplicate ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Duplicate', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('balance sheet ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('balance sheet ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Balance Sheet', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('ledger ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('ledger ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Ledger', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('trial balance ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('trial balance ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Trial Balance', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('income vs expense ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('income vs expense ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Income VS Expense', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('convert ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('convert ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck account_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'convert', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="pos" role="tabpanel"
                                        aria-labelledby="pills-contact-tab">
                                        @php
                                            $modules = ['warehouse'];
                                        @endphp
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                @if (!empty($permissions))
                                                    <h6 class="my-3">{{ __('Assign POS related Permission to Roles') }}
                                                    </h6>
                                                    <table class="table table-bordered mb-0" id="dataTable-1">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>
                                                                    <input type="checkbox"
                                                                        class="form-check-input custom_align_middle"
                                                                        name="pos_checkall" id="pos_checkall">
                                                                </th>
                                                                <th>{{ __('Module') }} </th>
                                                                <th>{{ __('Permissions') }} </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>

                                                            @foreach ($modules as $module)
                                                                <tr>
                                                                    <td><input type="checkbox"
                                                                            class="form-check-input ischeck pos_checkall"
                                                                            data-id="{{ str_replace(' ', '', $module) }}">
                                                                    </td>
                                                                    <td><label class="ischeck"
                                                                            data-id="{{ str_replace(' ', '', $module) }}">{{ ucfirst($module) }}</label>
                                                                    </td>
                                                                    <td>
                                                                        <div class="row ">
                                                                            @if (in_array('view ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('view ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck pos_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'View', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('add ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('add ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck pos_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Add', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('manage ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('manage ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck pos_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Manage', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('create ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('create ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck pos_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Create', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('edit ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('edit ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck pos_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Edit', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('delete ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('delete ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck pos_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Delete', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('show ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('show ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck pos_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Show', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif


                                                                            @if (in_array('send ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('send ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck pos_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Send', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif


                                                                            @if (in_array('convert ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('convert ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck pos_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'convert', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                            @if (in_array('create payment ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('create payment ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck pos_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Create Payment', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif
                                                                            @if (in_array('delete payment ' . $module, (array) $permissions))
                                                                                @if ($key = array_search('delete payment ' . $module, $permissions))
                                                                                    <div
                                                                                        class="col-md-3 custom-control custom-checkbox">
                                                                                        {{ Form::checkbox('permissions[]', $key, false, ['class' => 'form-check-input isscheck pos_checkall isscheck_' . str_replace(' ', '', $module), 'id' => 'permission' . $key]) }}
                                                                                        {{ Form::label('permission' . $key, 'Delete Payment', ['class' => 'custom-control-label']) }}<br>
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="submit-row">
                            <input type="submit" value="{{ __('Create') }}" class="btn  btn-primary me-4">
                            <a href="{{ route('roles.index') }}" class="btn  btn-secondary">{{ __('Cancel') }}</a>

                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
            $("#staff_checkall").click(function() {
                $('.staff_checkall').not(this).prop('checked', this.checked);
            });
            $("#crm_checkall").click(function() {
                $('.crm_checkall').not(this).prop('checked', this.checked);
            });
            $("#project_checkall").click(function() {
                $('.project_checkall').not(this).prop('checked', this.checked);
            });
            $("#hrm_checkall").click(function() {
                $('.hrm_checkall').not(this).prop('checked', this.checked);
            });
            $("#account_checkall").click(function() {
                $('.account_checkall').not(this).prop('checked', this.checked);
            });
            $("#pos_checkall").click(function() {
                $('.pos_checkall').not(this).prop('checked', this.checked);
            });
            $(".ischeck").click(function() {
                var ischeck = $(this).data('id');
                $('.isscheck_' + ischeck).prop('checked', this.checked);
            });
        });
    </script>
@endsection
