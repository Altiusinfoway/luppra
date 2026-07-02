@extends('layouts.app')

@section('content')
<style>
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
</style>

<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Attendance Report</h4>
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('attendances.report') }}">Attendance</a></li>
                        <li class="breadcrumb-item active">Report</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Attendance Filter -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-custom">

                    <div class="card-header d-flex justify-content-between align-items-center bg-light">
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

                    @if (session('error_msg'))
                        <div class="alert alert-danger col-md-4 m-4">{{ session('error_msg') }}</div>
                    @endif

                    <div class="table-responsive"  style="max-height:600px; overflow-y: auto;">
                        <table class="table table-bordered">
                            <thead class="bg-primary text-white" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th>Date</th>
                                    @foreach($timeSlots as $slot)
                                        <th class="text-center">{{ $slot }}</th>
                                    @endforeach
                                    <th class="text-center">Total Hours</th>
                                    <th class="text-center">In-Out Time</th>
                                    {{-- <th class="text-center">Note</th>
                                    <th class="text-center">Correction</th>
                                    <th class="text-center">Leave Reason</th> --}}
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
                                        $note = '';
                                        $leave_reason = '';
                                        $lastCheckIn = '00:00:00';
                                        $lastCheckOut = '00:00:00';
                                        $attId = null;

                                        foreach ($attendancesForDate as $att) {
                                            if (!empty($att['check_in'])) {
                                                $checkIn = \Carbon\Carbon::createFromFormat('H:i:s', $att['check_in'], 'Asia/Kolkata');
                                                $checkOut = !empty($att['check_out'])
                                                    ? \Carbon\Carbon::createFromFormat('H:i:s', $att['check_out'], 'Asia/Kolkata')
                                                    : now('Asia/Kolkata');

                                                $totalSeconds += $checkIn->diffInSeconds($checkOut);
                                                $lastCheckIn = $att['check_in'];
                                                $lastCheckOut = $att['check_out'] ?? '00:00:00';
                                                $note = $att['note'] ?? '';
                                                $leave_reason = $att['leave_reason'] ?? '';
                                                $attId = $att['id'];
                                            }
                                        }

                                        $hours = floor($totalSeconds / 3600);
                                        $minutes = floor(($totalSeconds % 3600) / 60);
                                        $totalTimeFormatted = sprintf('%02d:%02d', $hours, $minutes);
                                    @endphp

                                    <td class="text-center">{{ $totalTimeFormatted }}</td>
                                    <td class="text-center">{{ $lastCheckIn }} - {{ $lastCheckOut }}</td>
                                    {{-- <td class="w-50">{{ $note }}</td>
                                    <td class="text-center">
                                        @if ($attId)
                                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#attemodal_{{ $key }}">
                                            <span class="ri-pencil-line"></span>
                                        </button>
                                        @endif
                                    </td>
                                    <td class="w-50">{{ $leave_reason }}</td> --}}
                                </tr>

                                {{-- @if ($attId)
                                <div class="modal fade" id="attemodal_{{ $key }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Attendance Update</h5>
                                            </div>
                                            <hr>
                                            <form method="post" action="{{ route('attendances.update', $attId) }}">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label>Check-in Time:</label>
                                                            <input type="time" step="1" class="form-control" name="check_in" value="{{ $lastCheckIn }}" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Check-out Time:</label>
                                                            <input type="time" step="1" class="form-control" name="check_out" value="{{ $lastCheckOut }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-2">
                                                        <div class="col-md-6">
                                                            <label>Note:</label>
                                                            <textarea name="note" class="form-control">{{ $note }}</textarea>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Leave Reason:</label>
                                                            <textarea name="leave_reason" class="form-control">{{ $leave_reason }}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif --}}
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
