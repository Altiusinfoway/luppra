@extends('layouts.app')

@section('content')

<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Device Section</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('device.index') }}">Device </a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <!-- Varying Modal Content -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title  mb-0">Device Add</h5>
                        </div>
                    </div>
                    <div class="card-body">
                       <form method="POST" class="ajaxform_instant_reload" action="{{ route('device.store') }}">
					    @csrf
                        <div class="form-group row mb-4">
						<label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Device Name') }}</label>
						<div class="col-sm-12 col-md-7">
							<input type="text" name="name" placeholder="My Iphone 13 Pro" class="form-control">
						</div>
					</div>
					<div class="form-group row mb-4">
						<label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Phone Number') }}</label>
						<div class="col-sm-12 col-md-7">
							<input type="tel" name="phone" required placeholder="Enter the phone number" class="form-control">

						</div>

					</div>
					<div class="form-group row mb-4">
						<label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Webhook Url') }}</label>
						<div class="col-sm-12 col-md-7">
							<input type="url" name="webhook_url" placeholder="your webhook receiver url" class="form-control">
							<small class="text-danger">{{ env('APP_NAME').__(' will sent via post method to this url') }}</small>
						</div>

					</div>
					{{-- <div class="form-group row mb-4">
						<label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Lead Phone') }}</label>
						<div class="col-sm-12 col-md-7">
							<div class="form-check form-switch">
								<input class="form-check-input" type="checkbox" name="is_lead_mobile_number" id="is_lead_mobile_number" value="1">
								<label class="form-check-label" for="is_lead_mobile_number">{{ __('Create lead from incoming customer messages on this device') }}</label>
							</div>
							<small class="text-muted">{{ __('When enabled, new incoming WhatsApp numbers will be checked in customers and a lead will be created automatically.') }}</small>
						</div>

					</div> --}}


                            <div class="text-center mb-3">
                                <button type="submit" class="btn btn-success w-sm">Submit</button>
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
