<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Entity;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\Utility;
use App\Models\WorkingHours;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class LeaveController extends Controller
{

    public function add_leave(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            Log::info('------ start add_leave ------');
            Log::info('Request :-', $request->all());

            $input = $request->all();

            $employee = Employee::where('user_id', $user->id)->first();

            if ($employee) {

                $request->validate([
                    'start_date' => 'required|date',
                    'end_date'   => 'required|date|after_or_equal:start_date',
                    'leave_type' => 'required|not_in:0',
                    'hours_leave'  => 'required',
                    'reason'     => 'required',
                ]);

                $input['employee_id'] = $employee->id;
                $input['status'] = 1;
            } else {
                return Utility::return_response(false, 'Employee not found.', null, 404);
            }

            // Calculate total days
            $start = Carbon::parse($request->start_date);
            $end   = Carbon::parse($request->end_date);
            $input['total_days'] = $start->diffInDays($end) + 1;

            // Hours leave
            $input['hours_leave'] = $request->hours_leave ?? null;

            $leave = Leave::create($input);

            Log::info('------ end add_leave ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(
                true,
                'Leave has been added successfully.',
                $leave,
                200
            );
        } catch (JWTException $e) {
            Log::info('add leave error ', [$e->getMessage()]);
            return Utility::return_response(false, "Token invalid or not provided.", "", 500);
        }
    }

    public function edit_leave(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            Log::info('------ start edit_leave ------');
            Log::info('Request :-', $request->all());

            $employee = Employee::where('user_id', $user->id)->first();

            if (!$employee) {
                return Utility::return_response(false, 'Employee not found.', null, 404);
            }

            $request->validate([
                'leave_id'    => 'required|exists:leaves,id',
                'start_date'  => 'required|date',
                'end_date'    => 'required|date|after_or_equal:start_date',
                'leave_type'  => 'required|not_in:0',
                'hours_leave' => 'required',
                'reason'      => 'required',
            ]);

            $leave = Leave::where('id', $request->leave_id)
                ->where('employee_id', $employee->id)
                ->first();

            if (!$leave) {
                return Utility::return_response(false, 'Leave not found or access denied.', null, 404);
            }

            if ($leave->status != 1) {
                return Utility::return_response(
                    false,
                    'Approved or rejected leave cannot be edited.',
                    "",
                    422
                );
            }

            $input = $request->only([
                'leave_id',
                'start_date',
                'end_date',
                'leave_type',
                'hours_leave',
                'reason'
            ]);

            // Recalculate total days
            $start = Carbon::parse($request->start_date);
            $end   = Carbon::parse($request->end_date);
            $input['total_days'] = $start->diffInDays($end) + 1;

            // Reset status to pending after edit
            $input['status'] = 1;

            $leave->update($input);

            Log::info('------ end edit_leave ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(
                true,
                'Leave has been updated successfully.',
                $leave,
                200
            );
        } catch (ValidationException $e) {
            return Utility::return_response(false, 'Validation error', $e->errors(), 422);
        } catch (JWTException $e) {
            Log::info('edit leave error ', [$e->getMessage()]);
            return Utility::return_response(false, 'Token invalid or not provided.', '', 500);
        }
    }

    public function leave_list(Request $request)
{
    try {
        $user = JWTAuth::parseToken()->authenticate();

        Log::info('------ start leave_list ------');
        Log::info('Request :-', $request->all());

        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return Utility::return_response(false, 'Employee not found.', null, 404);
        }

        $query = Leave::with(['get_leave_type'])
            ->select(
                'id',
                'start_date',
                'end_date',
                'total_days',
                'leave_type',
                'hours_leave',
                'reason',
                'status',
                'remark'
            )
            ->where('employee_id', $employee->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        $data = $query->orderBy('start_date', 'desc')->get();

        foreach ($data as $itm) {
            $itm->status_name = match ((int) $itm->status) {
                1 => 'Pending',
                2 => 'Accept',
                3 => 'Reject',
                default => 'Unknown',
            };

            $itm->hours_leave_name = match ((int) $itm->hours_leave) {
                1 => 'Half Day',
                2 => 'Full Day',
                default => '',
            };
        }

        Log::info('------ end leave_list ------');
        Log::info('------------------------------------------------------------------------------');

        return Utility::return_response(true, "Leaves list.", $data, 200);

    } catch (JWTException $e) {
        return Utility::return_response(false, "Token invalid or not provided.", "", 500);
    } catch (\Throwable $e) {
        Log::info('leave_list error ', [$e->getMessage()]);
        return Utility::return_response(false, $e->getMessage(), "", 500);
    }
}
}
