<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Models\Leave;
use App\Models\Employee;
use App\Models\User;
use App\Models\Role;
use App\Models\LeaveType;
use App\Models\Utility;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax())
        {
            try
            {
                $query = $this->scopedLeavesQuery()
                    ->with(['employees.departments','get_leave_type'])
                    ->select('id','employee_id','start_date','end_date','total_days','leave_type','hours_leave','reason','status','remark');

                if ($request->employee_filter) {
                    $query->where('employee_id', $request->employee_filter);
                }

                if ($request->role_filter) {
                    $user_ids = User::where('type', $request->role_filter)
                        ->where('created_by', \Auth::user()->creatorId())
                        ->pluck('id');
                    $employee_ids = Employee::whereIn('user_id', $user_ids)->pluck('id');
                    $query->whereIn('employee_id', $employee_ids);
                }

                if ($request->status_filter) {
                   $query->where('status',$request->status_filter);
                }

                if ($request->start_date_filter) {
                    $query->whereDate('start_date', '>=', $request->start_date_filter);
                }

                if ($request->end_date_filter) {
                    $query->whereDate('end_date', '<=', $request->end_date_filter);
                }

                $data = $query->orderBy('id', 'desc')->get();

                $user = auth()->user();

                return datatables()->of($data)
                ->addIndexColumn()
                    ->addColumn('emp_name', function ($row) use ($user) {
                        if (in_array($user->type, ['HRM', 'company'])) {
                            return $row->employees->name ?? '-';
                        } else {
                            return '';
                        }
                    })
                    ->addColumn('department_name', function ($row) use ($user) {
                        if (in_array($user->type, ['HRM', 'company'])) {
                            return $row->employees->departments->name ?? '-';
                        } else {
                            return '';
                        }
                    })
                    ->addColumn('leave_type_name', function ($row) {
                         return $row->get_leave_type->name ?? '';
                    })
                     ->addColumn('hours_leave_name', function ($row) {
                        if($row['hours_leave'] == 1)
                        {
                            return "half";
                        }
                        else
                        {
                            return "full";
                        }
                    })
                    ->addColumn('status_name', function ($row) {
                        if($row->status == 1)
                        {
                            return '<span class="bg-primary badge p-2 fs-6">Pending</span>';
                        }
                        elseif($row->status == 2)
                        {
                            return '<span class="bg-success badge p-2 fs-6">Accept</span>';
                        }
                        else
                        {
                            return '<span class="bg-danger badge p-2 fs-6">Reject</span>';
                        }
                    })
                    ->addColumn('action', function($row)
                    {
                        $current_date = date('Y-m-d');
                        $isCompanyOrHRM = auth()->user()->type == 'HRM' || auth()->user()->type == 'company' ;
                        $isLeaveToday = $row->start_date <= $current_date && $row->end_date >= $current_date;

                        if ((!$isCompanyOrHRM && $isLeaveToday) || $isCompanyOrHRM)
                        {
                            $modalId = "statusChangeModal" . $row->id;

                            $actions = '
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-more-fill align-middle"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">';

                            if (\Auth::user()->can('edit leave')) {
                                $actions .= '
                                <li>
                                    <a href="' . route('leaves.edit', $row->id) . '" class="dropdown-item edit-item-btn">
                                        <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                    </a>
                                </li>';
                            }

                            if (\Auth::user()->can('delete leave'))
                            {
                                $actions .= '
                                <li>
                                    <a class="dropdown-item remove-item-btn"
                                        data-delete-popup="true"
                                        data-bs-original-title="You are about to delete a Leave?"
                                        data-bs-original-description="Deleting your Leave will remove all of your information from our database."
                                        data-url="' . route('leaves.delete', $row->id) . '"
                                        data-method="DELETE"
                                        data-cb="afterDelete"
                                        href="javascript:void(0)">
                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Cancel
                                    </a>
                                </li>';
                            }

                            if ($isCompanyOrHRM)
                            {
                                $actions .= '
                                <li>
                                    <form action="' . route('leaves.update_status_reject', $row->id) . '" method="POST">
                                        ' . csrf_field() . '
                                        <input type="hidden" name="status" value="2">
                                        <button type="submit" class="dropdown-item">
                                            <i class="ri-arrow-right-up-fill align-bottom me-2 text-muted"></i> Accept
                                        </button>
                                    </form>
                                </li>

                                <li>
                                    <a href="javascript:void(0)" class="dropdown-item"
                                        data-bs-toggle="modal"
                                        data-bs-target="#' . $modalId . '">
                                        <i class="ri-close-circle-line align-bottom me-2 text-muted"></i> Reject
                                    </a>
                                </li>';
                            }

                            $actions .= '</ul></div>';
                            $actions .= '
                            <div class="modal fade" id="' . $modalId . '" tabindex="-1" aria-labelledby="' . $modalId . 'Label" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <form action="' . route('leaves.update_status_reject', $row->id) . '" method="post">
                                        ' . csrf_field() . '
                                        <input type="hidden" name="status" value="3">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="' . $modalId . 'Label">Leave Status Update</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <h4>Do you want to Reject this leave request?</h4>
                                                <div class="mt-3">
                                                    <label>Enter Remark<span class="text-danger">*</span></label>
                                                    <textarea name="remark" rows="5" class="form-control" required></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-danger">Reject</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>';

                            return $actions;
                        }

                    })
                    ->rawColumns(['leave_type_name','status_name','action','hours_leave_name'])
                    ->setRowClass('main-row')
                    ->make(true);
            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        }

        $data['emp_list'] = $this->scopedEmployeesQuery()->get();
        $data['role_list']= Role::where('created_by', '=', \Auth::user()->creatorId())->where('name', '!=', 'client')->get();

        return view('leave.index',$data);
    }

    public function create()
    {
        $data['emp_list'] = $this->scopedEmployeesQuery()->get();
        $data['leave_type_list'] = LeaveType::pluck('name','id');
        return view('leave.create',$data);
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $employee = Employee::where('user_id',\Auth::user()->id)->first();

        if(\Auth::user()->type == 'HRM' || \Auth::user()->type == 'company')
        {
            $request->validate([
                'start_date'=>'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'leave_type' => 'required|not_in:0',
                'reason'=>'required',
                'employee_id'=>'required',
                'status'=>'required|not_in:0',
                'hours_leave'=>'required',
            ]);
            $input['employee_id'] = $request->employee_id;
        }
        elseif($employee)
        {
            $request->validate([
                'start_date'=>'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'leave_type' => 'required|not_in:0',
                'reason'=>'required',
            ]);
            $input['employee_id'] = $employee->id;
        }
        else
        {
            return redirect()->back()->with(['error_msg'=>'Employee not found.']);
        }


        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $input['total_days'] = $start->diffInDays($end) + 1;
        $input['hours_leave'] = $request->hours_leave;
        if($request->status == 2)
        {
            Utility::emp_remaining_leave($input['employee_id'],$input['total_days']);
        }
        Leave::create($input);

        return redirect()->route('leaves.index')->with(['success'=>'Leave has been added successfully']);
    }

    public function edit(Request $request,$id)
    {
        $data['leave']  = $this->scopedLeavesQuery()->findOrFail($id);
        $data['emp_list'] = $this->scopedEmployeesQuery()->get();
        $data['leave_type_list'] = LeaveType::pluck('name','id');
        return view('leave.edit',$data);
    }

    public function update(Request $request,$id)
    {
        $input = $request->all();
        $employee = Employee::where('user_id', \Auth::user()->id)->first();

        if(\Auth::user()->type == 'HRM' || \Auth::user()->type == 'company')
        {
            $request->validate([
                'start_date'=>'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'leave_type' => 'required|not_in:0',
                'reason'=>'required',
                'employee_id'=>'required',
                'status'=>'required|not_in:0',
                'hours_leave'=>'required',
            ]);
            $input['employee_id'] = $request->employee_id;

        }
        elseif($employee)
        {
            $request->validate([
                'start_date'=>'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'leave_type' => 'required|not_in:0',
                'reason'=>'required',
            ]);
            $input['employee_id'] = $employee->id;
        }
        else
        {
            return redirect()->back()->with(['error_msg'=>'Employee not found.']);
        }

        $lv  =  $this->scopedLeavesQuery()->findOrFail($id);

        $input =$request->all();
        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $input['total_days'] = $start->diffInDays($end) + 1;
        $input['hours_leave'] = (!empty($request->hours_leave)) ? $request->hours_leave:0;

        if($request->status == 2 & $lv['status'] != 2)
        {
            Utility::emp_remaining_leave($input['employee_id'],$input['total_days']);
        }

        $lv->update($input);

        return redirect()->route('leaves.index')->with(['success'=>'Leave has been updated successfully']);
    }

    public function delete(Request $request,$id)
    {
        $lv  =  $this->scopedLeavesQuery()->findOrFail($id);
        $lv->delete();

        return response()->json([
            'success' => 'Leave has been deleted successfully.'
        ], 200);

        return redirect()->route('leaves.index');
    }

    public function update_status_reject(Request $request,$id)
    {
        $leave = $this->scopedLeavesQuery()->findOrFail($id);
        $leave->status = $request->status;

        if($request->status == 2 & $leave['status'] != 2)
        {
            Utility::emp_remaining_leave($leave['employee_id'],$leave['total_days']);
        }

        if(!empty($request->remark))
        {
            $leave->remark = $request->remark;
        }
        $leave->save();
        return redirect()->back()->with('success', 'Status has been updated successfully');
    }

    private function scopedEmployeesQuery()
    {
        if (\Auth::user()->type == 'Sales') {
            return Employee::where('user_id', \Auth::id());
        }

        return Employee::whereHas('getUser', function ($q) {
            $q->where('created_by', \Auth::user()->creatorId());
        });
    }

    private function scopedLeavesQuery()
    {
        if (\Auth::user()->type == 'Sales') {
            $emp = Employee::where('user_id', \Auth::id())->first();
            return Leave::where('employee_id', $emp?->id ?? 0);
        }

        return Leave::whereHas('employees.getUser', function ($q) {
            $q->where('created_by', \Auth::user()->creatorId());
        });
    }
}
