@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Bank Account Detail</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('bank-account-details.index') }}">Bank Account
                                        Detail </a></li>
                                <li class="breadcrumb-item active">Create</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end page title -->
            <div class="row justify-content-center">
                <!-- Varying Modal Content -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title  mb-0">Bank Account Detail Add</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('bank-account-details.store') }}" method="POST" id="salesTargetForm">
                                @csrf

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="account_holder_name">Account Holder Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="account_holder_name" class="form-control"
                                            placeholder="Enter Account Holder Name"
                                            value="{{ old('account_holder_name') }}">
                                        @if ($errors->has('account_holder_name'))
                                            <div class="error text-danger">{{ $errors->first('account_holder_name') }}</div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="account_no">Account Number<span
                                                class="text-danger">*</span></label>
                                        <input type="number" name="account_no" class="form-control"
                                            placeholder="Enter Account Number" value="{{ old('account_no') }}">
                                        @if ($errors->has('account_no'))
                                            <div class="error text-danger">{{ $errors->first('account_no') }}</div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="account_type">Account Type<span
                                                class="text-danger">*</span></label>
                                        <select name="account_type" class="form-control">
                                            <option value="">Select Account Type</option>
                                            @foreach (\App\Models\BankDetail::getAccountTypes() as $key => $val)
                                                <option value="{{ $key }}"
                                                    {{ old('account_type') == $key ? 'selected' : '' }}>
                                                    {{ $val }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('account_type'))
                                            <div class="error text-danger">{{ $errors->first('account_type') }}</div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="bank_name">Bank Name<span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="bank_name" class="form-control"
                                            placeholder="Enter Bank Name" value="{{ old('bank_name') }}">
                                        @if ($errors->has('bank_name'))
                                            <div class="error text-danger">{{ $errors->first('bank_name') }}</div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="branch_name">Branch Name<span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="branch_name" class="form-control"
                                            placeholder="Enter Branch Name" value="{{ old('branch_name') }}">
                                        @if ($errors->has('branch_name'))
                                            <div class="error text-danger">{{ $errors->first('branch_name') }}</div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="ifsc_code">IFSC Code<span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="ifsc_code" class="form-control"
                                            placeholder="Enter IFSC Code" value="{{ old('ifsc_code') }}">
                                        @if ($errors->has('ifsc_code'))
                                            <div class="error text-danger">{{ $errors->first('ifsc_code') }}</div>
                                        @endif
                                    </div>
                                    <div class="text-center mb-3">
                                        <button type="submit" class="btn btn-sm btn-success w-sm">Submit</button>
                                    </div>
                                </div>



                            </form>
                            <!-- end card body -->
                        </div>
                    </div>
                </div><!--end col-->
            </div><!--end row-->

        </div>
        <!-- container-fluid -->
    </div>
@endsection
