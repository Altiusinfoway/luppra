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
                            <h4>{{ __('Scan the QR Code On Your Whatsapp Mobile App') }}</h4>
                            <div class="card-header-action none loggout_area">
                                <a href="javascript:void(0)" class="btn btn-sm btn-neutral logout-btn"
                                    data-id="{{ $device->uuid }}">
                                    <i class="fas fa-sign-out-alt"></i>&nbsp{{ __('Logout') }}
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-center qr-area">


                                <div class="justify-content-center">
                                    &nbsp&nbsp
                                    <div class="spinner-grow text-primary" role="status">
                                        <span class="sr-only">{{ __('Loading...') }}</span>
                                    </div>
                                    <br>
                                    <p><strong>{{ __('QR Loading.....') }}</strong></p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="alert bg-gradient-red server_disconnect none text-white " role="alert">
                                {{ __('Opps! Server Disconnected 😭') }}
                            </div>

                            <div class="alert bg-gradient-green logged-alert none text-white " role="alert">
                                 {{ __('Device Connected ') }}  <img src="{{ asset('public/uploads/firework.png') }}" alt="">
                            </div>
                        </div>
                    </div>


                    <!-- new code -->
                      <div class="card card-neutral none helper-box">
                    <div class="card-body">
                        <div class="row">

                            {{-- <div class="col-sm-6 mb-2 mt-2">
                                <a href="{{ url('device/chats/'.$device->uuid) }}" class="btn btn-neutral col-12 bg-primary text-white">
                                    <i class="fi fi-rs-paper-plane"></i>&nbsp {{ __('My Chat list') }}
                                </a>
                            </div> --}}

                            {{-- <div class="col-sm-6 mb-2 mt-2">
                                <a href="{{ url('/user/device/groups/'.$device->uuid) }}" class="btn btn-neutral col-12 bg-primary text-white">
                                    <i class="fi fi-rs-paper-plane"></i>&nbsp {{ __('My Group list') }}
                                </a>
                            </div>--}}

                            @if($check_device_active)
                            <div class="col-sm-6 mt-2">
                                <a href="{{ url('/sent-text-message') }}" class="btn btn-neutral col-12 bg-primary text-white">
                                    <i class="fi fi-rs-paper-plane"></i>&nbsp {{ __('Send a message') }}
                                </a>
                            </div>
                            @endif
                           {{-- <div class="col-sm-6 mt-3">
                                <a href="{{ url('/user/bulk-message/create') }}" class="btn btn-neutral col-12 bg-primary text-white">
                                    <i class="fi fi-rs-rocket-lunch"></i>&nbsp {{ __('Send bulk message') }}
                                </a>
                            </div> --}}

                        </div>
                    </div>
                </div>

                </div><!--end col-->





                <div class="p-3 col-md-12">
                    <input type="hidden" id="device_status" value="{{ $device->status }}">
                    <input type="hidden" id="base_url" value="{{ url('/') }}">
                    <input type="hidden" id="device_id" value="{{ $device->uuid }}">
                </div>

            </div><!--end row-->

        </div>
        <!-- container-fluid -->
    </div>


@endsection

@section('page-script')
<script src="{{ asset('public/build/assets/js/pages/user/confetti.browser.min.js') }}"></script>
<script src="{{ asset('public/build/assets/js/pages/user/qr.js') }}"></script>
@endsection
