<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\Utility;
use App\Models\EmployeeSalaryDetail;
use App\Models\BankDetail;
use App\Models\Payments;
use App\Models\User;
use App\Models\EmployeeSalesTarget;
use App\Models\EmployeePaymentHistory;
use PDF;
use Illuminate\Support\Facades\File;

class PayRollController extends Controller
{
    public function index(Request $request)
    {

        if ($request->ajax()) {

            try {

                $requestList = $this->scopedEmployeesQuery()->with('SalesTarget')
                    ->whereHas('getUser', function ($query) {
                        $query->where(function ($q) {
                            $q->where('type', 'Sales')
                                ->where('delete_status', '!=', 1);
                        });
                    })->select('id', 'user_id', 'name', 'salary', 'incentive', 'sales_target_id');

                $month_val =  date('Y-m'); //'2025-11'
                $cur_date = Carbon::now();

                $data = $requestList->orderBy('id', 'desc')->get();


                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('sales_target', function ($row) use ($cur_date) {
                        $is_target_assign = EmployeeSalesTarget::with('getSalesEmployeeTarget')
                            ->where('employee_id', $row->id)->where('is_eligible_target', 1)
                            ->whereMonth('current_month_date', $cur_date->month)
                            ->whereYear('current_month_date', $cur_date->year)
                            ->first();
                        return optional(optional($is_target_assign)->getSalesEmployeeTarget)->max_target ?? 0;
                    })
                    ->addColumn('get_incentive', function ($row) use ($cur_date) {

                        $is_target_assign = EmployeeSalesTarget::with('getSalesEmployeeTarget')
                            ->where('employee_id', $row->id)->where('is_eligible_target', 1)
                            ->whereMonth('current_month_date', $cur_date->month)
                            ->whereYear('current_month_date', $cur_date->year)
                            ->first();

                        $target = optional($is_target_assign)->getSalesEmployeeTarget;
                        if (!$target) {
                            return 0;
                        }
                        return $target->incentiveSummary();
                    })
                    ->addColumn('target_achieve', function ($row) use ($month_val) {
                        $target_val = Utility::getSalesEmpTarget($row->user_id, $month_val);
                        return number_format($target_val, 2);
                    })
                    ->addColumn('sales_bonus', function ($row) use ($month_val, $cur_date) {
                        $achieve_target = Utility::getSalesEmpTarget($row->user_id, $month_val);


                        $is_target_assign = EmployeeSalesTarget::with('getSalesEmployeeTarget')
                            ->where('employee_id', $row->id)
                            ->whereMonth('current_month_date', $cur_date->month)
                            ->whereYear('current_month_date', $cur_date->year)
                            ->first();

                        if ($is_target_assign) {
                            $bonus = Utility::getSalesEmpFinalSalary($row->id, $row->salary, 1, $achieve_target);
                        } else {
                            $bonus = Utility::getSalesEmpFinalSalary($row->id, $row->salary, 0, $achieve_target);
                        }
                        return number_format($bonus, 2);
                    })

                    ->addColumn('received_sal', function ($row) use ($month_val) {


                        $sal_detail = EmployeeSalaryDetail::where('employee_id', $row->id)
                            ->where('payment_status', 'unpaid')
                            ->sum('final_salary');

                        if ($sal_detail) {
                            return $sal_detail;
                        }
                        return 0;
                    })

                    ->addColumn('action', function ($row) use ($month_val) {
                        $employeeId = (int) ($row->id ?? 0);
                        $salary = (float) ($row->salary ?? 0);
                        $achieve_target = Utility::getSalesEmpTarget($row->user_id, $month_val);

                        //check emp target is set or not if set pass 1 otherwise 0
                        $cur_date = Carbon::now();

                        $is_target_assign = EmployeeSalesTarget::with('getSalesEmployeeTarget')
                            ->where('employee_id', $row->id)
                            ->whereMonth('current_month_date', $cur_date->month)
                            ->whereYear('current_month_date', $cur_date->year)
                            ->first();

                        if ($is_target_assign) {
                            $sales_bonus = Utility::getSalesEmpFinalSalary($row->id, $row->salary, 1, $achieve_target);
                        } else {
                            $sales_bonus = Utility::getSalesEmpFinalSalary($row->id, $row->salary, 0, $achieve_target);
                        }

                        $sales_bonus = (float) ($sales_bonus ?? 0);

                        $html = '';
                        $html .= '<div class="dropdown d-inline-block">
                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-more-fill align-middle"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">';


                        $html .= '<li>
                                    <a href="' . route('payrolls.cal_emp_salary', [$employeeId, $salary, 1, $sales_bonus]) . '" class="dropdown-item edit-item-btn">
                                        <i class="ri-arrow-right-circle-fill align-bottom me-2 text-muted"></i> Generate Salary
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item edit-item-btn" data-size="lg"
                                            data-url="' . route('payrolls.logs', $row->id) . '"
                                            data-ajax-popup="true"
                                            data-bs-original-title="' . $row->name . ' Payroll Logs">
                                        <i class="ri-arrow-right-circle-fill align-bottom me-2 text-muted"></i> Payroll Logs
                                    </a>
                                </li>';
                        $html .= '</ul></div>';
                        return $html;
                    })

                    ->rawColumns(['sales_target', 'sales_bonus', 'received_sal', 'action', 'get_incentive'])
                    ->setRowClass('main-row')
                    ->make(true);
            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        }

        return view('payroll.index');
    }

    public function other_emp_payroll(Request $request)
    {
         if ($request->ajax()) {

            try {
                $requestList = $this->scopedEmployeesQuery()->with('SalesTarget')->whereHas('getUser', function ($query) {
                    $query->where('type', '!=', 'Sales');
                })->select('id', 'user_id', 'name', 'salary', 'incentive', 'sales_target_id');

                $data = $requestList->orderBy('id', 'desc')->get();

                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('received_sal', function ($row) {

                        /* $currentDate = date('Y-m-d');
                        $currentMonth = date('m', strtotime($currentDate));
                        $currentYear = date('Y', strtotime($currentDate));

                        $sal_detail = EmployeeSalaryDetail::where('employee_id', $row->id)
                            ->whereMonth('generate_salary_cur_date', $currentMonth)
                            ->whereYear('generate_salary_cur_date', $currentYear)->orderBy('id', 'desc')->first();

                       if(isset($sal_detail))
                       {
                        return $sal_detail['final_salary'];
                       } */

                        $sal_detail = EmployeeSalaryDetail::where('employee_id', $row->id)
                            ->where('payment_status', 'unpaid')
                            ->sum('final_salary');

                        if ($sal_detail) {
                            return $sal_detail;
                        }
                        return 0;
                    })

                    ->addColumn('action', function ($row) {
                        $employeeId = (int) ($row->id ?? 0);
                        $salary = (float) ($row->salary ?? 0);
                        $html = '';
                        $html .= '<div class="dropdown d-inline-block">
                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-more-fill align-middle"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">';

                        $html .= '<li>
                                    <a href="' . route('payrolls.cal_emp_salary', [$employeeId, $salary, 0, 0]) . '" class="dropdown-item edit-item-btn">
                                        <i class="ri-arrow-right-circle-fill align-bottom me-2 text-muted"></i> Generate Salary
                                    </a>
                                     <a href="javascript:void(0);" class="dropdown-item edit-item-btn" data-size="lg"
                                            data-url="' . route('payrolls.logs', $row->id) . '"
                                            data-ajax-popup="true"
                                            data-bs-original-title="' . $row->name . ' Payroll Logs">
                                        <i class="ri-arrow-right-circle-fill align-bottom me-2 text-muted"></i> Payroll Logs
                                    </a>
                                </li>';
                        $html .= '</ul></div>';
                        return $html;
                    })

                    ->rawColumns(['received_sal', 'action'])
                    ->setRowClass('main-row')
                    ->make(true);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        }

        return view('payroll.index');
    }

    public  function cal_emp_salary($emp_id, $salary, $is_sale_emp, $sales_bonus)
    {
        //if employee sales then check
        //check current month target set or not

        \Log::info('-------- controller cal_emp_salary -----------');
        $is_error = "no";
        $now = Carbon::now();
        $employee_rcd = $this->scopedEmployeesQuery()->where('id', $emp_id)->first();
        if ($employee_rcd) {

            $user_rcd = User::where('id', $employee_rcd->user_id)->first();
            if ($user_rcd && $user_rcd->type == 'Sales') {
                $empExists = EmployeeSalesTarget::where('employee_id', $emp_id)->exists();
                if (!$empExists) {
                    $is_error = "error";
                }
                $targetMonthlyExist = EmployeeSalesTarget::where('employee_id', $emp_id)
                    ->whereMonth('current_month_date', $now->month)
                    ->whereYear('current_month_date', $now->year)
                    ->exists();
                if (!$targetMonthlyExist) {

                    $is_error = "error";
                }
            }
        }

        if ($is_error == 'error') {
            \Log::info('--- if is_error ');
            // return redirect()->route('payrolls.index')->with(['error'=>'Please add Sales Employee Target']);
            // sales emp target not set then basic salary add
            if ($is_sale_emp == 1) {
                $mes = Utility::generate_emp_salary($emp_id, $salary, $is_sale_emp, $sales_bonus);
            }
        } else {
            $mes = Utility::generate_emp_salary($emp_id, $salary, $is_sale_emp, $sales_bonus);
        }


        if ($mes == 'success') {
            return redirect()->route('payrolls.index')->with(['success' => 'Employee Salary Generate has been added successfully']);
        }
    }

    public function cal_all_emp_sal()
    {
        $get_all_sales_emp = $this->scopedEmployeesQuery()->with('SalesTarget')
            ->whereHas('getUser', function ($query) {
                $query->where(function ($q) {
                    $q->where('type', 'Sales')
                        ->where('delete_status', '!=', 1);
                });
            })->select('id', 'user_id', 'name', 'salary', 'incentive', 'sales_target_id')->get();

        $month_val =  date('Y-m');

        if (count($get_all_sales_emp) > 0) {
            foreach ($get_all_sales_emp as $sal_emp) {
                $achieve_target = Utility::getSalesEmpTarget($sal_emp->user_id, $month_val);

                //check emp target is set or not if target is set then pass 1 otherwise pass 0
                $cur_date = Carbon::now();

                $is_target_assign = EmployeeSalesTarget::with('getSalesEmployeeTarget')
                    ->where('employee_id', $sal_emp->id)
                    ->whereMonth('current_month_date', $cur_date->month)
                    ->whereYear('current_month_date', $cur_date->year)
                    ->first();

                if ($is_target_assign) {
                    $sales_bonus = Utility::getSalesEmpFinalSalary($sal_emp->id, $sal_emp->salary, 1, $achieve_target);
                } else {
                    $sales_bonus = Utility::getSalesEmpFinalSalary($sal_emp->id, $sal_emp->salary, 0, $achieve_target);
                }

                $mes = Utility::generate_emp_salary($sal_emp['id'], $sal_emp['salary'], 1, $sales_bonus);
            }
        }


        //not sales emp
        $get_all_emp = $this->scopedEmployeesQuery()->with('SalesTarget')
            ->whereHas('getUser', function ($query) {
                $query->where(function ($q) {
                    $q->where('type', '!=', 'Sales')
                        ->where('delete_status', '!=', 1);
                });
            })->select('id', 'user_id', 'name', 'salary', 'incentive', 'sales_target_id')->get();

        if (count($get_all_emp) > 0) {
            foreach ($get_all_emp as $emp_data) {
                $mes = Utility::generate_emp_salary($emp_data['id'], $emp_data['salary'], 0, 0);
            }
        }

        return redirect()->route('payrolls.index')->with(['success' => 'Employee Salary Generate has been added successfully']);
    }

    public function payrollLogs($emp_id)
    {
        $employee = $this->scopedEmployeesQuery()->where('id', $emp_id)->firstOrFail();
        $cur_date = Carbon::now();
        $data['emp_id'] =$employee->id ;
        $data['all_paid_sal_sum'] = $this->scopedEmployeeSalaryDetailsQuery()->where('employee_id', $employee->id)
            ->where('payment_status', 'paid')->sum('final_salary');
        $data['cur_month_sal'] = $this->scopedEmployeeSalaryDetailsQuery()->where('employee_id', $employee->id)->whereMonth('salary_month', $cur_date->month)
            ->whereYear('salary_month', $cur_date->year)->where('payment_status', 'paid')
           ->value('final_salary');
        $data['pending_all_sal'] = $this->scopedEmployeeSalaryDetailsQuery()->where('employee_id', $employee->id)
            ->where('payment_status', 'unpaid')->sum('final_salary');

        $data['payment_history'] = $this->scopedEmployeePaymentsQuery()->where('payee_id',$employee->id)->get();

        return view('payroll.logs', $data);
    }



    public function unpaidPayments(Request $request)
    {


        if ($request->ajax()) {

            try {

                $unpaid_payments = $this->scopedEmployeeSalaryDetailsQuery()->where(['payment_status' => 'unpaid']);

                $data = $unpaid_payments->get();

                $grandTotal = $this->scopedEmployeeSalaryDetailsQuery()->where(['payment_status' => 'unpaid'])->sum('final_salary');

                return DataTables::of($data)

                    ->addColumn('checkbox', function ($row) {

                        return '<div class="form-check">
                                            <input class="form-check-input row-checkbox" type="checkbox" value="' . $row->id . '" id="cardtableCheck' . $row->id . '">
                                            <label class="form-check-label" for="cardtableCheck' . $row->id . '"></label>
                                        </div>';
                    })
                    ->addColumn('name', function ($row) {

                        return $row->getEmployee->name;
                    })
                    ->addColumn('payment_date', function ($row) {

                        return Utility::getDateFormated($row->salary_month, 'M Y');
                    })
                    ->addColumn('amount_raw', function ($row) {
                        return $row->final_salary;
                    })
                    ->addColumn('payment_status', function ($row) {

                        return Utility::paymentStatus($row->payment_status);
                    })
                    ->addColumn('action', function ($row) {

                        return '<button class="btn btn-sm btn-outline-primary action-btn"><i class="fas fa-eye"></i> View</button>';
                    })
                    ->rawColumns(['checkbox', 'payment_date', 'amount_raw', 'user_nm', 'payment_status', 'action'])
                    ->with([
                        'grand_total' => $grandTotal
                    ])
                    ->setRowClass('main-row')
                    ->make(true);
            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        }

        return view('payroll.unpaid_payments');
    }

    public function pay(Request $request, $selected)
    {

        $bank_detail_list = BankDetail::all();
        $account_transaction_type = Payments::getAccountTransactionTypes();
        $paymentMethods = Payments::getPaymentMethods();

        return view('payroll.create_payment', compact('selected', 'bank_detail_list', 'account_transaction_type', 'paymentMethods'));
    }

       public function payroll_emp(Request $request)
        {

            $request->validate([
                'account_transaction_type' => 'required|in:' . implode(',', array_keys(Payments::getAccountTransactionTypes())),
                'selected' => 'required',
                'bank_detail_id' => 'required',
                'payment_method' => 'required|in:' . implode(',', array_keys(Payments::getPaymentMethods())),
                'transaction_id' => 'required_unless:payment_method,cash',
                'payment_date' => 'required',
            ]);

            try {

                if ($request->has('selected') && $request->selected != "") {

                    if ($request->selected == 'all') {

                        // Get unpaid salaries to all.
                        $unpaid_payments = $this->scopedEmployeeSalaryDetailsQuery()->where(['payment_status' => 'unpaid'])->get();
                    } else {

                        // Get unpaid salaries to selected.
                        $unpaid_payments = $this->scopedEmployeeSalaryDetailsQuery()
                            ->where(['payment_status' => 'unpaid'])
                            ->whereIn('id', explode(',', $request->selected))
                            ->get();
                    }

                    DB::beginTransaction();
                    if (!empty($unpaid_payments)) {


                        foreach ($unpaid_payments as $pay) {

                            $payment                    = new Payments();
                            $payment->created_by        = \Auth::user()->id;
                            $payment->payment_method    = $request->payment_method ?? '';
                            $payment->payment_status    = 'paid';
                            $payment->description       = 'EMP-' . $pay->getEmployee->employee_id . ' ' . $request->description ?? '';
                            $payment->payment_date      = Utility::getDBDateFormated($request->payment_date);
                            $payment->payment_type      = $request->account_transaction_type ?? '';
                            $payment->amount            = $pay->final_salary ?? '';
                            $payment->bank_detail_id    = $request->bank_detail_id ?? null;
                            // $payment->entity_id         = $pay->employee_id ?? null;
                            $payment->payee_type        = "employee";
                            $payment->payee_id          = $pay->employee_id ?? null;
                            $payment->cheque_no         = $payment->payment_method == 'cheque' ? $request->transaction_id : null;
                            $payment->transaction_id    = $payment->payment_method != 'cheque' ? $request->transaction_id : null;

                            if ($request->hasFile('attachment')) {

                                $filenameWithExt = $request->file('attachment')->getClientOriginalName();

                                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                                $extension       = $request->file('attachment')->getClientOriginalExtension();
                                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                                $dir = 'uploads/attachment/';
                                $attachment_path = $dir . $fileNameToStore;
                                if (\File::exists($attachment_path)) {
                                    \File::delete($attachment_path);
                                }

                                $url    = '';
                                $path   = Utility::upload_file($request,'attachment',$fileNameToStore, $dir, []);

                                if ($path['flag'] == 1) {

                                    $url = $path['url'];
                                } else {

                                    return response()->json(['message' => __($path['msg'])]);
                                }

                                $attachment  = !empty($request->attachment) ? $fileNameToStore : null;

                                $payment->attachment = $attachment;
                            }
                            $payment->save();

                            $pay->payment_status = 'paid';
                            $pay->payment_id = $payment->id;
                            $pay->save();

                            //employee payment history
                            if($payment->payee_type == 'employee')
                            {
                                $emp_data['payment_id']=$payment->id;
                                $emp_data['employee_salary_detail_id']=$pay->id;
                                EmployeePaymentHistory::create($emp_data);
                            }


                            DB::commit();
                        }
                    }
                } else {
                    // Null :: not select any.
                }
            } catch (\Exception $e) {

                DB::rollBack();
                throw $e;
            }

            return response()->json(['message' => 'Payroll payment status updated successfully!']);
        }

    public function download_payroll_attachment(Request $request,$payment_id)
    {
        $payment = $this->scopedEmployeePaymentsQuery()->findOrFail($payment_id);
        if (!$payment || empty($payment->attachment)) {
            abort(404, "Attachment not found");
        }
        $fileName = $payment->attachment;
        $filePath = $fileName;
        if (!file_exists($filePath)) {
            abort(404, "File not found on server");
        }
        return response()->download($filePath);
    }
     public function view_payment_history(Request $request,$payment_id)
    {
       $payment = $this->scopedEmployeePaymentsQuery()->find($payment_id);

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        $employee_rcd = null;
        $payment_history_rcd = null;

        if ($payment->payee_type == 'employee') {
            $employee_rcd = Employee::with('departments')->find($payment->payee_id);

            $payment_history_rcd = EmployeePaymentHistory::with('getEmployeeSalaryDetail')
                ->where('payment_id', $payment_id)
                ->first();
        }

        return response()->json([
            'id'                => $payment->id,
            'transaction_id'    => $payment->transaction_id,
            'payment_date'      => $payment->payment_date,
            'amount'            => $payment->amount,
            'payment_method'    => $payment->payment_method,
            'payment_status'    => $payment->payment_status,
            'description'       => $payment->description,
            'attachment'        => $payment->attachment,
            // Employee info
            'employee_name'     => optional($employee_rcd)->name ?? '',
            'employee_id'       => optional($employee_rcd)->id ?? '',
            'employee_dept_name'=> optional(optional($employee_rcd)->departments)->name ?? '',
            'employee_bank_numb'=> optional($employee_rcd)->account_number ?? '',
            // Salary details
            'employee_salary'   => optional(optional($payment_history_rcd)->getEmployeeSalaryDetail)->final_salary
                                - optional(optional($payment_history_rcd)->getEmployeeSalaryDetail)->sales_bonus ?? 0,
            'employee_bonus'    => optional(optional($payment_history_rcd)->getEmployeeSalaryDetail)->sales_bonus ?? 0,
            'final_salary'      => $payment->amount ?? 0,
        ]);
    }

    public function download_payment_history(Request $request,$payment_id)
    {
        $data['current_date'] = date('Y-m-d');
        $data['payment_rcd'] = EmployeePaymentHistory::with(['getEmployeeSalaryDetail','getPayment'])
            ->whereHas('getPayment', function ($q) {
                $q->whereIn('id', $this->scopedEmployeePaymentsQuery()->pluck('id'));
            })
            ->where('payment_id', $payment_id)
            ->firstOrFail();
        if ($data['payment_rcd']->getPayment->payee_type == 'employee') {
            $data['employee_rcd'] = Employee::with(['departments','getDesignation'])
                ->find($data['payment_rcd']->getPayment->payee_id);
        }
        $pdf = PDF::loadView('payroll.pdf_payment_history', $data);
        $pdf->setPaper('A4', 'portrait');
        $file_name = $data['payment_rcd']->getPayment->id . '-' . uniqid() . '.pdf';
        $folder_path = storage_path('uploads/payment_history');
        $full_path = $folder_path . '/' . $file_name;
        if (!File::exists($folder_path)) {
            File::makeDirectory($folder_path, 0775, true);
        }
        $pdf->save($full_path);
        return response()->download($full_path)->deleteFileAfterSend(false);
    }

    public function filter_payment_history(Request $request, $emp_id)
    {
        $this->scopedEmployeesQuery()->where('id', $emp_id)->firstOrFail();

        $query = $this->scopedEmployeePaymentsQuery()->where('payee_id', $emp_id);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('transaction_id', 'like', '%' . $request->search . '%')
                ->orWhere('payment_method', 'like', '%' . $request->search . '%')
                ->orWhere('amount', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->dateRange) {

            if ($request->dateRange == "This Month") {
                $query->whereMonth('payment_date', now()->month)
                    ->whereYear('payment_date', now()->year);

            } elseif ($request->dateRange == "Last Month") {
                $query->whereMonth('payment_date', now()->subMonth()->month)
                    ->whereYear('payment_date', now()->subMonth()->year);

            } elseif ($request->dateRange == "Last 3 Months") {
                $query->whereBetween('payment_date', [
                    now()->subMonths(3)->startOfMonth(),
                    now()->endOfMonth()
                ]);
            }
        }

        if ($request->status && $request->status !== "All Statuses") {
           if ($request->status == "Paid") {
                $status = "paid";
            } elseif ($request->status == "UnPaid") {
                $status = "unpaid";
            } else {
                $status = null;
            }

            if ($status) {
                $query->where('payment_status', $status);
            }
        }

        return response()->json($query->orderBy('payment_date', 'DESC')->get());
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

    private function scopedEmployeeSalaryDetailsQuery()
    {
        $employeeIds = $this->scopedEmployeesQuery()->pluck('id');
        return EmployeeSalaryDetail::whereIn('employee_id', $employeeIds);
    }

    private function scopedEmployeePaymentsQuery()
    {
        $employeeIds = $this->scopedEmployeesQuery()->pluck('id');
        return Payments::where('payee_type', 'employee')->whereIn('payee_id', $employeeIds);
    }

}
