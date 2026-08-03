@extends('layouts.app')
@php
    $profile=\App\Models\Utility::get_file('uploads/avatar/');
     $default_img = \App\Models\Utility::defaultImage();
@endphp

@section('page-css')
    <style>
        .user-form-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .user-form-suite .hero-shell,
        .user-form-suite .form-shell {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
        }

        .user-form-suite .hero-eyebrow {
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

        .user-form-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.84);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .user-form-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .user-form-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
        }

        .user-form-suite .section-intro {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }

        .user-form-suite .preview-avatar {
            width: 104px;
            height: 104px;
            object-fit: cover;
            border-radius: 20px;
            border: 1px solid #dbeafe;
            box-shadow: 0 14px 24px rgba(15, 23, 42, 0.08);
        }

        .user-form-suite .form-actions {
            padding-top: 1rem;
            border-top: 1px solid rgba(226, 232, 240, 0.8);
        }

        .user-form-suite .section-card {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 16px;
        }
    </style>
@endsection

@section('content')

<div class="page-content user-form-suite">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-8">
                                <span class="hero-eyebrow">Access Management</span>
                                <h1 class="mb-3">Edit User</h1>
                                <p class="text-muted mb-0">Update account details, role assignment, profile image, and user type from a cleaner dedicated form shell.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">User</a></li>
                                        <li class="breadcrumb-item active">Edit</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="row g-3">
                        <div class="col-md-6 col-xl-4">
                            <div class="card summary-card h-100">
                                <div class="card-body">
                                    <span class="label">Workflow</span>
                                    <h3>Edit</h3>
                                    <p class="text-muted mb-0 mt-2">Update account details, role access, and profile settings from one editor.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <div class="card summary-card h-100">
                                <div class="card-body">
                                    <span class="label">Account Type</span>
                                    <h3>{{ $user->type ?? 'User' }}</h3>
                                    <p class="text-muted mb-0 mt-2">Quick visibility into the current account classification before changes are saved.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card form-shell">
                        <div class="card-header">
                            <h4 class="card-title mb-0">User Form</h4>
                        </div>
                        <!-- end card header -->
                        <div class="card-body">
                        <div class="section-intro">
                            <h5 class="mb-1">User Configuration</h5>
                            <p class="text-muted mb-0">Edit contact information, password, role assignment, account type, and profile image from the same access-management workspace.</p>
                        </div>

                        {{Form::model($user,array('route' => array('users.update', $user->id), 'method' => 'PUT',  'enctype' => "multipart/form-data", 'class'=>'needs-validation','novalidate')) }}
                            <div class="section-card">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="mb-3">

                                    {{ Form::label('name', __('User name'), ['class' => 'form-label']) }}<x-required></x-required>
                                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter name'), 'required' => 'required']) }}

                                    @error('name')
                                        <small class="invalid-name" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </small>
                                    @enderror

                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="mb-3">

                                            {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}<x-required></x-required>
                                            {{ Form::email('email', null, ['class' => 'form-control', 'placeholder' => __('Enter Email'), 'required' => 'required']) }}

                                            @error('email')
                                                <small class="invalid-email" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </small>
                                            @enderror


                                            </div>
                                            <div class="mb-3">
                                            {{ Form::label('phone', __('Contact No'), ['class' => 'form-label']) }}<x-required></x-required>
                                            {{ Form::text('phone', null, ['class' => 'form-control', 'placeholder' => __('Enter contact no.'), 'required' => 'required']) }}
                                            @error('phone')
                                                <small class="invalid-phone" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </small>
                                            @enderror
                                            </div>
                                            <div class="mb-3">

                                                {{ Form::label('password', __('Password'), ['class' => 'form-label']) }}<x-required></x-required>
                                                {{ Form::password('password', ['class' => 'form-control', 'placeholder' => __('Enter Password'), 'minlength' => '6']) }}
                                                @error('password')
                                                    <small class="invalid-password" role="alert">
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    </small>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">

                                            <div class="mb-3">
                                                {{ Form::label('role', __('User Role'), ['class' => 'form-label']) }}<x-required></x-required>
                                                {!! Form::select('role', $roles, $user->roles, ['class' => 'form-control select', 'required' => 'required']) !!}
                                                @error('role')
                                                    <small class="invalid-role" role="alert">
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    </small>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                {{ Form::label('user_type', __('User Type'), ['class' => 'form-label']) }}<x-required></x-required>
                                                {!! Form::select('user_type', ['' => 'Select User Type'] + $user_type_list, old('user_type', $user->type), ['class' => 'form-control select', 'required' => 'required']) !!}
                                                @error('user_type')
                                                    <small class="invalid-user_type" role="alert">
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    </small>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <div class="form-group">

                                                    <div class="choose-files mt-3">
                                                        <label class="form-label"
                                                            for="avatar">{{__('Choose file here')}}</label>
                                                        <input type="file" class="form-control" name="avatar"
                                                            id="avatar">
                                                    </div>

                                                    <div class="theme-avtar-logo mt-4">
                                                        <img id="image" src="{{ ($user->avatar) ?  $user->avatar :  $default_img }}"
                                                            class="preview-avatar">
                                                    </div>

                                                    <span class="text-xs text-muted">{{ __('Please upload a valid image file. Size of image should not be more than 2MB.')}}</span>
                                                </div>
                                            </div>



                                        </div>
                                    </div>


                                </div>
                            </div>
                            </div>
                            <!-- end row -->

                            <!-- end card -->
                            <div class="text-center mb-3 form-actions">
                                <button type="submit" class="btn btn-primary w-sm">Submit</button>
                            </div>
                            {{ Form::close() }}

                        <!-- end card body -->
                        </div>


                    </div>
                    <!-- end col -->

                </div>
                <!-- end row -->
        </div>

        <div class="row mt-4">
            <div class="col-lg-8">
                <div class="card form-shell">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Activity History</h4>
                    </div>
                    <div class="card-body">
                        @include('activity._timeline', [
                            'activities' => $activityTimeline,
                            'emptyMessage' => 'No activity found for this user.',
                        ])
                    </div>
                </div>
            </div>
        </div>




    </div>
</div>
@endsection
