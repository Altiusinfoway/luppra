@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div
                            class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                            <h4 class="mb-sm-0">Employee Section</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employee</a></li>
                                    <li class="breadcrumb-item active"> Profile</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end page title -->
                <div class="position-relative mx-n4 mt-n4">
                    <div class="profile-wid-bg profile-setting-img">
                        <img src="https://kk.asiantechnocast.com/assets/images/profile-bg.jpg" class="profile-wid-img"
                            alt="">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="card mt-n5">
                            <div class="card-body p-4">
                                <div class="text-center">
                                    <div class="profile-user position-relative d-inline-block mx-auto  mb-4">
                                        @php
                                            $default_img = \App\Models\Utility::defaultImage();
                                        @endphp
                                        <img src="{{ !empty($user_data->avatar) ? $user_data->avatar : $default_img }}"
                                            class="rounded-circle avatar-xl img-thumbnail user-profile-image material-shadow"
                                            alt="user-profile-image">
                                        <div class="avatar-xs p-0 rounded-circle profile-photo-edit">

                                            <label for="profile-img-file-input" class="profile-photo-edit avatar-xs">
                                                <span
                                                    class="avatar-title rounded-circle bg-light text-body material-shadow">
                                                    <i class="ri-camera-fill"></i>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    <h5 class="fs-16 mb-1">{{ $emp_data['name'] ?? '' }}</h5>

                                </div>
                            </div>
                        </div>
                        <!--end card-->
                        <div class="card">
                            <div class="card-body">
                                <div class="nav nav-pills flex-column nav-pills-tab custom-verti-nav-pills text-center">
                                    <a class="nav-link show active" href="#">
                                        <i class="ri-user-2-line d-block fs-20 mb-1"></i> Profile
                                    </a>

                                </div>
                            </div>
                        </div>

                        <!--end card-->
                    </div>
                    <!--end col-->
                    <div class="col-md-9">
                        <div class="card mt-xxl-n5">
                            <div class="card-header">
                                <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#personalDetails"
                                            role="tab" aria-selected="true">
                                            <i class="fas fa-home"></i> Personal Details
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" data-bs-toggle="tab" href="#bankDetail" role="tab"
                                            aria-selected="false" tabindex="-1">
                                            <i class="fas fa-home"></i> Bank Details
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" data-bs-toggle="tab" href="#changePassword" role="tab"
                                            aria-selected="false" tabindex="-1">
                                            <i class="far fa-user"></i> Change Password
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body p-4">
                                <div class="tab-content">
                                    <div class="tab-pane active" id="personalDetails" role="tabpanel">
                                        @if (isset($user_id))
                                            <form action="{{ route('employees.update', [$user_id, 'user' => 'user']) }}"
                                                method="post" enctype="multipart/form-data">
                                            @else
                                                <form action="{{ route('employees.update', [$emp_id, 'emp' => 'emp']) }}"
                                                    method="post" enctype="multipart/form-data">
                                        @endif
                                        @csrf

                                        <div class="row g-3">
                                            <div class="col-lg-10">
                                                <div class="">
                                                    <label for="firstnameInput" class="form-label">Name <span
                                                            class="text-danger">*</span> </label>
                                                    <input type="text" class="form-control" id="name" name="name"
                                                        placeholder="Enter your Name" value="{{ $emp_data['name'] ?? '' }}">
                                                    @if ($errors->has('name'))
                                                        <span class="text-danger">{{ $errors->first('name') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <!--end col-->
                                            <div class="col-lg-2">
                                                <label for="" class="form-label">Gender <span
                                                        class="text-danger">*</span></label>
                                                <div>
                                                    <input class="form-check-input" class="form-check" type="radio"
                                                        name="gender" id="male" value="male"
                                                        {{ isset($emp_data['gender']) && $emp_data['gender'] == 'male' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="flexRadioDefault1">
                                                        Male
                                                    </label>
                                                    &nbsp;
                                                    <input class="form-check-input" class="form-check" type="radio"
                                                        name="gender" value="female" id="female"
                                                        {{ isset($emp_data['gender']) && $emp_data['gender'] == 'female' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="flexRadioDefault2">
                                                        Female
                                                    </label>
                                                </div>
                                                @if ($errors->has('gender'))
                                                    <div class="error text-danger">{{ $errors->first('gender') }}</div>
                                                @endif
                                            </div>
                                            <!--end col-->
                                            <div class="col-lg-6 col-xxl-3">
                                                <div class="mb-3">
                                                    <label for="dob" class="form-label">Date Of Birth <span
                                                            class="text-danger">*</span> </label>
                                                    <input type="date" class="form-control" name="dob"
                                                        id="dob" value="{{ $emp_data['dob'] ?? '' }}">
                                                    @if ($errors->has('dob'))
                                                        <span class="text-danger">{{ $errors->first('dob') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <!--end col-->
                                            <div class="col-lg-6 col-xxl-3">
                                                <div class="mb-3">
                                                    <label for="phonenumberInput" class="form-label">Phone Number <span
                                                            class="text-danger">*</span></label>
                                                    <input type="number" class="form-control" id="phonenumberInput"
                                                        placeholder="Enter your phone number" name="phone"
                                                        value="{{ $emp_data['phone'] ?? '' }}">
                                                    @if ($errors->has('phone'))
                                                        <div class="error text-danger">{{ $errors->first('phone') }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                            <!--end col-->
                                            <div class="col-lg-6 col-xxl-3">
                                                <div class="mb-3">
                                                    <label for="emailInput" class="form-label">Email Address <span
                                                            class="text-danger">*</span></label>
                                                    <input type="email" class="form-control" id="emailInput"
                                                        placeholder="Enter your email" name="email"
                                                        value="{{ $emp_data['email'] ?? '' }}">
                                                    @if ($errors->has('email'))
                                                        <div class="error text-danger">{{ $errors->first('email') }}</div>
                                                    @endif
                                                </div>
                                            </div>


                                            <div class="col-lg-6 col-xxl-3">
                                                <label for="company_doj" class="form-label ">Company Joining Date <span
                                                        class="text-danger">*</span></label>
                                                <input type="date" name="company_doj" class="form-control"
                                                    value="{{ $emp_data['company_doj'] ?? '' }}">
                                                @if ($errors->has('company_doj'))
                                                    <div class="error text-danger">{{ $errors->first('company_doj') }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="col-lg-6 col-xxl-3">
                                                <label for="documents" class="form-label">Documents <span
                                                        class="text-danger">*</span></label>
                                                <input type="file" name="documents" class="form-control">
                                                @if ($errors->has('documents'))
                                                    <div class="error text-danger">{{ $errors->first('documents') }}</div>
                                                @endif
                                            </div>



                                            <div class="col-lg-6 col-xxl-3">
                                                <div class="mb-3">
                                                    <label for="department_id" class="form-label">Select Departement <span
                                                            class="text-danger">*</span></label>
                                                    <select name="department_id" id="department_id" class="form-control">
                                                        <option value="0">Select Department</option>
                                                        @foreach ($department_list as $dept)
                                                            <option value="{{ $dept['id'] }}"
                                                                {{ isset($emp_data) && $emp_data->department_id == $dept['id'] ? 'selected' : '' }}>
                                                                {{ $dept['name'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    @if ($errors->has('department_id'))
                                                        <div class="error text-danger">
                                                            {{ $errors->first('department_id') }}</div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-lg-6 col-xxl-3">
                                                <div class="mb-3">
                                                    <label for="designation_id" class="form-label">Select Designation
                                                        <span class="text-danger">*</span></label>
                                                    <select name="designation_id" id="designation_id"
                                                        class="form-control">
                                                        <option value="0">Select Designation</option>
                                                    </select>

                                                    @if ($errors->has('designation_id'))
                                                        <div class="error text-danger">
                                                            {{ $errors->first('designation_id') }}</div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-lg-6 col-xxl-3">
                                                <div class="mb-3">
                                                    <label for="no_of_leave" class="form-label">No Of Leave </label>
                                                    <input type="number" class="form-control" name="no_of_leave"
                                                        value="{{ $emp_data['no_of_leave'] ?? '' }}">
                                                    @if ($errors->has('no_of_leave'))
                                                        <div class="error text-danger">
                                                            {{ $errors->first('no_of_leave') }}</div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-lg-3 col-xxl-3">
                                                <div class="mb-3">
                                                    <label for="role" class="form-label">Select Role
                                                        <span class="text-danger">*</span></label>
                                                    <select name="role" id="role"
                                                        class="form-control">
                                                        <option value="">Select Role</option>
                                                        @foreach ($roles as $id=>$name)
                                                             <option value="{{ $id }}" @if($user_data && $user_role_ids == $id) selected @endif>{{ $name }}</option>
                                                        @endforeach
                                                    </select>

                                                    @if ($errors->has('role'))
                                                        <div class="error text-danger">
                                                            {{ $errors->first('role') }}</div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-lg-3 col-xxl-3">
                                                <div class="mb-3">
                                                    <label for="user_type" class="form-label">Select User Type
                                                        <span class="text-danger">*</span></label>
                                                    <select name="user_type" id="user_type"
                                                        class="form-control">
                                                        <option value="">Select User Type</option>
                                                        @foreach ($user_type_list as $id=>$name)
                                                             <option value="{{ $id }}" @if($user_data && $user_data->type == $name) selected @endif>{{ $name }}</option>
                                                        @endforeach
                                                    </select>

                                                    @if ($errors->has('user_type'))
                                                        <div class="error text-danger">
                                                            {{ $errors->first('user_type') }}</div>
                                                    @endif
                                                </div>
                                            </div>



                                            <div class="col-lg-3">
                                                <div class="mb-3">
                                                    <label for="address" class="form-label">Address <span
                                                            class="text-danger">*</span></label>
                                                    <textarea class="form-control" name="address" id="address" rows="5" placeholder="Enter Address"
                                                        style="height: 116px;direction:ltr;text-align:left;">{{ $emp_data['address'] ?? '' }}</textarea>
                                                    @if ($errors->has('address'))
                                                        <div class="error text-danger">{{ $errors->first('address') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            {{-- <div class="col-lg-3">
                                                <label class="form-label">Status <span
                                                        class="text-danger mr-3">*</span></label><br>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="is_active"
                                                        id="active" value="1"
                                                        {{ isset($emp_data['is_active']) && $emp_data['is_active'] == 1 ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="active">Active</label>
                                                </div>

                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="is_active"
                                                        id="inactive" value="0"
                                                        {{ isset($emp_data['is_active']) && $emp_data['is_active'] == 0 ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="inactive">In-Active</label>
                                                </div>

                                                @if ($errors->has('is_active'))
                                                    <div class="error text-danger">{{ $errors->first('is_active') }}</div>
                                                @endif
                                            </div> --}}
                                            <!--end col-->

                                            <div class="col-lg-12">
                                                <div class="hstack gap-2 justify-content-end">
                                                    <button type="submit" class="btn btn-primary">Updates</button>
                                                    <a href="{{ route('employees.index') }}"
                                                        class="btn btn-soft-success">Cancel</a>
                                                </div>
                                            </div>
                                            <!--end col-->
                                        </div>
                                        <!--end row-->
                                        </form>
                                    </div>
                                    <!--end tab-pane-->

                                    <div class="tab-pane bank-detail-form" id="bankDetail" role="tabpanel">
                                        <div id="success-message" class="alert alert-success d-none">Employee has been
                                            updated successfully</div>
                                        <form id="bankDetailForm"
                                            action="{{ route('employees.update_bank_detail', isset($user_id) ? [$user_id, 'user' => 'user'] : [$emp_id, 'emp' => 'emp']) }}"
                                            method="POST">

                                            @csrf
                                            <div class="">
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="account_holder_name" class="form-label">Account
                                                                Holder Name <span class="text-danger">*</span> </label>
                                                            <input type="text" class="form-control"
                                                                id="account_holder_name" name="account_holder_name"
                                                                placeholder="Enter Account Holder Name"
                                                                value="{{ $emp_data['account_holder_name'] ?? '' }}">
                                                            <span class="text-danger"
                                                                id="error-account_holder_name"></span>
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="account_number" class="form-label">Account
                                                                Number<span class="text-danger">*</span> </label>
                                                            <input type="number" class="form-control"
                                                                name="account_number" id="account_number"
                                                                value="{{ $emp_data['account_number'] ?? '' }}"
                                                                placeholder="Enter Account Number">
                                                            <span class="text-danger" id="error-account_number"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!--end col-->
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="bank_name" class="form-label">Bank Name<span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" id="bank_name"
                                                                placeholder="Enter Bank Name" name="bank_name"
                                                                value="{{ $emp_data['bank_name'] ?? '' }}">
                                                            <span class="text-danger" id="error-bank_name"></span>
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="bank_identifier_code" class="form-label">Bank
                                                                identifier Code <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control"
                                                                id="bank_identifier_code"
                                                                placeholder="Enter Bank identifier Code"
                                                                name="bank_identifier_code"
                                                                value="{{ $emp_data['bank_identifier_code'] ?? '' }}">
                                                            <span class="text-danger"
                                                                id="error-bank_identifier_code"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!--end col-->
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="branch_location" class="form-label">Branch
                                                                Location <span class="text-danger">*</span></label>
                                                            <input type="text" name="branch_location"
                                                                class="form-control"
                                                                value="{{ $emp_data['branch_location'] ?? '' }}"
                                                                placeholder="Enter Branch Location">
                                                            <span class="text-danger" id="error-branch_location"></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="account" class="form-label">Account <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" name="account" class="form-control"
                                                                value="{{ $emp_data['account'] ?? '' }}"
                                                                placeholder="Enter Account">
                                                            <span class="text-danger" id="error-account"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="salary_type" class="form-label">Salary Type <span
                                                                    class="text-danger">*</span></label>
                                                            {{-- <select name="salary_type" class="form-control">
                                                    <option value="0">Select Salary Type</option>
                                                    @foreach ($salary_type_list as $key => $sal_list)
                                                        <option value="{{ $key }}" {{ (old('salary_type', $emp_data['salary_type'] ?? '') == $key) ? 'selected' : '' }}>
                                                            {{ $sal_list }}
                                                        </option>
                                                    @endforeach
                                                </select> --}}
                                                            <input type="text" name="sal" class="form-control"
                                                                value="Monthly" disabled>
                                                            <input type="hidden" name="salary_type" class="form-control"
                                                                value="1">
                                                            <span class="text-danger" id="error-salary_type"></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 ">
                                                        <div class="mb-3">
                                                            <label for="salary" class="form-label">Salary <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="number" name="salary" class="form-control"
                                                                placeholder="Enter Salary"
                                                                value="{{ $emp_data['salary'] ?? '' }}">
                                                            <span class="text-danger" id="error-salary"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                @if (isset($isSalesEmp) && $isSalesEmp == 1)
                                                    {{-- <div class="row">
                                            <div class="col-lg-6">
                                                <div class="mb-3">
                                                    <label for="min_target" class="form-label">Target Range <span class="text-danger">*</span></label>
                                                    <select name="sales_target_id" class="form-control" id="sales_target_id">
                                                        <option value="">Select Sales Target Range</option>
                                                        @foreach ($sales_target_list as $sales_list)
                                                            <option value="{{ $sales_list['id'] }}" @if (!empty($emp_data['sales_target_id']) && $sales_list['id'] == $emp_data['sales_target_id']) selected @endif>{{ $sales_list['min_target'] }} </option>
                                                        @endforeach
                                                    </select>
                                                    <span class="text-danger" id="error-sales_target_id"></span>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="mb-3">
                                                    <label for="incentive" class="form-label">Incentives (%) <span class="text-danger">*</span></label>
                                                    <input type="text" name="incentive" class="form-control"  placeholder="Enter Incentive" id="incentive" value="{{ $emp_data['incentive'] ?? 0 }}">
                                                    <span class="text-danger" id="error-incentive"></span>
                                                </div>
                                            </div>
                                        </div> --}}
                                                @endif

                                                <div class="col-lg-12">
                                                    <div class="hstack gap-2 justify-content-end">
                                                        <button type="submit" class="btn btn-primary">Updates</button>
                                                        <a href="{{ route('employees.index') }}"
                                                            class="btn btn-soft-success">Cancel</a>
                                                    </div>
                                                </div>
                                                <!--end col-->
                                            </div>

                                        </form>
                                    </div>
                                    <!--end tab-pane-->

                                    <div class="tab-pane" id="changePassword" role="tabpanel">
                                        <div id="success-message_password" class="alert alert-success d-none">Passowrd has
                                            been updated successfully</div>
                                        <div id="error-general" class="alert alert-danger d-none"></div>
                                        <form id="chagePasswordForm"
                                            action="{{ route('employees.update_pwd', isset($user_id) ? [$user_id, 'user' => 'user'] : [$emp_id, 'emp' => 'emp']) }}"
                                            method="POST">

                                            @csrf
                                            <div class="row">
                                                <div class="col-lg-6 mb-3">
                                                    <div>
                                                        <label for="old_password" class="form-label">Old
                                                            Password<span class="text-danger"> * </span></label>
                                                        <input type="password" class="form-control" id="old_password"
                                                            placeholder="Enter old password" name="old_password">
                                                        <span class="text-danger" id="error-old_password"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <!--end col-->
                                                <div class="col-lg-6 mb-3">
                                                    <div>
                                                        <label for="password" class="form-label">New
                                                            Password<span class="text-danger"> * </span></label>
                                                        <input type="password" class="form-control" id="password"
                                                            placeholder="Enter new password" name="password">
                                                        <span class="text-danger" id="error-password"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <!--end col-->
                                                <div class="col-lg-6 mb-3">
                                                    <div>
                                                        <label for="confirm_password" class="form-label">Confirm
                                                            Password<span class="text-danger"> * </span></label>
                                                        <input type="password" class="form-control" id="confirm_password"
                                                            placeholder="Enter Confirm password" name="confirm_password">
                                                        <span class="text-danger" id="error-confirm_password"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <!--end col-->
                                                <div class="col-lg-12">
                                                    <div class="mb-3">
                                                        {{-- <a href="javascript:void(0);" class="link-primary text-decoration-underline">Forgot
                                                        Password ?</a> --}}
                                                    </div>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12">
                                                    <div class="text-end">
                                                        <button type="submit" class="btn btn-success">Change
                                                            Password</button>
                                                    </div>
                                                </div>
                                                <!--end col-->
                                            </div>
                                            <!--end row-->
                                        </form>
                                        {{-- <div class="mt-4 mb-3 border-bottom pb-2">
                                        <div class="float-end">
                                            <a href="javascript:void(0);" class="link-primary">All Logout</a>
                                        </div>
                                        <h5 class="card-title">Login History</h5>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0 avatar-sm">
                                            <div class="avatar-title bg-light text-primary rounded-3 fs-18 material-shadow">
                                                <i class="ri-smartphone-line"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6>iPhone 12 Pro</h6>
                                            <p class="text-muted mb-0">Los Angeles, United States - March 16 at
                                                2:47PM</p>
                                        </div>
                                        <div>
                                            <a href="javascript:void(0);">Logout</a>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0 avatar-sm">
                                            <div class="avatar-title bg-light text-primary rounded-3 fs-18 material-shadow">
                                                <i class="ri-tablet-line"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6>Apple iPad Pro</h6>
                                            <p class="text-muted mb-0">Washington, United States - November 06
                                                at 10:43AM</p>
                                        </div>
                                        <div>
                                            <a href="javascript:void(0);">Logout</a>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0 avatar-sm">
                                            <div class="avatar-title bg-light text-primary rounded-3 fs-18 material-shadow">
                                                <i class="ri-smartphone-line"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6>Galaxy S21 Ultra 5G</h6>
                                            <p class="text-muted mb-0">Conneticut, United States - June 12 at
                                                3:24PM</p>
                                        </div>
                                        <div>
                                            <a href="javascript:void(0);">Logout</a>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 avatar-sm">
                                            <div class="avatar-title bg-light text-primary rounded-3 fs-18 material-shadow">
                                                <i class="ri-macbook-line"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6>Dell Inspiron 14</h6>
                                            <p class="text-muted mb-0">Phoenix, United States - July 26 at
                                                8:10AM</p>
                                        </div>
                                        <div>
                                            <a href="javascript:void(0);">Logout</a>
                                        </div>
                                    </div> --}}
                                    </div>


                                    <!--end tab-pane-->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end col-->
                </div>
                <!--end row-->

            </div>
        </div>
        <!-- container-fluid -->
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#bankDetailForm').on('submit', function(e) {
                e.preventDefault();

                let form = $(this);
                let url = form.attr('action');
                let formData = form.serialize();

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    success: function(res) {
                        $('#success-message')
                            .removeClass('d-none');
                        setTimeout(function() {
                            window.location.href = res.redirect_url;
                        }, 1500);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('#error-' + key).text(value[0]);
                            });

                            $('a[href="#bankDetail"]').tab('show');
                        } else {
                            console.log('Something went wrong.');
                        }
                    }
                });
            });

            $('#chagePasswordForm').on('submit', function(e) {
                e.preventDefault();

                let form = $(this);
                let url = form.attr('action');
                let formData = form.serialize();

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    success: function(res) {
                        $('#success-message_password')
                            .removeClass('d-none');
                        setTimeout(function() {
                            window.location.href = res.redirect_url;
                        }, 1500);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            let response = xhr.responseJSON;

                            if (response.cust_error == 1) {
                                $('#error-general')
                                    .text(response.message)
                                    .removeClass('d-none');

                                setTimeout(function() {
                                    $('#error-general').addClass('d-none').text('');
                                }, 3000);
                            }

                            $.each(errors, function(key, value) {
                                $('#error-' + key).text(value[0]);
                            });

                            $('a[href="#changePassword"]').tab('show');
                        } else {
                            console.log('Something went wrong.');
                        }
                    }
                });
            });

        });
    </script>
    <script>
        $(document).ready(function() {
            const selectedDept = $('#department_id').val();
            const selectedDesig = `{{ isset($emp_data) ? $emp_data->designation_id : 0 }}`;

            function loadDesignations(deptId, preselectId = 0) {
                if (deptId != 0) {
                    $.ajax({
                        url: '{{ url('employees/get-designations') }}/' + deptId,
                        type: 'GET',
                        success: function(data) {
                            $('#designation_id').empty().append(
                                '<option value="0">Select Designation</option>');
                            $.each(data, function(index, designation) {
                                let selected = (designation.id == preselectId) ? 'selected' :
                                    '';
                                $('#designation_id').append('<option value="' + designation.id +
                                    '" ' + selected + '>' + designation.name + '</option>');
                            });
                        }
                    });
                } else {
                    $('#designation_id').empty().append('<option value="0">Select Designation</option>');
                }
            }

            $('#department_id').change(function() {
                let deptId = $(this).val();
                loadDesignations(deptId);
            });

            if (selectedDept != 0 && selectedDesig != 0) {
                loadDesignations(selectedDept, selectedDesig);
            }
        });
    </script>
@endsection
