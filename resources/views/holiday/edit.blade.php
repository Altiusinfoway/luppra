@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Holiday Section</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('holidays.index') }}">Holiday</a></li>
                                <li class="breadcrumb-item active">Edit</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end page title -->
            <div class="row justify-content-center">
                <!-- Varying Modal Content -->
                <div class="col-xxl-10 col-lg-10">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title  mb-0">Holiday Edit</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('holidays.update', $holiday['id']) }}" method="POST" id="holidaysForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label" for="name">name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" id="name"
                                            placeholder="Enter name" value="{{ $holiday['name'] ?? '' }}">
                                        @if ($errors->has('name'))
                                            <div class="error text-danger">{{ $errors->first('name') }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="start_date">Start Date <span
                                                class="text-danger">*</span></label>
                                        <input type="date" name="start_date"
                                            class="form-control datepicker-range flatpickr-input active"
                                            id="datepicker-range" data-provider="flatpickr" data-range="true"
                                            value="{{ $holiday['start_date'] ?? '' }}">
                                        @if ($errors->has('start_date'))
                                            <div class="error text-danger">{{ $errors->first('start_date') }}</div>
                                        @endif
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="end_date">End Date<span
                                                class="text-danger">*</span></label>
                                        <input type="date" name="end_date"
                                            class="form-control datepicker-range flatpickr-input active"
                                            id="datepicker-range" data-provider="flatpickr" data-range="true"
                                            value="{{ $holiday['end_date'] ?? '' }}">
                                        @if ($errors->has('end_date'))
                                            <div class="error text-danger">{{ $errors->first('end_date') }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 mb-3">
                                        <label for="" class="form-label">Status <span
                                                class="text-danger">*</span></label>
                                        <div>
                                            <input class="form-check-input" class="form-check" type="radio"
                                                name="is_active" id="active" value="1"
                                                {{ isset($holiday['is_active']) && $holiday['is_active'] == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="flexRadioDefault1">
                                                Active
                                            </label>
                                            &nbsp;
                                            <input class="form-check-input" class="form-check" type="radio"
                                                name="is_active" value="0" id="in-active"
                                                {{ isset($holiday['is_active']) && $holiday['is_active'] == 0 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="flexRadioDefault2">
                                                In-Active
                                            </label>
                                        </div>
                                        @if ($errors->has('is_active'))
                                            <div class="error text-danger">{{ $errors->first('is_active') }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="mb-3">
                                        <label class="form-label" for="description">Description</label>
                                        <textarea name="description" class="form-control" id="description" rows="10"
                                            style="direction: ltr;text-align:left;">{{ $holiday['description'] }}</textarea>
                                    </div>
                                </div>

                                <div class="text-center mt-3">
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
