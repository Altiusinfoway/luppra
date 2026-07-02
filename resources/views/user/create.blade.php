@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h5 class="mb-sm-0">User </h5>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">User </a></li>
                                <li class="breadcrumb-item active">Create</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>



            <div class="row justify-content-center">
                <!-- Varying Modal Content -->
                <div class="col-xxl-10 col-lg-10">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">User Form</h4>
                        </div>
                        <!-- end card header -->
                        <div class="card-body">

                            {{ Form::open(['url' => 'users', 'method' => 'post', 'enctype' => 'multipart/form-data', 'id' => 'userForm']) }}
                            @csrf
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
