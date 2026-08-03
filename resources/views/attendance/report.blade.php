@extends('layouts.app')

@section('page-css')
<style>
    .attendance-report-suite {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
    }

    .attendance-report-suite .hero-shell,
    .attendance-report-suite .shell-card {
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
    }

    .attendance-report-suite .hero-shell {
        background:
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.16), transparent 30%),
            radial-gradient(circle at left center, rgba(16, 185, 129, 0.12), transparent 30%),
            linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }

    .attendance-report-suite .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        padding: 7px 12px;
        border-radius: 999px;
        border: 1px solid #bfdbfe;
        background: rgba(255, 255, 255, 0.86);
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .table td, .table th {
        white-space: nowrap;
        vertical-align: middle;
    }

    .card-custom {
        padding: 1.5rem;
        border-radius: 1rem;
        box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, 0.03);
    }

    .attendance-report-suite .summary-card {
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.86);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
    }

    .attendance-report-suite .summary-card .label {
        display: block;
        margin-bottom: 8px;
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .attendance-report-suite .summary-card h3 {
        margin: 0;
        font-size: 1.7rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #0f172a;
    }

    .attendance-report-suite .toolbar-shell {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #f8fafc;
        padding: 14px 16px;
    }

    .attendance-report-suite .table-shell {
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        overflow: hidden;
        background: #fff;
    }

    .attendance-report-suite .status-banner {
        border: 1px solid #fecaca;
        border-radius: 18px;
        padding: 1rem 1.1rem;
        background: linear-gradient(180deg, #fef2f2 0%, #fffafa 100%);
        color: #b91c1c;
        box-shadow: 0 12px 26px rgba(239, 68, 68, 0.08);
        margin: 1rem 1.5rem 0;
    }

    .attendance-report-suite .status-banner .banner-label {
        display: block;
        margin-bottom: 0.3rem;
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        opacity: .82;
    }
</style>
@endsection

@section('content')
<div class="page-content attendance-report-suite">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12">
                <div class="card hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <span class="hero-eyebrow">Attendance Insights</span>
                                <h2 class="mt-3 mb-2">Attendance Report</h2>
                                <p class="text-muted mb-0">Review employee attendance slots, total hours, and in-out patterns from a cleaner reporting surface.</p>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('attendances.report') }}">Attendance</a></li>
                                        <li class="breadcrumb-item active">Report</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Report</span>
                        <h3>Attendance</h3>
                        <p class="text-muted mb-0 mt-2">Review hourly coverage and working time in a layout that reads more like a modern analytics page.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Filters</span>
                        <h3>Employee + Month</h3>
                        <p class="text-muted mb-0 mt-2">Switch staff and reporting month quickly from a more structured filter panel.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card shell-card card-custom">
                    <div class="card-header">
                        <div class="toolbar-shell d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <h5 class="card-title mb-0">Attendance Report</h5>

                        <form method="get" action="{{ route('attendances.report') }}" id="reportForm" class="d-flex gap-2 align-items-center">
                            <select name="emp_filter" class="form-control" onchange="this.form.submit();">
                                <option value="0">Select Employee</option>
                                @foreach ($employee_list as $emplist)
                                    <option value="{{ $emplist['id'] }}" {{ request('emp_filter') == $emplist['id'] ? 'selected' : '' }}>
                                        {{ $emplist['name'] }}
                                    </option>
                                @endforeach
                            </select>

                            <input type="month" class="form-control" name="month_picker" id="month_picker"
                                   onchange="this.form.submit();" value="{{ request('month_picker') }}">
                        </form>
                        </div>
                    </div>

                    @if (session('error_msg'))
                        <div class="status-banner">
                            <span class="banner-label">Report issue</span>
                            {{ session('error_msg') }}
                        </div>
                    @endif

                    <div class="table-responsive table-shell" style="max-height:600px; overflow-y: auto;">
                        <table class="table table-bordered">
                            <thead class="bg-primary text-white" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th>Date</th>
                                    @foreach($timeSlots as $slot)
                                        <th class="text-center">{{ $slot }}</th>
                                    @endforeach
                                    <th class="text-center">Total Hours</th>
                                    <th class="text-center">In-Out Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dates as $key => $date)
                                @php
                                    $formattedDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
                                    $attendancesForDate = collect($attendance_list)
                                        ->where('date', $formattedDate)
                                        ->values();
                                @endphp

                                <tr>
                                    <td class="bg-primary text-white">
                                        {{ \Carbon\Carbon::parse($date)->format('d-m-Y') }} - {{ \Carbon\Carbon::parse($date)->format('l') }}
                                    </td>

                                    @foreach($timeSlots as $slot)
                                        @php
                                            $slotStart = \Carbon\Carbon::parse("$date $slot");
                                            $slotEnd = $slotStart->copy()->addHour();
                                            $present = 0;

                                            foreach ($attendancesForDate as $att) {
                                                if (!empty($att['check_in'])) {
                                                    $checkInTime = \Carbon\Carbon::parse("$date " . $att['check_in']);
                                                    $checkOutTime = !empty($att['check_out'])
                                                        ? \Carbon\Carbon::parse("$date " . $att['check_out'])
                                                        : now();

                                                    if ($checkInTime < $slotEnd && $checkOutTime >= $slotStart) {
                                                        $present = 1;
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp
                                        <td class="text-center {{ $present ? 'bg-success text-white' : 'bg-danger-subtle' }}">
                                            {{ $present }}
                                        </td>
                                    @endforeach

                                    @php
                                        $totalSeconds = 0;
                                        $lastCheckIn = '00:00:00';
                                        $lastCheckOut = '00:00:00';

                                        foreach ($attendancesForDate as $att) {
                                            if (!empty($att['check_in'])) {
                                                $checkIn = \Carbon\Carbon::createFromFormat('H:i:s', $att['check_in'], 'Asia/Kolkata');
                                                $checkOut = !empty($att['check_out'])
                                                    ? \Carbon\Carbon::createFromFormat('H:i:s', $att['check_out'], 'Asia/Kolkata')
                                                    : now('Asia/Kolkata');

                                                $totalSeconds += $checkIn->diffInSeconds($checkOut);
                                                $lastCheckIn = $att['check_in'];
                                                $lastCheckOut = $att['check_out'] ?? '00:00:00';
                                            }
                                        }

                                        $hours = floor($totalSeconds / 3600);
                                        $minutes = floor(($totalSeconds % 3600) / 60);
                                        $totalTimeFormatted = sprintf('%02d:%02d', $hours, $minutes);
                                    @endphp

                                    <td class="text-center">{{ $totalTimeFormatted }}</td>
                                    <td class="text-center">{{ $lastCheckIn }} - {{ $lastCheckOut }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<script>
    $(document).ready(function() {

    });
</script>
@endsection
