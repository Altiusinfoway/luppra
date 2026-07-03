<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\User;
use App\Models\Entity;
use App\Models\SalesTarget;
use App\Models\EmployeeSalesTarget;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Quotes;

class SalesEmployeeTarget extends Controller
{
    public function index(Request $request, $slug, $user_id = null)
    {
            $data['dynamic_slug'] = $slug;

            $data['user_id'] = $user_id;
            $now = Carbon::now();

            // check if any record exists for current month + year
            $data['exists'] = EmployeeSalesTarget::whereMonth('current_month_date', $now->month)
                ->whereYear('current_month_date', $now->year)
                ->exists();

            if ($request->ajax()) {
                try {
                    if ($slug == 'all_months') {
                        // fetch all users with targets in current month
                         $query = EmployeeSalesTarget::with(['getUser', 'getSalesEmployeeTarget'])
                            ->whereMonth('current_month_date', $now->month)
                            ->whereYear('current_month_date', $now->year)
                            ->selectRaw('
                                MAX(id) as id,
                                employee_id,
                                MAX(user_id) as user_id,
                                MAX(current_month_date) as current_month_date,
                                MAX(sales_target_id) as sales_target_id,
                                MAX(achieve_amount) as achieve_amount
                            ')
                            ->groupBy('employee_id');
                    } elseif ($slug == 'current_month') {
                        $now = now();
                        $query = EmployeeSalesTarget::with(['getUser', 'getSalesEmployeeTarget'])
                            ->where('user_id', $user_id)
                            ->where('is_eligible_target', 1)
                            ->whereYear('current_month_date', $now->year);
                    } else {
                        return response()->json([
                            'error' => 'Invalid slug provided'
                        ], 400);
                    }

                    // Optional filter by user name
                    if ($request->name) {
                        $query->whereHas('getUser', function ($q) use ($request) {
                            $q->where('name', 'like', '%' . $request->name . '%');
                        });
                    }

                    $dataSet = $query->get();

                    return DataTables::of($dataSet)
                        ->addColumn('user_name', function ($row) {
                            return $row->getUser->name ?? '-';
                        })
                        ->addColumn('total_target', function ($row) use ($slug){

                            if ($slug == 'all_months')
                            {
                                $all = EmployeeSalesTarget::with('getSalesEmployeeTarget')
                                    ->where('employee_id', $row->employee_id)
                                    ->where('is_eligible_target', 1)
                                    ->get();

                                $sum_all_target = $all->sum(function ($item) {
                                    return optional($item->getSalesEmployeeTarget)->max_target ?? 0;
                                });

                                return $sum_all_target;
                            }
                            else
                            {
                                return optional($row->getSalesEmployeeTarget)->max_target ?? 0;
                            }
                        })
                        ->addColumn('achieve_amt', function ($row) use ($slug, $now) {

                            if ($slug == 'all_months')
                            {
                                //all months target sum
                                $all_achive = EmployeeSalesTarget::with('getSalesEmployeeTarget')
                                    ->where('employee_id', $row->employee_id)
                                    ->where('is_eligible_target', 1)
                                    ->get();

                                $sum_all_achieve = $all_achive->sum(function ($item) {
                                    return $item->achieve_amount ?? 0;
                                });

                                return $sum_all_achieve;
                            }
                            else
                            {
                                 return $row->achieve_amount ?? 0;
                            }
                        })
                        ->addColumn('performance_per', function ($row) use ($slug, $now) {

                             if ($slug == 'all_months')
                            {
                                $all_achive = EmployeeSalesTarget::with('getSalesEmployeeTarget')
                                    ->where('employee_id', $row->employee_id)
                                    ->where('is_eligible_target', 1)
                                    ->get();

                                $sum_all_achieve = $all_achive->sum(function ($item) {
                                    return $item->achieve_amount ?? 0;
                                });

                                $sum_all_target = $all_achive->sum(function ($item) {
                                    return optional($item->getSalesEmployeeTarget)->max_target ?? 0;
                                });

                                $percentage = 0;

                                if ($sum_all_target > 0) {
                                    $percentage = ($sum_all_achieve / $sum_all_target) * 100;
                                    $percentage = min($percentage, 100);
                                }
                                return round($percentage, 2) . '%';
                            }
                            else
                            {
                                $percentage = 0;
                                $sum_all_achieve = $row->achieve_amount ?? 0;
                                $sum_all_target =optional($row->getSalesEmployeeTarget)->max_target ?? 0;
                                if ($sum_all_target > 0) {
                                    $percentage = ($sum_all_achieve / $sum_all_target) * 100;
                                    $percentage = min($percentage, 100);
                                }

                                return round($percentage, 2) . '%';
                            }
                        })

                       ->addColumn('month_name', function ($row) use ($slug){
                            return $month_nm = \Carbon\Carbon::parse($row->current_month_date)->format('F Y');

                        })

                        ->addColumn('action', function ($row) use ($slug) {
                            if ($slug == 'current_month') {
                                $editUrl = route("sales-employee-targets.get_month_lead", [$row->user_id, $row->employee_id,$row->current_month_date ?? 0]);
                            } else {
                                $editUrl = route("sales-employee-targets.index", ['current_month', $row->user_id]);
                            }

                            return '<div class="dropdown d-inline-block">
                                        <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ri-more-fill align-middle"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a href="' . $editUrl . '" class="dropdown-item edit-item-btn">
                                                    <i class="ri-eye-fill align-bottom me-2 text-muted"></i> View
                                                </a>
                                            </li>
                                        </ul>
                                    </div>';
                        })
                        ->rawColumns(['user_name', 'action', 'total_target', 'performance_per', 'achieve_amt','month_name'])
                        ->make(true);

                } catch (\Exception $e) {
                    return response()->json([
                        'error vvv' => 'Server Error: ' . $e->getMessage()
                    ], 500);
                }
            }

            return view('sales_emp_target.sal_emp_index', $data);


    }

    public function create()
    {
        $data['user_sales'] = User::where('type','Sales')
            ->where('created_by', \Auth::user()->creatorId())
            ->get();
        $data['sales_target_list'] = SalesTarget::get();
        return view('sales_emp_target.create',$data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
        'user_id' => 'required|array',
        'sales_target_id' => 'required|array',

        'user_id.*' => 'required|exists:users,id',
        'sales_target_id.*' => 'required|exists:sales_targets,id',
        'incentive' => 'nullable|array',
        'incentive.*' => 'nullable|numeric|min:0',
        ]);

        $monthDate = now()->startOfMonth()->toDateString();

        foreach ($request->user_id as $index => $userId)
        {
            $salesUser = User::where('id', $userId)
                ->where('type', 'Sales')
                ->where('created_by', \Auth::user()->creatorId())
                ->first();
            if (!$salesUser) {
                continue;
            }

            $employee = Employee::where('user_id', $salesUser->id)->first();

            if (!$employee) {
                continue;
            }

            $salesTarget = SalesTarget::find($request->sales_target_id[$index]);
            $defaultIncentive = (float) ($salesTarget->incentive_value ?? $salesTarget->incentive ?? 0);
            $manualIncentive = isset($request->incentive[$index]) && $request->incentive[$index] !== ''
                ? (float) $request->incentive[$index]
                : null;

            EmployeeSalesTarget::updateOrCreate(
                [
                    'user_id' => $userId,
                    'employee_id' => $employee->id,
                    'sales_target_id' => $request->sales_target_id[$index],
                    'current_month_date' => $monthDate,
                ],
                [
                    'incentive' => $manualIncentive ?? $defaultIncentive,
                    'is_eligible_target' => 0,
                ]
            );
        }

        return response()->json([
            'success'=>'yes',
            'redirect_route'=>route('sales-employee-targets.index','all_months'),
            'message' => 'Targets have been added successfully.',
        ], 200);
    }

    public function get_month_lead(Request $request,$user_id,$employee_id,$sales_target_assign_date)
    {
        $data['user_id'] =$user_id;
        $data['employee_id'] =$employee_id;
        $data['sales_target_assign_date'] =$sales_target_assign_date;
        $data['user_id'] = $user_id;

        if ($request->ajax())
        {
            try {

                $month = Carbon::parse($sales_target_assign_date)->month;
                $year = Carbon::parse($sales_target_assign_date)->year;

                $all_current_month = Quotes::where('user_id', $user_id)
                        ->where('status', 3)
                        ->whereMonth('created_at', $month)
                        ->whereYear('created_at', $year)
                        ->pluck('lead_id')
                        ->unique()
                        ->values();

                $query = Lead::whereIn('id', $all_current_month);

                $dataSet = $query->orderBy('id', 'desc')->get();

                return DataTables::of($dataSet)
                    ->addIndexColumn()
                    ->addColumn('lead_detail', function ($row) {
                        $lead_detail = '';

                            $lead = Lead::find($row->id);
                            if ($lead) {
                                $customer = Entity::with(['getAddress'])
                                    ->where('type', 'customer')
                                    ->where('id', $lead->customer_id)
                                    ->first();

                                if ($customer) {
                                    $lead_detail  = $customer->name ?? '';
                                    $lead_detail .= '<br>';

                                    if ($customer->getAddress) {
                                        $lead_detail .= $customer->getAddress->city ?? '';
                                        $lead_detail .= ', ' . ($customer->getAddress->state ?? '');
                                        $lead_detail .= '<br>';
                                    }

                                    $lead_detail .= $customer->contact ?? '';
                                    $lead_detail .= '<br>';
                                    $lead_detail .= $customer->email ?? '';
                                }
                            }

                            return $lead_detail;
                        return "";

                    })
                    ->rawColumns(['lead_detail'])
                    ->make(true);

            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        }

        return view('sales_emp_target.current_month_lead',$data);

    }

    public function get_sales_target_incentive(Request $request,$sales_target_id)
    {
        $get_sales_target = SalesTarget::where('id',$sales_target_id)->first();
        if($get_sales_target)
        {
            return response()->json([
                'status' => 'yes',
                'incentive' => (float) ($get_sales_target->incentive_value ?? $get_sales_target->incentive ?? 0),
                'mode' => (string) ($get_sales_target->incentive_mode ?? 'percent_over_target'),
                'label' => $get_sales_target->incentiveSummary(),
            ]);
        }
        return response()->json([
            'status' => 'status',
            'incentive' => 0,
        ]);

    }
}
