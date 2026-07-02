@extends('layouts.app')
@php
    $profile = \App\Models\Utility::get_file('uploads/avatar/');
@endphp
@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h5 class="mb-sm-0">Profile Section</h5>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">User Profile</a></li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>



            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Profile</h4>
                        </div>
                        <!-- end card header -->
                        <div class="card-body">

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
                                                        alt="Preview" style="max-height: 100px;" class="img-thumbnail">
                                                </div>

                                                <span class="text-danger" id="error-image_final"></span>
                                            </div>



                                        </div>
                                    </div>


                                </div>
                            </div>
                            <!-- end row -->

                            <!-- end card -->
                            <div class="text-center mb-3">
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
