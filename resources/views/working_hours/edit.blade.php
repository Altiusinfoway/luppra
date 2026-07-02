@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Working Hours</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('holidays.index') }}">Working Hours</a></li>
                                <li class="breadcrumb-item active">Edit</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end page title -->
            <div class="row justify-content-center">
                <!-- Varying Modal Content -->
                <div class="col-xxl-10 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title  mb-0">Working Hours Edit</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('working-hours.update', $working_id['id']) }}" method="POST"
                                id="holidaysForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label" for="day">Select Day <span
                                                class="text-danger">*</span></label>
                                        <select name="day" class="form-control" id="day">
                                            <option value="">Select Day</option>
                                            @foreach ($day_list as $key => $dy)
                                                <option value="{{ $key }}"
                                                    {{ old('day', $working_id['day'] ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $dy }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('day'))
                                            <div class="error text-danger">{{ $errors->first('day') }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="start_time">Start Time <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="start_time" class="form-control " id="start_time"
                                            value="{{ $working_id['start_time'] }}">
                                        @if ($errors->has('start_time'))
                                            <div class="error text-danger">{{ $errors->first('start_time') }}</div>
                                        @endif
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="end_time">End Time<span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="end_time" class="form-control " id="end_time"
                                            value="{{ $working_id['end_time'] }}">
                                        @if ($errors->has('end_time'))
                                            <div class="error text-danger">{{ $errors->first('end_time') }}</div>
                                        @endif
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

@section('page-script')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            flatpickr("#start_time", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true
            });
            flatpickr("#end_time", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true
            });
        });
    </script>
@endsection
