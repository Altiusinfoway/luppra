@extends('layouts.app')

@section('page-css')
    <style>
        .hr-form-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .hr-form-suite .hero-shell,
        .hr-form-suite .form-shell {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
        }

        .hr-form-suite .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.76);
            border: 1px solid #dbeafe;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .hr-form-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.86);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .hr-form-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .hr-form-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
        }

        .hr-form-suite .section-card {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 16px;
        }
    </style>
@endsection

@section('content')
<div class="page-content hr-form-suite">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-8">
                                <span class="hero-eyebrow">Schedule Setup</span>
                                <h1 class="mb-3">Create Working Hours</h1>
                                <p class="text-muted mb-0">Add a working-hours record with day and time details inside the same clean scheduling form shell.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('working-hours.index') }}">Working Hours</a></li>
                                        <li class="breadcrumb-item active">Create</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-3 mb-4 justify-content-center">
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Schedule Setup</span>
                        <h3>New Day Slot</h3>
                        <p class="text-muted mb-0 mt-2">Create a weekly timing block with the same cleaner form rhythm used across the refreshed admin workflows.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Fields</span>
                        <h3>Day + Time</h3>
                        <p class="text-muted mb-0 mt-2">Keep business-day selection and operating hours grouped into one focused scheduling card.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-xxl-10 col-lg-12">
                <div class="card form-shell">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title mb-0">Working Hours Add</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('working-hours.store') }}" method="POST" id="holidaysForm">
                            @csrf
                            <div class="section-card mb-3">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label" for="day">Select Day <span class="text-danger">*</span></label>
                                        <select name="day" class="form-control" id="day">
                                            <option value="">Select Day</option>
                                            @foreach ($day_list as $key => $dy)
                                                <option value="{{ $key }}" {{ old('day', $emp_data['day'] ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $dy }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($errors->has('day'))
                                            <div class="error text-danger">{{ $errors->first('day') }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="start_time">Start Time <span class="text-danger">*</span></label>
                                        <input type="text" name="start_time" class="form-control" id="start_time">
                                        @if($errors->has('start_time'))
                                            <div class="error text-danger">{{ $errors->first('start_time') }}</div>
                                        @endif
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="end_time">End Time<span class="text-danger">*</span></label>
                                        <input type="text" name="end_time" class="form-control" id="end_time">
                                        @if($errors->has('end_time'))
                                            <div class="error text-danger">{{ $errors->first('end_time') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mb-3">
                                <button type="submit" class="btn btn-primary w-sm">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
 document.addEventListener("DOMContentLoaded", function () {
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
