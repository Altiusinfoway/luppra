<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\WorkingHours;
use App\Models\Utility;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        // $emp = Employee::where('user_id', \Auth::user()->id)->first();
        // $data_list_all['current_date'] = date("Y-m-d");
        // $data_list_all['attendance_list'] = [];
        // $data_list_all['latest_attendance'] = null;
        // $data_list_all['show_check_in'] = false;
        // $data_list_all['show_check_out'] = false;



        // if ($request->ajax())
        // {
        //     try
        //     {

        //         $attendanceData = Attendance::select('id', 'date', 'check_in', 'check_out', 'total_hours');

        //         if (!empty($emp)) {

        //             $latest = Attendance::where('employee_id', $emp->id)->orderBy('id', 'desc')->first();
        //             $data_list_all['latest_attendance'] = $latest;

        //             $data_list_all['show_check_in'] = !$latest || ($latest && $latest->check_out !== null);
        //             $data_list_all['show_check_out'] = $latest && $latest->check_out === null;

        //             $attendanceData = $attendanceData->where('employee_id', $emp['id']);
        //         }

        //         $attendanceData = $attendanceData->orderBy('date')
        //             ->get();

        //         $grouped = $attendanceData->groupBy('date');

        //         $finalData = collect();
        //         $srNo = 1;

        //         foreach ($grouped as $date => $rows) {
        //             // Header row
        //             $finalData->push([
        //                 'sr_no' => '',
        //                 'date' => "<strong>Attendance of $date</strong>",
        //                 'check_in' => '',
        //                 'check_out' => '',
        //                 'total_hours' => '',
        //                 'row_type' => 'header',
        //             ]);

        //             // Detail rows
        //             foreach ($rows as $row) {
        //                 $finalData->push([
        //                     'sr_no' => $row->id,
        //                     'date' => $row->date,
        //                     'check_in' => $row->check_in,
        //                     'check_out' => $row->check_out ?: '0',
        //                     'total_hours' => $row->total_hours,
        //                     'row_type' => 'detail',
        //                 ]);
        //             }

        //             // Total row
        //             $totalSeconds = $rows->sum(function ($item) {
        //                 return strtotime("1970-01-01 {$item->total_hours} UTC");
        //             });

        //             $finalData->push([
        //                 'sr_no' => '',
        //                 'date' => '',
        //                 'check_in' => '',
        //                 'check_out' => '<strong>Total:</strong>',
        //                 'total_hours' => '<strong>' . gmdate('H:i:s', $totalSeconds) . '</strong>',
        //                 'row_type' => 'total',
        //             ]);
        //         }

        //         return DataTables::of($finalData)
        //             ->rawColumns(['date', 'check_out', 'total_hours'])
        //             ->make(true);
        //     } catch (\Exception $e) {

        //         dd($e);

        //         return response()->json([
        //             'error' => 'Server Error: ' . $e->getMessage()
        //         ], 500);
        //     }
        // }
        // return view('attendance.index', $data_list_all);

        $emp = Employee::where('user_id', \Auth::user()->id)->first();
        $data_list_all['current_date'] = date("Y-m-d");
        $data_list_all['attendance_list'] = [];
        $data_list_all['latest_attendance'] = null;
        $data_list_all['show_check_in'] = false;
        $data_list_all['show_check_out'] = false;
        $data_list_all['employee_list'] = $this->scopedEmployeeQuery()->pluck('name','id');



        if (!empty($emp)) {
            $latest = Attendance::where('employee_id', $emp->id)->orderBy('id', 'desc')->first();
            $data_list_all['latest_attendance'] = $latest;

            $data_list_all['show_check_in'] = !$latest || ($latest && $latest->check_out !== null);
            $data_list_all['show_check_out'] = $latest && $latest->check_out === null;
        }

        if ($request->ajax())
        {
            try
            {

                $query = $this->scopedAttendanceQuery()
                    ->with(['getEmployee'])
                    ->orderBy('date')
                    ->select('id', 'date', 'check_in', 'check_out', 'total_hours','employee_id');


                if (\Auth::user()->type == 'company') {
                    if ($request->employee_filter_id) {
                        $query->where('employee_id', $request->employee_filter_id);
                    }
                } else {
                    $query->where('employee_id', $emp['id']);
                }

                //filter
                if ($request->employee_filter_id)
                {
                    $query->where('employee_id', $request->employee_filter_id);
                }

                $attendanceData = $query->get();

                $grouped = $attendanceData->groupBy('date');

                $finalData = collect();
                $srNo = 1;

                foreach ($grouped as $date => $rows)
                {
                    // Header row
                    $finalData->push([
                        'sr_no' => '',
                        'emp_name'=>'',
                        'date' => "<strong>Attendance of $date</strong>",
                        'check_in' => '',
                        'check_out' => '',
                        'total_hours' => '',
                        'row_type' => 'header',
                    ]);

                    // Detail rows
                    foreach ($rows as $row) {
                        $finalData->push([
                            'sr_no' => $row->id,
                            'emp_name' => (\Auth::user()->type == 'company') ? ($row->getEmployee->name ?? '') : '',
                            'date' => $row->date,
                            'check_in' => $row->check_in,
                            'check_out' => $row->check_out ?: '0',
                            'total_hours' => $row->total_hours,
                            'row_type' => 'detail',
                        ]);
                    }

                    // Total row
                    $totalSeconds = $rows->sum(function ($item) {
                        return strtotime("1970-01-01 {$item->total_hours} UTC");
                    });

                    $finalData->push([
                        'sr_no' => '',
                        'emp_name'=>'',
                        'date' => '',
                        'check_in' => '',
                        'check_out' => '<strong>Total:</strong>',
                        'total_hours' => '<strong>' . gmdate('H:i:s', $totalSeconds) . '</strong>',
                        'row_type' => 'total',
                    ]);
                }

                return DataTables::of($finalData)
                    ->rawColumns(['date', 'check_out', 'total_hours','emp_name'])
                    ->make(true);
            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        }
        return view('attendance.index', $data_list_all);

    }

    public function store(Request $request)
    {
        $input = $request->all();

        $emp = Employee::where('user_id', \Auth::user()->id)->first();

        if (!$emp) {
            return redirect()->back()->with(['error_msg' => 'Employee not found.']);
        }

        $current_date = date("Y-m-d");
        $current_time = Carbon::now('Asia/Kolkata')->format('H:i:s');

        $input['employee_id'] = $emp['id'];


        //check working hours inside login or not
        $date = date('Y-m-d');
        $dayNumber = date('N', strtotime($date));
        $check_working = Utility::checkEmpLoginBtwWorkingHrs($dayNumber,$current_time,0);
        if($check_working != 1)
        {
            return redirect()->back()->with(['error' => $check_working]);
        }

        if (!empty($input['action']) && $input['action'] == 'check_in') {
            $input['date'] = $current_date;
            $input['check_in'] = $current_time;
            Attendance::create($input);
        }
        return redirect()->back()->with(['danger' => 'Attendance has been added successfully']);
    }

    public function report(Request $request)
    {
        $startTime = '09:00 AM';
        $endTime = '06:00 PM';

        $timeSlots = [];
        $start = Carbon::createFromFormat('h:i A', $startTime);
        $end = Carbon::createFromFormat('h:i A', $endTime);
        $emp_filter = $request->input('emp_filter');
        $month_picker = $request->input('month_picker');

        while ($start <= $end) {
            $timeSlots[] = $start->format('h:i A');
            $start->addHour();
        }

        if (empty($request->month_picker)) {
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
        } else {
            $startOfMonth = Carbon::createFromFormat('Y-m', $month_picker)->startOfMonth();
            $endOfMonth = Carbon::createFromFormat('Y-m', $month_picker)->endOfMonth();
        }

        $dates = [];

        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        $data['timeSlots'] = $timeSlots;
        $data['dates'] = $dates;

        $attendance_ids = $this->scopedAttendanceQuery()->pluck('employee_id')->unique();
        $data['employee_list'] = $this->scopedEmployeeQuery()->whereIn('id', $attendance_ids)->get();
        $attendanceQuery = $this->scopedAttendanceQuery();
        if (!empty($emp_filter)) {
            $attendanceQuery->where('employee_id', $emp_filter);
        }
        $data['attendance_list'] = $attendanceQuery->get()->toArray();

        return view('attendance.report', $data);
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();
        $attendace_id = $this->scopedAttendanceQuery()->findOrFail($id);

        $checkInRaw = $request->input('check_in');
        $checkOutRaw = $request->input('check_out');

        $checkIn = Carbon::createFromFormat('H:i:s', $checkInRaw);
        $checkOut = Carbon::createFromFormat('H:i:s', $checkOutRaw);

        $diffInSeconds = $checkIn->diffInSeconds($checkOut);
        $hours = floor($diffInSeconds / 3600);
        $minutes = floor(($diffInSeconds % 3600) / 60);
        $seconds = $diffInSeconds % 60;

        $totalTimeFormatted = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

        $inp['check_in'] = $checkInRaw;
        $inp['check_out'] = $checkOutRaw;
        $inp['total_hours'] = $totalTimeFormatted;
        $inp['note'] = $input['note'];
        $inp['leave_reason'] = $input['leave_reason'];

        $attendace_id->update($inp);

        return redirect()->back()->with('success', 'Attendance updated successfully');
    }

    public function attendance_update(Request $request, $id)
    {
        $input = $request->all();
        $emp = Employee::where('user_id', \Auth::user()->id)->first();

        if (!$emp) {
            return redirect()->back()->with(['error_msg' => 'Employee not found.']);
        }

        $current_time = Carbon::now('Asia/Kolkata')->format('H:i:s');

        $attendance = Attendance::where('id', $id)->where('employee_id', $emp->id)->first();
        if (!$attendance || !$attendance->check_in) {
            return redirect()->back()->with(['error_msg' => 'Attendance or check-in time not found.']);
        }

        $checkIn = Carbon::createFromFormat('H:i:s', $attendance->check_in, 'Asia/Kolkata');
        $checkOutTime = $current_time;

        // check working days time-------------
        $dayNumber = date('N');
        $workingCheck = Utility::checkEmpLoginBtwWorkingHrs($dayNumber, $current_time, 1);

        if ($workingCheck === 0) {
            return redirect()->back()->with(['error' => 'Working time not available.']);
        }
        if (is_string($workingCheck)) {
            $checkOutTime = $workingCheck;
        }
        //---------------------

        $checkOut = Carbon::createFromFormat('H:i:s', $checkOutTime, 'Asia/Kolkata');

        $diffInSeconds = $checkIn->diffInSeconds($checkOut);

        $hours = floor($diffInSeconds / 3600);
        $minutes = floor(($diffInSeconds % 3600) / 60);
        $seconds = $diffInSeconds % 60;

        $totalTimeFormatted = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

        $attendance->update([
            'check_out'   => $checkOut->format('H:i:s'),
            'total_hours' => $totalTimeFormatted,
            'is_present'  => 1,
        ]);

        return redirect()->back()->with(['success' => 'Attendance updated successfully']);

    /* old code
        $input = $request->all();

        $emp = Employee::where('user_id', \Auth::user()->id)->first();
        if (!$emp) {
            return redirect()->back()->with(['error_msg' => 'Employee not found.']);
        }

        $current_date = date("Y-m-d");
        $current_time = Carbon::now('Asia/Kolkata')->format('H:i:s');
        $input['employee_id'] = $emp['id'];
        $attendaceId = Attendance::where('id', $id)->where('employee_id', $emp['id'])->first();

        if ($attendaceId) {
            if (!$attendaceId || !$attendaceId->check_in) {
                return redirect()->back()->with(['error_msg' => 'Check-in time not found.']);
            }

            $checkIn = Carbon::createFromFormat('H:i:s', $attendaceId->check_in, 'Asia/Kolkata');
            $checkOut = Carbon::createFromFormat('H:i:s', $current_time, 'Asia/Kolkata');

            //check working hours inside login or not ------------------
            $date = date('Y-m-d');
            $dayNumber = date('N', strtotime($date));
            $check_working = Utility::checkEmpLoginBtwWorkingHrs($dayNumber,$current_time,1);

            if ($check_working === 0) {
                return redirect()->back()->with(['error' => 'Working time not available']);
            }

            if (is_string($check_working)) {
                $checkOutTime = $check_working;
            }
            //------------------------

            $diffInSeconds = $checkIn->diffInSeconds($checkOut);

            $hours = floor($diffInSeconds / 3600);
            $minutes = floor(($diffInSeconds % 3600) / 60);
            $seconds = $diffInSeconds % 60;

            $totalTimeFormatted = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

            $inp['check_out'] = $current_time;
            $inp['total_hours'] = $totalTimeFormatted;
            $inp['is_present'] = 1;
            $attendaceId->update($inp);
        }

        return redirect()->back()->with(['success' => 'Attendance has been updated successfully']);
        */
    }

    private function scopedEmployeeQuery()
    {
        $query = Employee::query();
        if (\Auth::user()->type == 'Sales') {
            $query->where('user_id', \Auth::id());
        } else {
            $query->whereHas('getUser', function ($q) {
                $q->where('created_by', \Auth::user()->creatorId());
            });
        }
        return $query;
    }

    private function scopedAttendanceQuery()
    {
        $query = Attendance::query();
        if (\Auth::user()->type == 'Sales') {
            $emp = Employee::where('user_id', \Auth::id())->first();
            $query->where('employee_id', $emp?->id ?? 0);
        } else {
            $query->whereHas('getEmployee.getUser', function ($q) {
                $q->where('created_by', \Auth::user()->creatorId());
            });
        }
        return $query;
    }
}
