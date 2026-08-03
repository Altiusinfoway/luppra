@extends('layouts.app')

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
                                    <h1 class="mb-3">Create User</h1>
                                    <p class="text-muted mb-0">Add a new team member, role, profile image, and account type using the same refined form layout as the rest of the updated admin UI.</p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">User</a></li>
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
                <div class="col-xxl-10 col-lg-10 mb-4">
                    <div class="row g-3">
                        <div class="col-md-6 col-xl-3">
                            <div class="card summary-card h-100">
                                <div class="card-body">
                                    <span class="label">Workflow</span>
                                    <h3>Create</h3>
                                    <p class="text-muted mb-0 mt-2">Add a new internal account with role, type, and basic profile details.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="card summary-card h-100">
                                <div class="card-body">
                                    <span class="label">Coverage</span>
                                    <h3>Role + Profile</h3>
                                    <p class="text-muted mb-0 mt-2">Create the access layer and the person record together from one form.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-10 col-lg-10">
                    <div class="card form-shell">
                        <div class="card-header">
                            <h4 class="card-title mb-0">User Form</h4>
                        </div>
                        <!-- end card header -->
                        <div class="card-body">
                            <div class="section-intro">
                                <h5 class="mb-1">User Configuration</h5>
                                <p class="text-muted mb-0">Set the account name, contact details, role assignment, image, and password from the same access-management form.</p>
                            </div>

                            {{ Form::open(['url' => 'users', 'method' => 'post', 'enctype' => 'multipart/form-data', 'id' => 'userForm']) }}
                            @csrf
                            <div class="section-card">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="mb-3">

                                        {{ Form::label('name', __('User name'), ['class' => 'form-label']) }}<x-required></x-required>
                                        {{ Form::text('name',  old('name'), ['class' => 'form-control', 'placeholder' => __('Enter name'), 'required' => 'required']) }}

                                        <small class="text-danger" id="error-name"></small>

                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="mb-3">

                                                {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}<x-required></x-required>
                                                {{ Form::email('email', old('email'), ['class' => 'form-control', 'placeholder' => __('Enter Email'), 'required' => 'required']) }}

                                                <small class="text-danger" id="error-email"></small>


                                            </div>
                                            <div class="mb-3">
                                                {{ Form::label('phone', __('Contact No'), ['class' => 'form-label']) }}<x-required></x-required>
                                                {{ Form::text('phone',  old('phone'), ['class' => 'form-control', 'placeholder' => __('Enter contact no.'), 'required' => 'required']) }}
                                                <small class="text-danger" id="error-phone"></small>
                                            </div>

                                             <div class="mb-3">
                                                {{ Form::label('role', __('User Role'), ['class' => 'form-label']) }}<x-required></x-required>
                                                @if (isset($req_type))
                                                    {!! Form::select('role', $roles, 3, ['class' => 'form-control select', 'required' => 'required']) !!}
                                                @else
                                                    {!! Form::select('role',['' => 'Select User Role'] + $roles->toArray(), old('role'), ['class' => 'form-control select', 'required' => 'required']) !!}
                                                @endif
                                                <small class="text-danger" id="error-role"></small>
                                            </div>

                                        </div>
                                        <div class="col-lg-6">

                                            <div class="mb-3">
                                                <label class="form-label" for="profile">Profile</label>
                                                <input type="file" class="form-control" name="avatar_final"
                                                    id="avatar_final">
                                            </div>

                                            <div class="mb-3">
                                                {{ Form::label('user_type', __('User Type'), ['class' => 'form-label']) }}<x-required></x-required>

                                                    {!! Form::select('user_type',['' => 'Select User Type'] + $user_type_list,
                                                   old('user_type'),
                                                    [
                                                        'class' => 'form-control select ' . ($errors->has('user_type') ? 'is-invalid' : ''),
                                                        'required' => true
                                                    ]) !!}

                                                <small class="text-danger" id="error-user_type"></small>
                                            </div>

                                             <div class="mb-3">

                                                {{ Form::label('password', __('Password'), ['class' => 'form-label']) }}<x-required></x-required>
                                                {{ Form::password('password', ['class' => 'form-control', 'placeholder' => __('Enter Password'), 'minlength' => '6']) }}
                                               <small class="text-danger" id="error-password"></small>
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




        </div>
    </div>
@endsection
@section('page-script')
<script>
    $(document).ready(function () {

    $('#userForm').on('submit', function (e) {
        e.preventDefault();

        // clear previous errors
        $('.text-danger').text('');
        $('.form-control').removeClass('is-invalid');

        let formData = new FormData(this);

        $.ajax({
            url: "{{ url('users') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },

            success: function (response) {
                show_toastr('Success', response.success, 'success');
                setTimeout(() => {
                    window.location.href = response.redirect_url;
                }, 1000);
            },

            error: function (xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;

                    $.each(errors, function (key, value) {
                        $('#error-' + key).text(value[0]);
                        $('[name="' + key + '"]').addClass('is-invalid');
                    });
                }
            }
        });
    });

});
</script>
@endsection
