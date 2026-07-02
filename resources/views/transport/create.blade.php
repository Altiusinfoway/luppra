@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Transport </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('transports.index') }}">Transport </a>
                                </li>
                                <li class="breadcrumb-item active">Create</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row justify-content-center">
                <!-- Varying Modal Content -->
                <div class="col-xl-12 col-xxl-10 ">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title  mb-0">Transport Add</h5>
                            </div>
                        </div>

                        <div class="card-body">

                            @include('transport._create')

                        </div>


                    </div>
                </div><!--end col-->
            </div><!--end row-->

        </div>
            <!-- container-fluid -->

    </div>
@endsection

