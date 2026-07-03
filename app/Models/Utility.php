<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Schema;
use PDF;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\LeadActivity;
use App\Models\WorkingHours;
use App\Models\Lead;
use App\Models\LeadProducts;
use App\Models\SalesTarget;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Employee;
use App\Models\EmployeeSalaryDetail;
use App\Models\EmployeeSalesTarget;
use App\Models\Quotes;
use App\Models\BankDetail;
use App\Services\TermsAndConditionService;


class Utility extends Model
{
    // private static $settings = NULL;
    private static $getsettings = null;
    private static $getsettingsid = null;
    private static $taxsData = null;
    private static $taxRateData = null;
    private static $taxData = null;
    private static $taxes = null;



    private static $languageSetting = null;

    private static function settingsConnectionName(): string
    {
        try {
            $default = (string) config('database.default', 'mysql');
            DB::connection($default)->getPdo();
            return $default;
        } catch (\Throwable $e) {
        }

        return 'mysql';
    }

    private static function settingsTable()
    {
        return DB::connection(self::settingsConnectionName())->table('settings');
    }

    //used now
    public static function getSetting($entity)
    {

        $setting = self::settingsTable()->where('name', $entity)->first();
        if ($setting) {
            return $setting->value;
        }

        return '';
    }

    public static function isDiscountAllowed($creatorId = null): int
    {
        $envValue = env('is_allowed_discount', env('IS_ALLOWED_DISCOUNT', 0));
        $defaultValue = in_array(strtolower((string) $envValue), ['1', 'on', 'yes', 'true'], true) ? 1 : 0;

        try {
            $connection = self::settingsConnectionName();
            $query = DB::connection($connection)->table('settings')->where('name', 'is_allowed_discount');

            if (Schema::connection($connection)->hasColumn('settings', 'created_by')) {
                $creatorId = $creatorId ?: (Auth::check() ? Auth::user()->creatorId() : null);

                if (empty($creatorId)) {
                    return $defaultValue;
                }

                $query->where('created_by', (int) $creatorId);
            }

            $value = $query->value('value');
        } catch (\Throwable $e) {
            return $defaultValue;
        }

        if ($value === null || $value === '') {
            return $defaultValue;
        }

        return in_array(strtolower((string) $value), ['1', 'on', 'yes', 'true'], true) ? 1 : 0;
    }

    public static function setting($slug)
    {
        return Session::has('settings.' . $slug) ? Session::get('settings.' . $slug) : '';
    }


    public static function employeeNumber($user_id)
    {
        $latest = Employee::where('created_by', $user_id)->latest()->first();

        if (!$latest) {
            return 1;
        }

        return $latest->employee_id + 1;
    }

    public static function employeeDetails($user_id, $created_by)
    {
        $user = User::where('id', $user_id)->first();
        $total_leave = \App\Models\LeaveRule::first();
        $employee = Employee::create(
            [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'password' => $user->password,
                'employee_id' => Utility::employeeNumber($created_by),
                'created_by' => $created_by,
                'no_of_leave' => $total_leave ? $total_leave->total_leave : 20
            ]
        );
    }

    public static function employeeDetailsUpdate($user_id, $created_by)
    {
        $user = User::where('id', $user_id)->first();

        $employee = Employee::where('user_id', $user->id)->update(
            [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ]
        );
    }

    public static function upload_file($request, $key_name, $name, $path, $custom_validation = [])
    {

        try {

            $settings = Utility::getStorageSetting();


            if (!empty($settings['storage_setting'])) {

                if ($settings['storage_setting'] == 'wasabi') {
                } else if ($settings['storage_setting'] == 's3') {
                } else {
                }

                $file = $request->$key_name;

                if (count($custom_validation) > 0) {

                    $validation = $custom_validation;
                } else {

                    $validation = [
                        'mimes: jpg,jpeg,png,xlsx,xls,csv,pdf,doc,docx,mp3,wav,m4a',
                        'max: 2048000',
                    ];
                }

                $validator = \Validator::make($request->all(), [
                    $key_name => $validation,
                ]);

                if ($validator->fails()) {

                    $res = [
                        'flag' => 0,
                        'msg' => $validator->messages()->first(),
                    ];

                    return $res;
                } else {

                    $name = $name;

                    $request->$key_name->move(storage_path($path), $name);
                    $path = $path . $name;

                    $res = [
                        'flag' => 1,
                        'msg' => 'success',
                        'url' => $path,
                    ];
                    return $res;
                }
            } else {
                $res = [
                    'flag' => 0,
                    'msg' => __('Please set proper configuration for storage.'),
                ];
                return $res;
            }
        } catch (\Exception $e) {

            $res = [
                'flag' => 0,
                'msg' => $e->getMessage(),
            ];
            return $res;
        }
    }

    public static function get_file($path)
    {
        $settings = Utility::getStorageSetting();

        try {
            /* if ($settings['storage_setting'] == 'wasabi') {

            } elseif ($settings['storage_setting'] == 's3') {

            } */

            return \Storage::disk($settings['storage_setting'])->url($path);
        } catch (\Throwable $th) {
            return '';
        }
    }

    public static function getStorageSetting()
    {
        $data = self::settingsTable();
        if (Schema::connection(self::settingsConnectionName())->hasColumn('settings', 'created_by')) {
            $data = $data->where('created_by', '=', Auth::check() ? Auth::user()->creatorId() : 1);
        }
        $data = $data->get();
        $settings = [
            "storage_setting" => "local",
            "local_storage_validation" => "jpg,jpeg,png,xlsx,xls,csv,pdf",
            "local_storage_max_upload_size" => "2048000",
            "s3_key" => "",
            "s3_secret" => "",
            "s3_region" => "",
            "s3_bucket" => "",
            "s3_url" => "",
            "s3_endpoint" => "",
            "s3_max_upload_size" => "",
            "s3_storage_validation" => "",
            "wasabi_key" => "",
            "wasabi_secret" => "",
            "wasabi_region" => "",
            "wasabi_bucket" => "",
            "wasabi_url" => "",
            "wasabi_root" => "",
            "wasabi_max_upload_size" => "",
            "wasabi_storage_validation" => "",

        ];
        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }
        return $settings;
    }

    public static function getOrderPrefix()
    {
        $data = self::settingsTable();
        $data = $data->where('name', 'order_code_prefix')->first();
        if ($data) {
            return $data->value;
        }
        return '';
    }

    public static function getQuotePrefix()
    {
        $data = self::settingsTable();
        $data = $data->where('name', 'quote_code_prefix')->first();
        if ($data) {
            return $data->value;
        }
        return '';
    }

    public static function getPOPrefix()
    {
        $data = self::settingsTable();
        $data = $data->where('name', 'po_code_prefix')->first();
        if ($data) {
            return $data->value;
        }
        return '';
    }

    public static function getDateFormated($date)
    {
        if (!empty($date) && $date != '0000-00-00') {
            return date("d M Y", strtotime($date));
        } else {
            return '';
        }
    }

    public static function getDBDateFormated($date)
    {
        if (!empty($date) && $date != '0000-00-00') {
            return date("Y-m-d H:i", strtotime($date));
        } else {
            return '';
        }
    }

    public static function getOrderStatus($slug)
    {

        if ($slug == 'Cancle') {
            return 5;
        }
        if ($slug == 'Dispatch') {
            return 4;
        }

        if ($slug == 'Ready To Dispatch') {
            return 3;
        }

        if ($slug == 'In Progress') {
            return 2;
        }

        if ($slug == 'Order Placed') {
            return 1;
        }
    }

    public static function weightConvert($weight, $fromWeight, $toWeight)
    {

        // Convert the input weight to kilograms first
        switch (strtolower($fromWeight)) {
            case 'g':
                $weightInKg = $weight / 1000;
                break;
            case 'kg':
                $weightInKg = $weight;
                break;
            case 'quintal':
                $weightInKg = $weight * 100;
                break;
            case 'ton':
                $weightInKg = $weight * 1000;
                break;
            default:
                throw new InvalidArgumentException("Unsupported from unit: $fromWeight");
        }

        // Convert from kilograms to the target unit
        switch (strtolower($toWeight)) {
            case 'g':
                return $weightInKg * 1000;
            case 'kg':
                return $weightInKg;
            case 'quintal':
                return $weightInKg / 100;
            case 'ton':
                return $weightInKg / 1000;
            default:
                throw new InvalidArgumentException("Unsupported to unit: $toWeight");
        }
    }


    public static function UserDetailsUpdate($user_id, $input)
    {
        $user = User::where('id', $user_id)->first();
        $updateData = [];

        if (!empty($input['name'])) {
            $updateData['name'] = $input['name'];
        }

        if (!empty($input['email'])) {
            $updateData['email'] = $input['email'];
        }

        if (!empty($input['phone'])) {
            $updateData['phone'] = $input['phone'];
        }

        if (!empty($input['password'])) {
            $updateData['password'] = $input['password'];
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }
    }
    /* original
    public static function find_row_with_value($needle, $haystack)
    {
        foreach ($haystack as $item) {
            if (is_array($item) && isset($item['date']) && $item['date'] === $needle) {
                return $item;
            }
        }
        return null;
    }
    */

    public static function find_row_with_value($needle, $haystack)
    {
        foreach ($haystack as $item) {
            if (is_array($item) && isset($item['date']) && $item['date'] === $needle) {
                return $item;
            }
        }
        return null;
    }


    public static function quote_pdf_generate_store($id, $folder)
    {
        $quote = Quotes::where('id',$id)->first();
        $printOptions = ['original' => 1];

        $invoices = '';

        $printCount = count($printOptions);
        $print = 0;

	        $company_name='';
	        if($quote->customer_id)
	        {
	            $company_detail = Entity::where('id',$quote->customer_id)->first();
	            $company_name=$company_detail->company_name;
	        }
	        $quotationTerms = app(TermsAndConditionService::class)
	            ->getQuotationTerms(config('database.default', 'mysql'));

	        foreach (array_keys($printOptions) as $val) {
            $print++;

            $data = [
                'quote_id' => $quote,
                    'quote_products' => $quote->quoteProducts,
                    'bank_detail' => BankDetail::first(),
                    'qrCode' => '',
	                    'print_option' => $val,
	                    'for_pdf' => true,
	                    'check_discount_allow'=>self::isDiscountAllowed(),
	                    'quotation_terms' => $quotationTerms,
	            ];

            $invoices .= view('quotes.quotation', $data)->render();

            if ($printCount !== $print) {
                $invoices .= '<div class="page-break"></div>';
            }
        }

        $pdf = PDF::loadHTML($invoices);

        $company_name = str_replace(' ', '_', trim($company_name));
        $file_name = $company_name.'_'.$quote->code . '_' . time() . '.pdf';

        $folder_path = storage_path('uploads/' . $folder);
        $full_path = $folder_path . '/' . $file_name;

        if (!File::exists($folder_path)) {
            File::makeDirectory($folder_path, 0775, true);
        }

        if (!empty($quote->quote_invoice)) {
            $old_file = $folder_path . '/' . $quote->quote_invoice;
            if (File::exists($old_file)) {
                File::delete($old_file);
            }
        }

        $pdf->save($full_path);

         $quote->update([
            'quote_invoice' => $file_name
        ]);

        return $full_path;

        /* old code
        $data['quote_id'] = Quotes::find($id);
        $data['lead_id'] = Lead::where('id', $data['quote_id']['lead_id'])->first();
        $data['quote_products'] = QuoteProducts::where('quote_id', $id)->get();
        $data['check_discount_allow'] = self::isDiscountAllowed();
        $data['bank_detail'] = BankDetail::first();
        $data['qrCode'] = '';

        // return view('quotes.quotation', $data);
        //  return $pdf->stream('invoice-INV-2025-001.pdf');
        $pdf = PDF::loadView('quotes.quotation', $data);
        $pdf->setPaper('A4', 'portrait');

        $file_name = 'quotation_' . time() . '.pdf';
        $file_name = $data['quote_id']->code . '-' . time() . '.pdf';

        $folder_path = storage_path('uploads/' . $folder);
        $full_path = $folder_path . '/' . $file_name;


        if (!File::exists($folder_path)) {
            File::makeDirectory($folder_path, 0775, true);
        }

        $pdf->save($full_path);

        if ($data['quote_id']['quote_invoice'] == null) {
            $data['quote_id']->update(['quote_invoice' => $file_name]);
        } else {
            $image_path = $folder_path . '/' . $data['quote_id']['quote_invoice'];
            if (File::exists($image_path)) {
                File::delete($image_path);
            }
            $data['quote_id']->update(['quote_invoice' => $file_name]);
        }
        return $full_path;
        */

    }

    public static function add_lead_activity($lead_id, $user_id, $desc, $date_time, $action)
    {
        $inp['lead_id'] = $lead_id;
        $inp['user_id'] = $user_id;
        $inp['desc'] = $desc;
        $inp['date_time'] = $date_time;
        $inp['action'] = $action;

        LeadActivity::create($inp);
    }

    //all key null replace with ""
    public static function replaceNullWithEmpty($data)
    {
        if ($data instanceof \Illuminate\Support\Collection) {
            $data = $data->toArray();
        }

        if ($data instanceof \Illuminate\Database\Eloquent\Model) {
            $data = $data->toArray();
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_null($value)) {
                    $data[$key] = "";
                } elseif (is_array($value) || is_object($value)) {
                    $data[$key] = self::replaceNullWithEmpty($value);
                }
            }
            return $data;
        }

        if (is_object($data)) {
            foreach ($data as $key => $value) {
                if (is_null($value)) {
                    $data->$key = "";
                } elseif (is_array($value) || is_object($value)) {
                    $data->$key = self::replaceNullWithEmpty($value);
                }
            }
            return $data;
        }

        return $data;
    }

    public static function return_response($result, $msg, $data, $code)
    {


        $clean_data = self::replaceNullWithEmpty($data);


        $response["result"] = $result;
        $response["message"] = $msg;
        $response["data"] =  ((is_array($clean_data) || is_object($clean_data)) ? $clean_data : []);
        // ((is_array($data) || is_object($data)) ? $data : []);
        \Log::info('------- response ------');
        \Log::info('response=  ',[$clean_data]);
        return response($response, $code);
    }

    public static function getSalesEmpTarget($user_id, $month_val)
    {
        \Log::info('------------------- getSalesEmpTarget ------------------- ');
        $month = \Carbon\Carbon::parse($month_val)->month;
        $year = \Carbon\Carbon::parse($month_val)->year;

        $all_quotes = Quotes::where('status', 3)->whereMonth('created_at', $month)->whereYear('created_at', $year)->
        where('user_id',$user_id)->where('created_by','=',\Auth::user()->creatorId())->get();

        $all_lead_ids = [];

        if ($all_quotes->count() > 0) {
            foreach ($all_quotes as $al_qt) {
                $get_sales_emp_lead = Lead::where('user_id', $user_id)
                    ->where('id', $al_qt->lead_id)
                    ->first();

                if ($get_sales_emp_lead) {
                    $all_lead_ids[] = $get_sales_emp_lead->id;
                }
            }
        }

        // $all_lead_ids = array_unique($all_lead_ids);

        $final_quote_records = Quotes::where('user_id',$user_id)->where('created_by','=',\Auth::user()->creatorId())   //whereIn('lead_id', $all_lead_ids)
            ->where('status', 3) //final status
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get();


        //12_11 change logic
        /*
        $wonLeads = Lead::where('user_id', $user_id)->where('stage_id', 5) //won status
            ->whereMonth('won_date', $month)->whereYear('won_date', $year)->pluck('id');
        if ($wonLeads->isEmpty()) {
            return 0;
        }
        $leadProducts = LeadProducts::whereIn('lead_id', $wonLeads)->get();
        $total = $leadProducts->sum(function ($product) {
            return $product->qty * $product->price;
        });
        */

        $total = $final_quote_records->sum(function ($product) {
            return $product->grand_total;
        });

        \Log::info('total achieve target value', ['key ' => $total]);
        \Log::info('------------------------------------------------------- ');
        return $total;
    }

    //emp incentive how much add that value return based on assign multiple target (sales emp bonus)
    public static function getSalesEmpFinalSalary($emp_id, $emp_base_sal, $is_sales_emp_target_set, $achieve_target)
    {
        \Log::info('------------------- getSalesEmpFinalSalary ------------------- ');

        \Log::info('is_sales_emp_target_set ', ['key  ' => $is_sales_emp_target_set]);
        if ($is_sales_emp_target_set == 1) {
            \Log::info('inside if emp target is set');
            //sales emp target is set
            $cur_date = Carbon::now();

            $sales_targets = EmployeeSalesTarget::with('getSalesEmployeeTarget')->where('employee_id', $emp_id)
                ->whereMonth('current_month_date', $cur_date->month)
                ->whereYear('current_month_date', $cur_date->year)
                ->get();

            // If no target set, return 0 incentive
            if ($sales_targets->isEmpty()) {
                return 0;
            }

            $sortedTargets = $sales_targets->sortBy(function ($t) {
                return $t->getSalesEmployeeTarget->max_target ?? 0;
            });

            $eligibleTarget = null;

            // Find the highest eligible target based on achieved value
            foreach ($sortedTargets as $target) {
                $targetDetails = $target->getSalesEmployeeTarget;
                if (!$targetDetails) continue;

                if ($achieve_target >= $targetDetails->max_target) {
                    $eligibleTarget = $target;
                }
            }


            // If no target achieved, return 0 incentive
            if (!$eligibleTarget) {
                return 0;
            }

            \Log::info('eligibleTarget ', ['key ' => $eligibleTarget]);

            $targetDetails = $eligibleTarget->getSalesEmployeeTarget;
            if (!$targetDetails) {
                return 0;
            }
            $legacyIncentivePercent = isset($eligibleTarget->incentive) ? (float) $eligibleTarget->incentive : null;
            $get_new_val = (float) $targetDetails->calculateDynamicIncentive((float) $achieve_target, $legacyIncentivePercent);

            \Log::info('bonus ', ['key ' => $get_new_val]);

            //update achieve total & update which target consider
            EmployeeSalesTarget::where('employee_id', $emp_id)
                ->whereMonth('current_month_date', $cur_date->month)
                ->whereYear('current_month_date', $cur_date->year)
                ->update(['is_eligible_target' => 0]);

            $get_rcd = EmployeeSalesTarget::where('id', $eligibleTarget->id)->first();
            if ($get_rcd) {
                $get_rcd->update(['achieve_amount' => $achieve_target, 'is_eligible_target' => 1]);
            }

            return  $get_new_val;
        }

        \Log::info('------------------------------------------------------- ');

        \Log::info('bonus ', ['key ' => 0]);
        return 0;
    }

    //------------- start nw ---------------

    //emp login then check working hrs according login or not
    public static function checkEmpLoginBtwWorkingHrs($day, $start_time, $end_time)
    {
        //login user
        if ($end_time == 0) {
            $avl_working = WorkingHours::where('day', $day)->where('start_time', '<', $start_time)->first();
            if (isset($avl_working) & !empty($avl_working)) {
                if ($start_time > $avl_working->end_time) {
                    return "Please Login only during working hours";
                }
                return 1;
            } else {
                return "working time not available";
            }
        }

        //logout-user
        if ($end_time == 1) {
            $avl_working = WorkingHours::where('day', $day)->orderBy('end_time', 'desc')->first();
            if ($avl_working) {
                if ($start_time < $avl_working->end_time) {
                    return 1;
                }

                // if ($start_time > $avl_working->end_time) {
                //     return $avl_working->end_time;
                // }
            } else {
                return 0;
            }
        }
    }

    //emp attendace according total hours sum return
    public static function monthly_emp_total_wk_hrs($current_date, $emp_id)
    {
        \Log::info('------------------- monthly_emp_total_wk_hrs ------------------- ');
        $startDate = date('Y-m-01', strtotime($current_date));
        $endDate = date('Y-m-t', strtotime($current_date));

        \Log::info('startDate ', ['key ' => $startDate]);
        \Log::info('endDate ', ['key ' => $endDate]);

        // Step 1: Fetch all attendance records for the month
        $attendanceRecords = Attendance::where('employee_id', $emp_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('is_present', 1)
            ->get(['date', 'total_hours']);

        \Log::info('attendance list ', ['key ' => $attendanceRecords]);

        // Group by date and sum total_hours per day (in seconds)
        $dailyTotals = [];

        foreach ($attendanceRecords as $record) {
            $date = $record->date;
            list($h, $m, $s) = explode(':', $record->total_hours);

            $seconds = ($h * 3600) + ($m * 60) + $s;

            if (!isset($dailyTotals[$date])) {
                $dailyTotals[$date] = 0;
            }

            $dailyTotals[$date] += $seconds;
        }

        if (empty($dailyTotals)) {
            return '00:00:00';
        }

        \Log::info('dailyTotals ', ['key ' => $dailyTotals]);

        $totalSeconds = array_sum($dailyTotals);

        \Log::info('totalSeconds ', ['key ' => $totalSeconds]);

        // Step 2: Get holiday dates not attended
        // $monthNumber = date('n', strtotime($current_date));
        $holidayDates = self::get_office_holidays($current_date);

        \Log::info('holidayDates ', ['key ' => $holidayDates]);

        $holidaySeconds = 0;

        foreach ($holidayDates as $holidayDate) {
            if (!isset($dailyTotals[$holidayDate])) {
                $dayOfWeek = date('w', strtotime($holidayDate)); // 0 = Sunday

                if ($dayOfWeek == 0) {
                    continue; // Skip Sunday
                }

                $working = \App\Models\WorkingHours::where('day', $dayOfWeek)->first();

                if ($working && $working->start_time && $working->end_time) {
                    $start = strtotime($working->start_time);
                    $end = strtotime($working->end_time);

                    if ($end > $start) {
                        $holidaySeconds += ($end - $start);
                    }
                }
            }
        }

        // Step 3: Format total attendance time
        $attendance_hours = sprintf(
            '%02d:%02d:%02d',
            floor($totalSeconds / 3600),
            floor(($totalSeconds % 3600) / 60),
            $totalSeconds % 60
        );

        // Step 4: Format total holiday time
        $holiday_hours = sprintf(
            '%02d:%02d:%02d',
            floor($holidaySeconds / 3600),
            floor(($holidaySeconds % 3600) / 60),
            $holidaySeconds % 60
        );

        // Step 5: Total (attendance + holidays)
        $totalMergedSeconds = $totalSeconds + $holidaySeconds;

        $total_working_hours = sprintf(
            '%02d:%02d:%02d',
            floor($totalMergedSeconds / 3600),
            floor(($totalMergedSeconds % 3600) / 60),
            $totalMergedSeconds % 60
        );

        \Log::info('total_working_hours ', ['key ' => $total_working_hours]);

        \Log::info('------------------------------------------------------- ');
        return $total_working_hours;
    }

    //return array & total leaves-dates of cuurent month holiday
    public static function get_office_holidays($current_date)
    {
        \Log::info('------------------- get_office_holidays ------------------- ');

        $monthStart = date("Y-m-01", strtotime($current_date)); // start of current month

        //12_11 chg bcz it return start-date not last-date of that month
        // $monthEnd = date("Y-m-d", strtotime($current_date));    // current date only

        $date_cur = Carbon::parse($current_date);
        $lastDateOfMonth = $date_cur->copy()->endOfMonth();
        $monthEnd = $lastDateOfMonth->format('Y-m-d');


        $holidays = Holiday::where('is_active', 1)->where(function ($query) use ($monthStart, $monthEnd) {
            $query->whereBetween('start_date', [$monthStart, $monthEnd])
                ->orWhereBetween('end_date', [$monthStart, $monthEnd])
                ->orWhere(function ($q) use ($monthStart, $monthEnd) {
                    $q->where('start_date', '<=', $monthStart)
                        ->where('end_date', '>=', $monthEnd);
                });
        })->get();

        $holidayDates = [];

        foreach ($holidays as $holiday) {
            $start = strtotime($holiday->start_date);
            $end = strtotime($holiday->end_date);

            for ($date = $start; $date <= $end; $date += 86400) {
                $current = date('Y-m-d', $date);
                if ($current >= $monthStart && $current <= $monthEnd) {
                    $holidayDates[] = $current;
                }
            }
        }

        \Log::info('holidayDates ', ['key ' => $holidayDates]);

        \Log::info('------------------------------------------------------- ');
        return array_unique($holidayDates);
    }

    // emp leave deduct when leave status set as 2(approve leave)
    public static function emp_remaining_leave($emp_id, $total_days)
    {
        \Log::info('------------------- emp_remaining_leave ------------------- ');

        $emp_rcd = Employee::find($emp_id);

        if ($emp_rcd) {
            if ($emp_rcd['remaining_leave'] != 0) {
                $emp_rcd['remaining_leave'] = $emp_rcd['remaining_leave'] - $total_days;
                $emp_rcd->save();
            }
        }
        \Log::info('------------------------------------------------------- ');
    }

    //check emp take leave or not
    public static function get_emp_leave_check($emp_id)
    {
        \Log::info('------------------- get_emp_leave_check ------------------- ');
        $emp_rcd = Employee::where('id', $emp_id)->first();
        if ($emp_rcd) {
            //not deduct salary
            if ($emp_rcd['remaining_leave']  != 0) {
                \Log::info('emp_rcd if value=1');
                \Log::info('---------------------------------------------------');
                return 1;
            } else  //deduct salary
            {
                \Log::info('emp_rcd else value=0');
                \Log::info('---------------------------------------------------');
                return 0;
            }
        }
        \Log::info('---------------------------------------------------');
    }

    //attendance insert temp
    public static  function insertMonthlyAttendance($employeeId, $year = 2025, $month = 11)
    {
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $data = [];

        while ($startDate->lte($endDate)) {
            if ($startDate->dayOfWeek !== Carbon::SUNDAY) {
                $data[] = [
                    'employee_id' => $employeeId,
                    'date' => $startDate->toDateString(),
                    'check_in' => '10:00:00',
                    'check_out' => '18:00:00',
                    'total_hours' => '08:00:00',
                    'is_present' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $startDate->addDay();
        }

        // Bulk insert into attendance table
        DB::table('attendances')->insert($data);
    }


    //return array of dates & remove sunday dates (2025,5)
    public static function office_working_dates($cur_year, $cur_month)
    {
        \Log::info('------------------- office_working_dates ------------------- ');

        $dates = [];
        $startDate = Carbon::createFromDate($cur_year, $cur_month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        while ($startDate->lte($endDate)) {
            if ($startDate->dayOfWeek !== Carbon::SUNDAY) {
                $dates[] = $startDate->toDateString(); // Format: YYYY-MM-DD
            }
            $startDate->addDay();
        }

        \Log::info('office working dates', ['key' => $dates]);

        \Log::info('------------------------------------------------------- ');


        return $dates;
    }

    //return json of date & total_working_hours(based on workingHours start-end-diff) (2025,5)
    public static function office_working_days($cur_year, $cur_month)
    {
        \Log::info('------------------- office_working_days ------------------- ');

        $dates = self::office_working_dates($cur_year, $cur_month);
        $result = [];

        foreach ($dates as $date) {
            $carbonDate = Carbon::parse($date);
            $dayOfWeek = $carbonDate->dayOfWeek; // 1 = Monday, ..., 6 = Saturday

            $workingHour = WorkingHours::where('day', $dayOfWeek)->first();

            if ($workingHour) {
                // Calculate total working hours as a time difference
                $start = Carbon::createFromTimeString($workingHour->start_time);
                $end = Carbon::createFromTimeString($workingHour->end_time);

                $totalHours = $start->diff($end)->format('%H:%I:%S');

                $result[] = [
                    'date' => $date,
                    'total_working_hours' => $totalHours,
                ];
            }
        }

        \Log::info('result ', ['key' => $result]);

        \Log::info('------------------------------------------------------- ');
        return $result;
    }


    //return json of days count & total hours_count
    public static function calculateWorkSummary(array $workingDays)
    {
        \Log::info('------------------- calculateWorkSummary ------------------- ');

        \Log::info('working days ', ['key' => $workingDays]);

        $totalSeconds = 0;
        $dateCount = count($workingDays);

        foreach ($workingDays as $entry) {
            list($hours, $minutes, $seconds) = explode(':', $entry['total_working_hours']);
            $totalSeconds += ($hours * 3600) + ($minutes * 60) + $seconds;
        }

        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        $totalTimeFormatted = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

        \Log::info('value func ', ['key' => [
            'total_days' => $dateCount,
            'total_working_hours' => $totalTimeFormatted,
        ]]);

        \Log::info('------------------------------------------------------- ');
        return [
            'total_days' => $dateCount,
            'total_working_hours' => $totalTimeFormatted,
        ];
    }

    //return per hrs salary based on original sal
    public static function emp_per_hrs_sal($emp_salary, $total_days_sumofhrs_per_month_json)
    {
        \Log::info('------------------- emp_per_hrs_sal ------------------- ');

        $totalDays = $total_days_sumofhrs_per_month_json['total_days'];
        $workingTime = $total_days_sumofhrs_per_month_json['total_working_hours'];

        // Convert HH:MM:SS to total seconds
        list($hours, $minutes, $seconds) = explode(':', $workingTime);
        $totalSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;

        if ($totalSeconds == 0) {
            \Log::info('perHourRate default', ['key' => $totalSeconds]);

            \Log::info('------------------------------------------------------- ');
            return 0; // Avoid division by zero
        }

        // Per second rate
        $perSecondRate = $emp_salary / $totalSeconds;

        // Convert to per hour
        $perHourRate = $perSecondRate * 3600;

        \Log::info('------------------------------------------------------- ');

        \Log::info('perHourRate ', ['key' => $perHourRate]);
        // Round to 2 decimal places
        return round($perHourRate, 2);
    }

    //return final salary of emp
    public static function emp_monthly_calculated_salary($emp_salary, $total_days_json, $actual_worked_time, $emp_id)
    {
        \Log::info('---------- emp_monthly_calculated_salary --------');
        // Step 1: Calculate per hour salary
        $per_hr_salary = self::emp_per_hrs_sal($emp_salary, $total_days_json);

        \Log::info('per_hr_salary ', ['key ' => $per_hr_salary]);

        $deduction_check =  self::emp_leave_check_deduct($emp_id, $per_hr_salary);

        \Log::info('deduction_check ', [$deduction_check]);

        $is_salary_deduct = $deduction_check['is_salary_deduct'];
        $extra_sal = $deduction_check['extra_sal'];

        \Log::info('is_salary_deduct ', ['key ' => $is_salary_deduct]);
        \Log::info('extra_sal ', ['key ' => $extra_sal]);
        \Log::info('actual_worked_time ', ['key ' => $actual_worked_time]);

        // Step 2: Convert actual worked time to decimal hours
        list($h, $m, $s) = explode(':', $actual_worked_time);
        $decimal_hours = $h + ($m / 60) + ($s / 3600);

        \Log::info('decimal_hours ', ['key' => $decimal_hours]);

        // Step 3: Multiply
        $final_salary = round($per_hr_salary * $decimal_hours, 2);

        \Log::info('final_salary 1st ', ['key' => $final_salary]);

        if ($is_salary_deduct == 0) {
            //salary not deduct and add leave dates extra sal added

            \Log::info('salary not deduct and add leave dates extra sal added ');
            $final_salary = $final_salary + $extra_sal;

            \Log::info('final_salary if', ['key' => $final_salary]);
        } else {
            //salary deduct and minus
            \Log::info('salary deduct and minusl ');
            $final_salary = $final_salary - $extra_sal;

            \Log::info('final_salary else', ['key' => $final_salary]);
        }

        \Log::info('final_salary ', [$final_salary]);
        \Log::info('---------------------------------------------------');

        return  $final_salary > 0 ? $final_salary : 0; //mk temp
    }

    //return 1 day salary for deduct in main-sal
    public static function emp_leave_check_deduct($emp_id, $emp_per_hr_sal)
    {
        \Log::info('------------------- emp_leave_check_deduct ------------------- ');

        //check remaining leave count of employee [fun return 1=not sal deduct,0=sal deduct]
        $is_leave_avail_or_not = self::get_emp_leave_check($emp_id);
        $holidaySeconds = 0;

        \Log::info('is_leave_avail_or_not=', [$is_leave_avail_or_not]);

        if ($is_leave_avail_or_not == 0) {
            // 1 day leave deduction
            \Log::info('if is_leave_avail_or_not == 0  ');

            /* 14_11_25 not sure this
            $working = \App\Models\WorkingHours::orderBy('id','desc')->first();

            if ($working && $working->start_time && $working->end_time) {
                $start = strtotime($working->start_time);
                $end = strtotime($working->end_time);

                if ($end > $start) {
                    $holidaySeconds += ($end - $start);
                }
            }

            $deduct_hours = $holidaySeconds / 3600; // Convert to decimal hours
             \Log::info('deduct_hours  ',[$deduct_hours]);

            $deduct_prc = round($deduct_hours * $emp_per_hr_sal, 2);
            */

            $emp_leave_date_extra_sal = self::check_employee_leave_status_hours($emp_id, $emp_per_hr_sal);

            \Log::info('emp_leave_date_extra_sal ', ['key' => $emp_leave_date_extra_sal]);

            if ($emp_leave_date_extra_sal) {
                \Log::info('total achieve target value', [
                    'key' => ['is_salary_deduct' => 1, 'extra_sal' => $emp_leave_date_extra_sal]
                ]);

                \Log::info('------------------------------------------------------- ');

                return ['is_salary_deduct' => 1, 'extra_sal' => $emp_leave_date_extra_sal];
            }
        } else {
            // salary not deduct but hours that add bcz attendance has not that hours
            //check employee is on leave then working hours according hrs * per-hours-sal which are added into main sal

            \Log::info('else is_leave_avail_or_not ');


            //if remaining employee leave is not 0 then add hours
            //flag pass which define salary is deduct if deduct that how much deduct &
            //if not salary deduct than check emp is leave if leave then how much salary added
            $emp_leave_date_extra_sal = self::check_employee_leave_status_hours($emp_id, $emp_per_hr_sal);

            \Log::info('emp_leave_date_extra_sal ', ['key' => $emp_leave_date_extra_sal]);

            if ($emp_leave_date_extra_sal) {
                \Log::info('return value', [
                    'key' => ['is_salary_deduct' => 0, 'extra_sal' => $emp_leave_date_extra_sal]
                ]);

                return ['is_salary_deduct' => 0, 'extra_sal' => $emp_leave_date_extra_sal];

                \Log::info('------------------------------------------------------- ');
            }
        }

        \Log::info('out ', ['key' => ['is_salary_deduct' => 0, 'extra_sal' => 0]]);
        \Log::info('---------------------------------------------------');
        return ['is_salary_deduct' => 0, 'extra_sal' => 0];
        // return round($deduct_hours * $emp_per_hr_sal, 2);
    }

    public static function emp_final_sal_store($emp_id, $final_sal, $holiday_jsn, $office_wrk_hrs, $office_wrk_days, $emp_per_day_salary, $emp_work_hrs, $cur_date, $sale_bonus, $salary_month)
    {
        \Log::info('------------------- emp_final_sal_store ------------------- ');

        $salary_status = EmployeeSalaryDetail::where(['employee_id' => $emp_id, 'salary_month' => $salary_month])->latest()->first();

        if ($salary_status) {

            if ($salary_status->payment_status == 'paid') {
                return true;
            }

            $salary_status->delete();
        }


        $new_recd = EmployeeSalaryDetail::create([
            'employee_id' => $emp_id,
            'final_salary' => $final_sal,
            'holiday_json' => json_encode($holiday_jsn),
            'office_working_hours' => $office_wrk_hrs,
            'office_working_days' => $office_wrk_days,
            'emp_per_day_sal' => $emp_per_day_salary,
            'emp_working_hours' => $emp_work_hrs,
            'generate_salary_cur_date' => $cur_date,
            'sales_bonus' => $sale_bonus,
            'payment_status' => 'unpaid',
            'salary_month' => $salary_month,
        ]);

        \Log::info('EmployeeSalaryDetail tbl insert & its id ', ['key' => $new_recd]);

        \Log::info('------------------------------------------------------- ');
    }

    public static function generate_emp_salary($emp_id, $salary, $is_sale_emp, $sales_bonus)
    {

        \Log::info('------------------- generate_emp_salary ------------------- ');

        \Log::info('emp_id ', ['key ' => $emp_id]);
        \Log::info('salary ', ['key ' => $salary]);
        \Log::info('is_sale_emp ', ['key ' => $is_sale_emp]);
        \Log::info('sales_bonus ', ['key ' => $sales_bonus]);

        $empSalary = EmployeeSalaryDetail::select('salary_month')->where(['employee_id' => $emp_id, 'payment_status' => 'paid'])->latest()->first();

        \Log::info('empSalary ', ['key ' => $empSalary]);

        if ($empSalary) {
            \Log::info('if empSalary ');

            $unpaidSalary = self::getMonthRangeAfter($empSalary->salary_month);

            \Log::info('unpaidSalary', ['key ' => $unpaidSalary]);
        } else {

            \Log::info('else empSalary ');
            // Get attendances of emp.
            $attendanceRecords = Attendance::where('employee_id', $emp_id)->where('is_present', 1)
                ->select('date')
                ->oldest()->first();

            \Log::info('attendanceRecords ', ['key ' => $attendanceRecords]);

            if ($attendanceRecords) {

                $givenDate = \Carbon\Carbon::parse($attendanceRecords->date);
                $start = $givenDate->copy()->subMonth()->startOfMonth();

                $unpaidSalary = self::getMonthRangeAfter($start);

                \Log::info('if attendanceRecords =>$unpaidSalary', ['key' => $unpaidSalary]);
            } else {

                \Log::info('else attendanceRecords ');

                $cyr_date_new = date('Y-m-d');
                $salary_month = \Carbon\Carbon::parse($cyr_date_new)->format('Y-m-d');

                // check current month has mul record then delete & insert new record
                $check_all_cur_month = EmployeeSalaryDetail::where('employee_id', $emp_id)->whereDate('salary_month', $salary_month)->get();

                if (count($check_all_cur_month) > 0) {
                    foreach ($check_all_cur_month as $ch_month) {
                        $ch_month->delete();
                    }
                }

                \Log::info('attendance not found then 0 salary added');

                // if attendace not found then salary blank set
                EmployeeSalaryDetail::create([
                    'employee_id' => $emp_id,
                    'final_salary' => 0.00,
                    'holiday_json' => json_encode([]),
                    'office_working_hours' => '00:00:00',
                    'office_working_days' => 0,
                    'emp_per_day_sal' => 0.00,
                    'emp_working_hours' => '00:00:00',
                    'generate_salary_cur_date' => $cyr_date_new,
                    'sales_bonus' => 0.00,
                    'payment_status' => 'unpaid',
                    'salary_month' => $cyr_date_new,
                ]);

                $unpaidSalary = [];
            }
        }

        // return $unpaidSalary;

        \Log::info('out unpaidSalary', ['key' => $unpaidSalary]);

        $current_date = date('Y-m-d');

        \Log::info('current_date ', ['key ' => $current_date]);

        if (count($unpaidSalary) > 0) {

            foreach ($unpaidSalary as $salary_date) {

                $date = new \DateTime($salary_date);
                $current_year = $date->format('Y'); // 2025
                $current_month = $date->format('m'); // 08

                \Log::info('salary_date ', ['key' => $salary_date]);

                $json_date_with_total_wk_hrs = self::office_working_days($current_year, $current_month);

                \Log::info('json_date_with_total_wk_hrs', ['key ' => $json_date_with_total_wk_hrs]);

                $json_working_days_count_sum_of_hrs = self::calculateWorkSummary($json_date_with_total_wk_hrs);

                \Log::info('json_working_days_count_sum_of_hrs ', ['key ' => $json_working_days_count_sum_of_hrs]);


                $holiday_jsn = self::get_office_holidays($salary_date);

                \Log::info('holiday_jsn ', ['key ' => $holiday_jsn]);

                $per_hrs = self::emp_per_hrs_sal($salary, $json_working_days_count_sum_of_hrs);

                \Log::info('per_hrs', ['key ' => $per_hrs]);

                $actual_worked_time = self::monthly_emp_total_wk_hrs($salary_date, $emp_id);

                \Log::info('actual_worked_time ', ['key ' => $actual_worked_time]);

                if ($actual_worked_time != '00:00:00') {
                    \Log::info('actual_worked_time if');

                    $final_salary = self::emp_monthly_calculated_salary($salary, $json_working_days_count_sum_of_hrs, $actual_worked_time, $emp_id);

                    \Log::info('final_salary ', ['key' => $final_salary]);

                    if ($sales_bonus != 0 && $final_salary != 0 && isset($is_sale_emp) && $is_sale_emp == 1) {
                        //sales emp sales-bonus added

                        \Log::info('sales emp sales-bonus added');

                        $sales_salary =  $final_salary + $sales_bonus;
                        self::emp_final_sal_store(
                            $emp_id,
                            $sales_salary,
                            $holiday_jsn,
                            $json_working_days_count_sum_of_hrs['total_working_hours'],
                            $json_working_days_count_sum_of_hrs['total_days'],
                            $per_hrs,
                            $actual_worked_time,
                            $current_date,
                            $sales_bonus,
                            $salary_date
                        );
                    } else {   //non-sales emp

                        \Log::info('non sales person');
                        self::emp_final_sal_store(
                            $emp_id,
                            $final_salary,
                            $holiday_jsn,
                            $json_working_days_count_sum_of_hrs['total_working_hours'],
                            $json_working_days_count_sum_of_hrs['total_days'],
                            $per_hrs,
                            $actual_worked_time,
                            $current_date,
                            0,
                            $salary_date
                        );
                    }
                } else {
                    //attendace inside not found record

                    \Log::info('actual_worked_time else');
                    \Log::info('attendace inside not found record (emp not login)');
                    $final_salary = 0;

                    if ($sales_bonus != 0 && $final_salary != 0 && isset($is_sale_emp) && $is_sale_emp == 1) {   //sales emp sales-bonus added

                        \Log::info('sales person');

                        $sales_salary =  $final_salary + $sales_bonus;
                        self::emp_final_sal_store(
                            $emp_id,
                            $sales_salary,
                            $holiday_jsn,
                            $json_working_days_count_sum_of_hrs['total_working_hours'],
                            $json_working_days_count_sum_of_hrs['total_days'],
                            $per_hrs,
                            $actual_worked_time,
                            $current_date,
                            $sales_bonus,
                            $salary_date
                        );
                    } else {
                        //non-sales emp

                        \Log::info('non sales person');

                        self::emp_final_sal_store(
                            $emp_id,
                            $final_salary,
                            $holiday_jsn,
                            $json_working_days_count_sum_of_hrs['total_working_hours'],
                            $json_working_days_count_sum_of_hrs['total_days'],
                            $per_hrs,
                            $actual_worked_time,
                            $current_date,
                            0,
                            $salary_date
                        );
                    }
                }
            }
        }

        \Log::info('------------------------------------------------------- ');

        return "success";
    }

    // -------- NEW 2_10 -----------
    public static function defaultImage()
    {
        return asset('public/build/assets/images/users/user-dummy-img.jpg');
    }

    public static function websiteLogo($for_pdf = false)
    {
        $logo = self::settingsTable()->where('name', 'website_logo')->value('value');

        if (!empty($logo)) {
            $storagePath = public_path('website_logo/' . $logo);

            if (file_exists($storagePath)) {
                return $for_pdf ? $storagePath : asset('public/website_logo/' . $logo);
            }
        }

        $default = public_path('build/assets/images/users/user-dummy-img.jpg');
        return $for_pdf ? $default : asset('public/build/assets/images/users/user-dummy-img.jpg');
    }

    public static function getWebsiteName()
    {
        return self::settingsTable()->where('name', 'website_name')->value('value');
    }

    public static function getWebsiteShortName()
    {
        return self::settingsTable()->where('name', 'website_short_name')->value('value');
    }

    public static function getUnitTypes()
    {
        return  UnitTypes::get()
            ->pluck('name', 'id')
            ->mapWithKeys(fn($value, $key) => [$key => ucwords(preg_replace('/(?<!^)[A-Z]/', ' $0', $value))])
            ->toArray();
    }

    public static function getUnits($id = null)
    {
        $result = Units::get();

        if ($id) {
            $result = $result->where('type_id', $id);
        }

        $result = $result->pluck('name', 'id')
            ->mapWithKeys(fn($value, $key) => [$key => ucwords(preg_replace('/(?<!^)[A-Z]/', ' $0', $value))])
            ->toArray();


        return $result;
    }

    public static function getTaxValue($user_address_id)
    {
        $user = Address::where('id', $user_address_id)->first();

        if (!$user) {
            return [
                'CGST' => 0,
                'SGST' => 0,
                'IGST' => 0,
                // 'tax_type' => 'none',
            ];
        }

         if (empty($user->country) || empty($user->state) ) { //|| empty($user->city)
             return [
                'CGST' => 0,
                'SGST' => 0,
                'IGST' => 0,
                // 'tax_type' => 'none',
            ];
        }

        $comp_billing_adr = self::settingsTable()->where('name', 'company_address_id')->value('value');

        $comp_adr_settings = Address::where('id', $comp_billing_adr)->first();

        if (empty($comp_adr_settings->country) || empty($comp_adr_settings->state) ) { //|| empty($comp_adr_settings->city)
             return [
                'CGST' => 0,
                'SGST' => 0,
                'IGST' => 0,
                'GST'=>1,
                // 'tax_type' => 'none',
            ];
        }

        $tax_value = DB::table('taxes')->pluck('percentage', 'name')->toArray();

        if (!isset($comp_adr_settings['country']) || !isset($comp_adr_settings['state']) ) { //|| !isset($comp_adr_settings['city'])
            return [
                'CGST' => 0,
                'SGST' => 0,
                'IGST' => 0,
                'GST'=>1,
                // 'tax_type' => 'none',
            ];
        }

        $setting_country = (int)$comp_adr_settings['country'];
        $setting_state   = (int)$comp_adr_settings['state'];
        $setting_city    = (int)$comp_adr_settings['city'];

        $user_country = (int)$user->country;
        $user_state   = (int)$user->state;
        $user_city    = (int)$user->city;

        if ($user_country === $setting_country)
        {
            if ($user_state === $setting_state) {
                // Same country & state → CGST + SGST
                return [
                    'CGST' => 1,//floatval($tax_value['CGST'] ?? 0),
                    'SGST' => 1,//floatval($tax_value['SGST'] ?? 0),
                    'IGST' => 0,
                    'GST'=>0,
                    // 'tax_type' => 'CGST+SGST',
                ];
            } else {
                // Same country, different state → IGST
                return [
                    'CGST' => 0,
                    'SGST' => 0,
                    'IGST' => 1,//floatval($tax_value['IGST'] ?? 0),
                     'GST'=>0,
                    // 'tax_type' => 'IGST',
                ];
            }
        } else {
            // Different country → IGST
            return [
                'CGST' => 0,
                'SGST' => 0,
                'IGST' => 1,//floatval($tax_value['IGST'] ?? 0),
                'GST'=>0,
                // 'tax_type' => 'IGST',
            ];
        }
    }

    // adr_slab_list (gst_json) return gst_rate_list according adr_json
    public static function gst_slab()
    {
        $query = GstSlabMaster::query();

        if (Auth::check()) {
            $query->where('created_by', Auth::user()->creatorId());
        }

        return $query->pluck('rate', 'id')->toArray();
    }

    // adr_slab_list (gst_json) return gst_rate_list according adr_json
    public static function gstNameList($adr_slab_list)
    {
        $activeGstKeys = array_filter($adr_slab_list, function ($value) {
            return $value != 0;
        });

        if (count($activeGstKeys) === 0) {
            return [];
        }

        // GST type name
        $gstKeys = array_keys($activeGstKeys);
        $gstTypeName = count($gstKeys) > 1
            ? implode('+', $gstKeys)   // CGST+SGST
            : $gstKeys[0];             // IGST

        // Get slabs (5, 12, 18 ...)
        $gstSlabs = GstSlabMaster::pluck('rate')->toArray();

        $rates = [];
        $gstCount = count($gstKeys);

        foreach ($gstSlabs as $rate) {

            if ($gstCount > 1) {
                // Split rate equally
                $split = $rate / $gstCount;
                $rates[$rate] = implode('+', array_fill(0, $gstCount, $split));
            } else {
                // Single GST (IGST)
                $rates[$rate] = (string)$rate;
            }
        }

        return [
            $gstTypeName => $rates
        ];
    }

    public static function getCompanyBillingAddress()
    {
        $comp_billing_adr = self::settingsTable()->where('name', 'billing_address_id')->value('value');

        $comp_adr_settings = Address::where('id', $comp_billing_adr)->first();
        if ($comp_adr_settings) {
            return $comp_adr_settings;
        }
        return '';
    }

    public static function WebsiteName()
    {
        $comp_name = self::settingsTable()->where('name', 'website_name')->value('value');
        if ($comp_name) {
            return $comp_name;
        }
        return '';
    }

    public static function getUnitName($unitId)
    {
        $unit = Units::where('id', $unitId)->first();
        if ($unit) {
            return $unit->name;
        }
        return '';
    }

    //emp on leave but that leave is official then salary not deduct
    public static function check_employee_leave_status_hours($emp_id, $emp_per_hr_sal)
    {
        \Log::info('------------------- check_employee_leave_status_hours ------------------- ');

        $leave_date_salary_bal = 0;
        $today = Carbon::today();

        //get all leave which are approved by hr current month
        $leaves = \App\Models\Leave::where('employee_id', $emp_id)
            ->where('status', 2) // approved
            ->whereMonth('start_date', Carbon::now()->month)
            ->whereYear('start_date', Carbon::now()->year)
            ->get();

        \Log::info('leaves ', ['key ' => $leaves]);



        $uniqueDates = [];

        //check current date before or equal to leave_date & must unique dates
        foreach ($leaves as $leave) {

            $start = Carbon::parse($leave->start_date);
            $end   = Carbon::parse($leave->end_date);

            while ($start->lte($end)) {

                // only count past days + today
                if ($start->lte($today)) {
                    $date = $start->format('Y-m-d');
                    $uniqueDates[$date] = true;
                }

                $start->addDay();
            }
        }


        // return count of unique dates
        \Log::info('unique date ', ['key ' => $uniqueDates]);

        //get working total hours which avaible in leave-dates
        if (count($uniqueDates) > 0) {
            //leave-date list according how much hours total
            $total_hrs = self::get_working_dates_hours($uniqueDates);
            \Log::info('total_hrs ', ['key ' => $total_hrs]);

            // 2. Ensure per-hour salary exists
            $emp_per_hr_sal = floatval($emp_per_hr_sal);
            \Log::info('emp_per_hr_sal ', ['key ' => $emp_per_hr_sal]);

            // 3. Calculate salary deduction OR salary balance
            //    (leave hours × per-hour salary)
            $leave_date_salary_bal = round($total_hrs * $emp_per_hr_sal, 2);
        }

        \Log::info('leave_date_salary_bal ', ['key ' => $leave_date_salary_bal]);

        \Log::info('------------------------------------------------------- ');

        return $leave_date_salary_bal;
    }


    //emp leave_date list pass that dates check in working-hours table and return total hrs
    public static function get_working_dates_hours($dates)
    {
        \Log::info('------------------- get_working_dates_hours ------------------- ');

        $totalHours = 0;

        foreach ($dates as $date => $val) {
            $carbonDate = Carbon::parse($date);
            $dayNumber  = $carbonDate->dayOfWeekIso;  // Mon=1..Sun=7

            \Log::info("Checking date $date — Day number: $dayNumber");

            $working = \App\Models\WorkingHours::where('day', $dayNumber)->first();

            if (!$working) {
                \Log::info("No working-hours found for weekday $dayNumber — skipping $date");
                continue;
            }

            $start = Carbon::createFromFormat('H:i:s', $working->start_time);
            $end   = Carbon::createFromFormat('H:i:s', $working->end_time);

            if ($end->lte($start)) {
                \Log::info("Invalid working time range on $date — skipping");
                continue;
            }

            $hours = $start->diffInHours($end);
            $totalHours += $hours;
        }

        \Log::info('totalHours ', ['key' => $totalHours]);
        \Log::info('------------------------------------------------------- ');
        return $totalHours;
    }

    public static function getMonthRangeAfter($date)
    {
        \Log::info('------------------- getMonthRangeAfter ------------------- ');

        $range = [];
        // $today = Carbon::today();
        $today = Carbon::parse('2025-11-30'); //temp must remove mk
        $currentMonthStart = $today->copy()->startOfMonth();
        $range[] = $currentMonthStart;


        \Log::info('------------------------------------------------------- ');

        \Log::info('range ', ['key ' => $range]);

        return $range;

        /* 14_11_25 mk
        temp cmt bcz previous month sal & current month both not works together
        $givenDate = Carbon::parse($date);
        $today = Carbon::today();
        $currentMonthStart = $today->copy()->startOfMonth();

        $range = [];

        // Start from next month after given date
        $start = $givenDate->copy()->addMonth()->startOfMonth();

        while ($start->lt($currentMonthStart)) {
            $range[] = $start->format('Y-m-01');
            $start->addMonth();
        }

        // Check if today is in the last 5 days of the month
        if ($today->diffInDays($today->copy()->endOfMonth()) < 5) {
            $range[] = $currentMonthStart->format('Y-m-01');
        }

        \Log::info('range ',['key'=>$range]);

        return $range;
        */
    }

    public static function paymentStatus($status)
    {

        if ($status == 'paid') {
            return '<span class="badge bg-success">Paid</span>';
        }

        return '<span class="badge bg-danger">Unpaid</span>';
    }

    public static function getInvoiceLayout(): string
    {
        $creatorId = Auth::check() ? Auth::user()->creatorId() : 1;
        $layout = self::settingsTable()->where('name', 'invoice_layout')->value('value');

        if (!$layout && self::settingsConnectionName() === 'landlord') {
            $layout = self::settingsTable()
                ->where('name', 'invoice_layout')
                ->where('created_by', $creatorId)
                ->value('value');
        }

        $allowed = ['layout_1', 'layout_2', 'layout_3', 'layout_4'];
        return in_array($layout, $allowed, true) ? $layout : 'layout_1';
    }

    public static function resolveOrderInvoiceTemplate(bool $forPdf = false): string
    {
        $layout = self::getInvoiceLayout();

        if ($forPdf) {
            return match ($layout) {
                'layout_2' => 'order.invoice',
                default => 'order.invoice_new',
            };
        }

        return 'order.invoice-view';
    }

     public static function order_pdf_generate_store($id, $folder)
    {
        $order = Order::where('id',$id)->first();
        $printOptions = ['original' => 1];
        $invoiceLayout = self::getInvoiceLayout();
        $invoiceTemplate = self::resolveOrderInvoiceTemplate(true);

        $invoices = '';

        $printCount = count($printOptions);
        $print = 0;

        $company_name='';
	        if($order->customer_id)
	        {
	            $company_detail = Entity::where('id',$order->customer_id)->first();
	            $company_name=$company_detail->company_name;
	        }
	        $invoiceTerms = app(TermsAndConditionService::class)
	            ->getConfiguredInvoiceTerms(config('database.default', 'mysql'));

	        foreach (array_keys($printOptions) as $val) {
            $print++;

            $data = [
                'order' => $order,
                    'order_products' => $order->orderProducts,
                    'bank_detail' => BankDetail::first(),
                    'qrCode' => '',
                    'print_option' => $val,
	                    'for_pdf' => true,
	                    'invoice_layout' => $invoiceLayout,
	                    'check_discount_allow'=>self::isDiscountAllowed(),
	                    'invoice_terms' => $invoiceTerms,
	            ];

            $invoices .= view($invoiceTemplate, $data)->render();

            if ($printCount !== $print) {
                $invoices .= '<div class="page-break"></div>';
            }
        }

        $pdf = PDF::loadHTML($invoices);

        $file_name = 'invoice-'.$order->order_number . '_' . time() . '.pdf';

        $folder_path = storage_path('uploads/' . $folder);
        $full_path = $folder_path . '/' . $file_name;

        if (!File::exists($folder_path)) {
            File::makeDirectory($folder_path, 0775, true);
        }

        if (!empty($order->order_invoice)) {
            $old_file = $folder_path . '/' . $order->order_invoice;
            if (File::exists($old_file)) {
                File::delete($old_file);
            }
        }

        $pdf->save($full_path);

         $order->update([
            'order_invoice' => $file_name
        ]);
    }
}
