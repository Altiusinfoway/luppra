@extends('layouts.app')
@php
    $profile = \App\Models\Utility::get_file('uploads/avatar/');
@endphp

@section('page-css')
    <style>
        .profile-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .profile-suite .hero-shell,
        .profile-suite .form-shell {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
        }

        .profile-suite .hero-eyebrow {
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

        .profile-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.84);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .profile-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .profile-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
        }

        .profile-suite .section-intro {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }

        .profile-suite .preview-avatar {
            width: 104px;
            height: 104px;
            object-fit: cover;
            border-radius: 20px;
            border: 1px solid #dbeafe;
            box-shadow: 0 14px 24px rgba(15, 23, 42, 0.08);
        }

        .profile-suite .form-actions {
            padding-top: 1rem;
            border-top: 1px solid rgba(226, 232, 240, 0.8);
        }
    </style>
@endsection

@section('content')
    <div class="page-content profile-suite">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-8">
                                    <span class="hero-eyebrow">Personal Workspace</span>
                                    <h1 class="mb-3">User Profile</h1>
                                    <p class="text-muted mb-0">Update your account information and profile image in the same refined admin form experience as the rest of the refreshed UI.</p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                            <li class="breadcrumb-item active">Profile</li>
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
                                    <span class="label">Workspace</span>
                                    <h3>Profile</h3>
                                    <p class="text-muted mb-0 mt-2">Maintain your own identity, password, and image from the same refined personal workspace.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <div class="card summary-card h-100">
                                <div class="card-body">
                                    <span class="label">Account</span>
                                    <h3>{{ $user->type ?? 'User' }}</h3>
                                    <p class="text-muted mb-0 mt-2">Current account classification visible while updating personal access details.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card form-shell">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Profile</h4>
                        </div>
                        <!-- end card header -->
                        <div class="card-body">
                            <div class="section-intro">
                                <h5 class="mb-1">Profile Configuration</h5>
                                <p class="text-muted mb-0">Update your contact details, password, and profile image from one cleaner self-service profile form.</p>
                            </div>

                            {{ Form::model($user, ['route' => ['user_profile.update', $user->id], 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate','id'=>'main-form']) }}
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

                                                {{ Form::label('password', __('Password'), ['class' => 'form-label']) }}
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
                                                <label for="image_final" class="form-label">Profile Image <span
                                                        class="text-danger">*</span></label>
                                                @php
                                                    $default_img = \App\Models\Utility::defaultImage();
                                                @endphp

                                                <input type="file" class="form-control" id="image_final"
                                                    name="image_final" accept="image/*">

                                                    <div class="mb-2">
                                                        <img id="preview-image" class="mt-3"
                                                            src="{{ !empty($user->avatar) ? $user->avatar : $default_img }}"
                                                            alt="Preview" class="preview-avatar">
                                                    </div>

                                                <span class="text-danger" id="error-image_final"></span>
                                            </div>



                                        </div>
                                    </div>


                                </div>
                            </div>
                            <!-- end row -->

                            <!-- end card -->
                            <div class="text-center mb-3 form-actions">
                                <button type="submit" class="btn btn-success w-sm">Submit</button>
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
        document.getElementById('image_final').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('preview-image');

            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };

                reader.readAsDataURL(file);
            } else {
                preview.src = '#';
                preview.style.display = 'none';
            }
        });
    </script>
    <script>
        $(document).ready(function()
        {
            const profileSuccessMessage = sessionStorage.getItem('profile_success_message');
            if (profileSuccessMessage) {
                sessionStorage.removeItem('profile_success_message');
                show_toastr('success', profileSuccessMessage);
            }

            $('#main-form').on('submit', function(e)
            {
                e.preventDefault();

                let form = $(this)[0];
                let url = $(this).attr('action');
                let formData = new FormData(form);

                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();
                $('.text-danger').text('');

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(res) {
                        if (res.success) {
                            sessionStorage.setItem('profile_success_message', res.success);
                            setTimeout(() => {
                                window.location.href = res.redirect_url;
                            }, 300);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;

                            $.each(errors, function(key, messages) {
                                let $input = $(`[name="${key}"]`);
                                if ($input.length > 0) {
                                    $input.addClass('is-invalid');
                                    $input.after(
                                        `<div class="invalid-feedback">${messages[0]}</div>`
                                        );
                                } else if (document.getElementById("error-" + key)) {
                                    $('#error-' + key).text(messages[0]);
                                } else {
                                    console.log('error', messages[0]);
                                }
                            });
                        } else {
                            console.log('error', 'Something went wrong.');
                        }
                    }
                });
            });


        });
    </script>
@endsection
