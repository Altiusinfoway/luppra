@extends('layouts.app')
@php
    $profile=\App\Models\Utility::get_file('uploads/avatar/');
     $default_img = \App\Models\Utility::defaultImage();
@endphp
@section('content')

<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h5 class="mb-sm-0">User Operation Form Section</h5>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">User Operation</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>



        <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">User Form</h4>
                        </div>
                        <!-- end card header -->
                        <div class="card-body">

                        {{Form::model($user,array('route' => array('users.update', $user->id), 'method' => 'PUT',  'enctype' => "multipart/form-data", 'class'=>'needs-validation','novalidate')) }}
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
                                                            class="big-logo" height="100px" width="100px">
                                                    </div>

                                                    <span class="text-xs text-muted">{{ __('Please upload a valid image file. Size of image should not be more than 2MB.')}}</span>
                                                </div>
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

        <div class="row mt-4">
            <div class="col-lg-8">
                <div class="card">
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
