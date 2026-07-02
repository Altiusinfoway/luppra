<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;
use App\Models\Utility;
use App\Models\Products;
use App\Models\LeadSource;
use App\Models\Entity;
use App\Models\LeaveType;
use App\Models\User;
use App\Models\Holiday;
use App\Models\WorkingHours;
use App\Models\Address;
use App\Models\LeadType;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Units;
use App\Models\UnitTypes;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Category;
use App\Models\CustomerPriceHistory;
use App\Models\GstSlabMaster;

class MasterController extends Controller
{

    public function lead_type_list(Request $request)
    {
        try
        {

            $user = JWTAuth::parseToken()->authenticate();
            Log::info('------ start lead_type_list ------');
            Log::info('Request :-',$request->all());


            $data = LeadType::select('id','name')->get();

            Log::info('------ end lead_type_list ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true,"lead type list.",$data,200);

        } catch (JWTException $e) {

            return Utility::return_response(false,"Token invalid or not provided.","",500);
        }
    }

    public function country_list(Request $request)
    {
        try
        {
            $user = JWTAuth::parseToken()->authenticate();
            Log::info('------ start country_list ------');
            Log::info('Request :-',$request->all());


            $data = Country::isActive()->select('id','name')->get();

            Log::info('------ end country_list ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true,"country list list.",$data,200);

        } catch (JWTException $e) {

            return Utility::return_response(false,"Token invalid or not provided.","",500);
        }
    }

    public function state_list(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'country_id' => 'required|exists:tenant.countries,id',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false,$validator->errors()->first(),"",422);
            }

            $user = JWTAuth::parseToken()->authenticate();

            Log::info('------ start state_list ------');
            Log::info('Request :-',$request->all());


            $data = State::isActive()->where('country_id',$request->country_id)->select('id','name','country_id')->get();


            Log::info('------ end state_list ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true,"state list.",$data,200);

        } catch (JWTException $e) {

            return Utility::return_response(false,"Token invalid or not provided.","",500);
        }
    }

    public function city_list(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'state_id' => 'required|exists:tenant.states,id',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false,$validator->errors()->first(),"",422);
            }

            $user = JWTAuth::parseToken()->authenticate();

            Log::info('------ start city_list ------');
            Log::info('Request :-',$request->all());

            $data = City::isActive()->where('state_id',$request->state_id)->select('id','name','state_id')->get();

            Log::info('------ end city_list ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true,"city list.",$data,200);

        } catch (JWTException $e) {

            return Utility::return_response(false,"Token invalid or not provided.","",500);
        }
    }

    public function product_list(Request $request)
    {
        try
        {
            $user = Auth::user();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            Log::info('------ start product_list ------');
            Log::info('Request :-',$request->all());

            if($request->customer_id)
            {
                $check_cust = Entity::where('id',$request->customer_id)
                    ->where('type','customer')
                    ->where('created_by', $user->creatorId())
                    ->first();
                if(!$check_cust)
                {
                    return Utility::return_response(false, "Customer Not Found.", "", 422);
                }

            }

            $all_products = Products::with([
                'getUnit'=>function ($q)
                {
                    $q->select('id','name','type_id');
                },
                 'getUnitType'=>function ($q)
                {
                    $q->select('id','name');
                },
                'getCategory',
                'getGstSlabMaster'
            ])
                ->select('id','name','image','sku_code','price','unit','unit_type','hsn_code','category_id','gst_slab_master_id')
                ->where('created_by', $user->creatorId())
                ->where(function ($query) {
                    $query->whereNull('delete_status')
                        ->orWhere('delete_status', '!=', 1);
                })
                ->where(function ($query) {
                    $query->whereNull('is_active')
                        ->orWhere('is_active', 1);
                })
                ->orderBy('id','desc')
                ->get();

            if(count($all_products) > 0)
            {
                foreach($all_products as $product_itm)
                {
                    if($request->customer_id)
                    {
                        $cust_pre_price = CustomerPriceHistory::where('customer_id',$request->customer_id)->where('product_id',$product_itm->id)->first();
                        $product_itm['customer_previous_price'] =$cust_pre_price?->price ?? "0.00";
                        $product_itm['discount'] =$cust_pre_price?->discount ?? "0.00";
                    }
                    else
                    {
                        $product_itm['customer_previous_price'] = "0.00";
                        $product_itm['discount'] ="0.00";
                    }


                }
            }

            Log::info('------ end product_list ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true,"product list.",$all_products,200);

        } catch (JWTException $e) {
            \Log::info('product-list error ',[$e->getMessage()]);
            return Utility::return_response(false,"Token invalid or not provided.","",500);
        }
    }

    public function unit_type_list(Request $request)
    {
        try
        {
            Log::info('------ start unit_type_list ------');
            Log::info('Request :-',$request->all());

            $user = JWTAuth::parseToken()->authenticate();


            $data = UnitTypes::get();

            Log::info('------ end unit_type_list ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true,"unit type list.",$data,200);

        } catch (JWTException $e) {
			 Log::info('unit_type_list  error ',[$e->getMessage()]);
            return Utility::return_response(false,"Token invalid or not provided.","",500);
        }
    }

     public function unit_list(Request $request)
    {
        try
        {
            Log::info('------ start unit_list ------');
            Log::info('Request :-',$request->all());

            $validator = Validator::make($request->all(), [
                'unit_type_id' => 'required|exists:tenant.unit_types,id',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false,$validator->errors()->first(),"",422);
            }

            $user = JWTAuth::parseToken()->authenticate();

            $data = Units::where('type_id',$request->unit_type_id)->select('id','name')->get();


            Log::info('------ end unit_list ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true,"unit  list.",$data,200);

        } catch (JWTException $e) {
			 Log::info('unit_list  error ',[$e->getMessage()]);
            return Utility::return_response(false,"Token invalid or not provided.","",500);
        }
    }

      public function leave_type_list(Request $request)
    {
        try
        {
            Log::info('------ start leave_type_list ------');
            Log::info('Request :-',$request->all());

            $user = JWTAuth::parseToken()->authenticate();


            $data = LeaveType::get();

            Log::info('------ end leave_type_list ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true,"leave type  list.",$data,200);

        } catch (JWTException $e) {
			 Log::info('leave_type_list  error ',[$e->getMessage()]);
            return Utility::return_response(false,"Token invalid or not provided.","",500);
        }
    }

    public function day_options(Request $request)
    {
        try
        {
            Log::info('------ start day_options ------');
            Log::info('Request :-',$request->all());

            $user = JWTAuth::parseToken()->authenticate();


              $data = [
                [
                    "id" => 1,
                    "name" => "Half Day"
                ],
                [
                    "id" => 2,
                    "name" => "Full Day"
                ]
            ];


            Log::info('------ end day_options ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true,"day options  list.",$data,200);

        } catch (JWTException $e) {
			 Log::info('day_options  error ',[$e->getMessage()]);
            return Utility::return_response(false,"Token invalid or not provided.","",500);
        }
    }


     public function holiday_list(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            Log::info('------ start holiday_list ------');
            Log::info('Request :-', $request->all());

            $data = Holiday::select(
                'id',
                'name',
                'start_date',
                'end_date',
                'description'
            )->orderBy('id','desc')->get();

            Log::info('------ end holiday_list ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, "holidays list.", $data, 200);
        } catch (JWTException $e) {

            return Utility::return_response(false, "Token invalid or not provided.", "", 500);
        }
    }

     public function working_hours_list(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            Log::info('------ start working_hours_list ------');
            Log::info('Request :-', $request->all());

            $dayList = [
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday',
                6 => 'Saturday',
                7 => 'Sunday',
            ];

            $workingHours = WorkingHours::select('id', 'day', 'start_time', 'end_time')->orderBy('id','desc')->get()
                ->map(function ($item) use ($dayList) {
                    return [
                        'id'         => $item->id,
                        'day'        => $item->day,
                        'day_name'   => $dayList[$item->day] ?? '-',
                        'start_time' => $item->start_time,
                        'end_time'   => $item->end_time,
                    ];
                });

            Log::info('------ end working_hours_list ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, 'Working hours list.', $workingHours, 200);
        } catch (JWTException $e) {
            return Utility::return_response(false, "Token invalid or not provided.", "", 500);
        }
    }


      public function attendance_update(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            Log::info('------ start attendance_update ------');
            Log::info('Request :-', $request->all());

            $emp = Employee::where('user_id', $user->id)->first();
            if (!$emp) {
                return Utility::return_response(false, 'Employee not found.', '', 404);
            }

            $currentTime = Carbon::now('Asia/Kolkata')->format('H:i:s');
            $todayDate   = Carbon::now('Asia/Kolkata')->format('Y-m-d');
            $dayNumber   = date('N');

            $attendance = Attendance::where('employee_id', $emp->id)
                ->whereDate('date', $todayDate)
                ->orderBy('id', 'desc')
                ->first();

            // CHECK IN
            if (!$attendance || $attendance->check_out !== null)
            {

                $workingCheck = Utility::checkEmpLoginBtwWorkingHrs($dayNumber, $currentTime, 0);

                if ($workingCheck === 0) {
                    return Utility::return_response(false, 'Working time not available.', '', 403);
                }
                if (is_string($workingCheck)) {
                    return Utility::return_response(false, $workingCheck, '', 403);
                }

                $attendance = Attendance::create([
                    'employee_id' => $emp->id,
                    'date'        => $todayDate,
                    'check_in'    => $currentTime,
                    'check_out'   => null,
                    'is_present'  => 0,
                ]);

                return Utility::return_response(true, 'Checked in successfully.',[
                    'date'=>$attendance->date,'check_in'=>$attendance->check_in,'check_out'=>$attendance->check_out,'total_hours'=>$attendance->total_hours,
                    'is_emp_login'=>1
                ], 200);
            }

            // CHECK OUT
            $workingCheck = Utility::checkEmpLoginBtwWorkingHrs($dayNumber, $currentTime, 1);

            if ($workingCheck === 0) {
                return Utility::return_response(false, 'Working time not available.', '', 403);
            }
            if (is_string($workingCheck)) {
                return Utility::return_response(false, $workingCheck, '', 403);
            }

            $checkIn  = Carbon::createFromFormat('H:i:s', $attendance->check_in, 'Asia/Kolkata');
            $checkOut = Carbon::createFromFormat('H:i:s', $currentTime, 'Asia/Kolkata');

            $diffInSeconds = $checkIn->diffInSeconds($checkOut);

            $attendance->update([
                'check_out'   => $checkOut->format('H:i:s'),
                'total_hours' => gmdate('H:i:s', $diffInSeconds),
                'is_present'  => 1,
            ]);

            Log::info('------ end attendance_update ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, 'Checked out successfully.',[
                    'date'=>$attendance->date,'check_in'=>$attendance->check_in,'check_out'=>$attendance->check_out,'total_hours'=>$attendance->total_hours,
                    'is_emp_login'=>0
                ], 200);
        } catch (JWTException $e) {
            return Utility::return_response(false, 'Token invalid or not provided.', '', 500);
        }
    }

    public function attendance_list(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            Log::info('------ start attendance_list ------');
            Log::info('Request :-', $request->all());

            $emp = Employee::where('user_id', $user->id)->first();
            if (!$emp) {
                return Utility::return_response(false, 'Employee not found.', '', 404);
            }

            $data = Attendance::where('employee_id',$emp->id)->get();

             $grouped = $data->groupBy('date');

            $finalData = [];

            foreach ($grouped as $date => $rows) {

                // Calculate total hours for the day
                $totalSeconds = 0;
                foreach ($rows as $row) {
                    if ($row->total_hours) {
                        $totalSeconds += strtotime("1970-01-01 {$row->total_hours} UTC");
                    }
                }

                $finalData[] = [
                    'date' => $date,
                    'records' => $rows->map(function ($row) {
                        return [
                            'attendance_id' => $row->id,
                            'check_in' => $row->check_in,
                            'check_out' => $row->check_out,
                            'total_hours' => $row->total_hours,
                        ];
                    }),
                    'day_total_hours' => gmdate('H:i:s', $totalSeconds),
                ];
            }


            Log::info('------ end attendance_list ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, 'Attendance List.',$finalData, 200);
        } catch (JWTException $e) {
            return Utility::return_response(false, 'Token invalid or not provided.', '', 500);
        }
    }



     public function transport_list(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            Log::info('------ start transport_list ------');
            Log::info('Request :-', $request->all());

            $data = Entity::GetTransport()
                ->select('id', 'name')->orderBy('id','desc')
                ->get();

            Log::info('------ end transport_list ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, "transports list.", $data, 200);
        } catch (JWTException $e) {

            return Utility::return_response(false, "Token invalid or not provided.", "", 500);
        }
    }


    public function category_list(Request $request)
    {
        try
        {
            Log::info('------ start category_list ------');
            Log::info('Request :-',$request->all());

            // $validator = Validator::make($request->all(), [
            //     // 'unit_type_id' => 'required|exists:tenant.unit_types,id',
            // ]);

            // if ($validator->fails()) {
            //     return Utility::return_response(false,$validator->errors()->first(),"",422);
            // }

            $user = JWTAuth::parseToken()->authenticate();

            $data = Category::query();

            if ($request->sub_category_id) {
                $data->where('parent_id', $request->sub_category_id);
            }

            $data = $data->orderBy('id', 'desc')->get();


            Log::info('------ end category_list ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true,"Category List.",$data,200);

        } catch (JWTException $e) {
			 Log::info('category_list  error ',[$e->getMessage()]);
            return Utility::return_response(false,"Token invalid or not provided.","",500);
        }
    }


    public function gst_list(Request $request)
    {
        try
        {
            Log::info('------ start gst_list ------');
            Log::info('Request :-',$request->all());


            $user = Auth::user();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            $data = GstSlabMaster::query();


            $data = $data->get();


            Log::info('------ end gst_list ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true,"Gst List.",$data,200);

        } catch (JWTException $e) {
			 Log::info('gst_list  error ',[$e->getMessage()]);
            return Utility::return_response(false,"Token invalid or not provided.","",500);
        }
    }



}
