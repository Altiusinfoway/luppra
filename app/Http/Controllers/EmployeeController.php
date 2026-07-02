<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Validator;
use App\Models\Role;
use Hash;
use Auth;
use File;
use App\Models\Utility;
use Illuminate\Support\Facades\Http;
use App\Models\Department;
use App\Models\Designation;
use App\Models\SalesTarget;
use App\Models\LeaveRule;
use App\Models\LocationHistory;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax())
        {
            try {
                $query = Employee::with('user')->select('id', 'name', 'email', 'dob','user_id');

                if ($request->name) {
                    $query->where('name', 'like', '%' . $request->name . '%');
                }

                $data = $query->orderBy('id', 'desc')->get();

                return DataTables::of($data)
                ->addIndexColumn()
                    ->addColumn('role', function($row){
                        return $row->user ? $row->user->type : '';
                    })
                    ->addColumn('action', function ($row) {

                        $editUrl = route("employees.edit", [$row->user_id, 'user_section' => 'employee']);

                        $actions = '
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-more-fill align-middle"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a href="' . $editUrl . '" class="dropdown-item edit-item-btn">
                                            <i class="ri-eye-line align-bottom me-2 text-muted"></i> View
                                        </a>
                                    </li>';

                        // ✅ Show Map button ONLY for Sales user
                        if ($row->user_id)
                        {
                            $user_rcd = User::where('id',$row->user_id)->first();

                            if($user_rcd && $user_rcd->type == 'Sales')
                            {
                                $mapUrl = route('employees.map_index', $row->user_id);
                                $actions .= '
                                        <li>
                                            <a href="' . $mapUrl . '" class="dropdown-item">
                                                <i class="ri-map-pin-line align-bottom me-2 text-muted"></i> Map
                                            </a>
                                        </li>';
                            }

                        }

                        $actions .= '
                                </ul>
                            </div>';

                        return $actions;
                    })
                    ->rawColumns(['role','action'])
                    ->setRowClass('main-row')
                    ->make(true);
            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        }

        return view('employee.index');
    }

    public function edit(Request $request,$id)
    {
        $user_section = $request->input('user_section');
        $data['salary_type_list'] =[1=>"Monthly"];
        $data['department_list']=Department::all();
        $data['sales_target_list'] = SalesTarget::all();
        $data['user_data'] = User::with('roles')->where('id',$id)->first();
        $roleQuery = Role::query()->where('name', '!=', 'client');
        if (!app()->bound('currentTenant')) {
            $roleQuery->where('created_by', '=', \Auth::user()->creatorId());
        }
        $data['roles'] = $roleQuery->get()->pluck('name', 'id');
        $data['user_type_list'] = User::$predefineUserTypeList;
        $data['user_role_ids'] = optional($data['user_data']->roles->first())->id;
        if($user_section)
        {
            $data['user_id']=$id;
            $data['emp_data'] =Employee::where('user_id',$id)->first();

            $sales_dept = Department::where('id',$data['emp_data']->department_id)->whereRaw('LOWER(name) LIKE ?', ['%sales%'])->first();
            $data['isSalesEmp'] =0;
            if(!empty($sales_dept))
            {
                $data['isSalesEmp']=1;
            }
        }
        else
        {
            $data['emp_id']=$id;
            $data['emp_data'] =Employee::find($id);
        }

        return view('employee.create',$data);
    }

    public function update(Request $request,$id)
    {

        $user_section = $request->input('user');
        $input =$request->all();
        $request->validate([
            'name' => [
                 'required'
             ],
             'phone' => 'required|numeric|digits:10',
             'email' => 'required|email|unique:tenant.users,email,' . $id,
             'gender' => 'required',
             'dob'   => 'required',
             'company_doj' => 'required',
             'address'=>'required',
             'department_id'=>'required|not_in:0',
             'designation_id'=>'required|not_in:0',
             'role'=>'required',
             'user_type'=>'required',
         ]);


        if(!empty($user_section))
        {
           //id from user-id
           $user_data= User::find($id);
           $input['user_id']=$user_data['id'];
           $input['created_by']=\Auth::user()->creatorId();

           $emp_exist = Employee::where('user_id',$id)->first();


           if(!empty($emp_exist))
           {

                $image = $emp_exist->documents;
                if($request->hasFile('documents'))
                {
                    $filenameWithExt = $request->file('documents')->getClientOriginalName();

                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('documents')->getClientOriginalExtension();
                    $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                    $dir = 'uploads/employee/';
                    $image_path = $dir . $fileNameToStore;
                    if (\File::exists($image_path)) {
                        \File::delete($image_path);
                    }
                    $url = '';
                    $path = Utility::upload_file($request,'documents',$fileNameToStore,$dir,[]);

                    if($path['flag'] == 1){
                        $url = $path['url'];
                    }else{
                        return redirect()->back()->with('error', __($path['msg']));
                    }

                    $image  = !empty($request->documents) ? $image_path : '';

                }
                $input['documents']=$image;

                Utility::UserDetailsUpdate($id,$input);

                if(empty($input['no_of_leave']))
                {
                    $leave_rule = LeaveRule::where('id',1)->first();
                    if($leave_rule)
                    {
                        $input['no_of_leave']= $leave_rule['total_leave'];
                        $input['remaining_leave']= $leave_rule['total_leave'];
                    }
                    else
                    {
                        $input['no_of_leave']= 0;
                    }
                }
                else
                {
                    $input['no_of_leave']= $request->no_of_leave;
                    $input['remaining_leave']= $request->no_of_leave;
                }

                $emp_exist->update($input);
           }
           else
           {
                $request->validate([
                    'documents' => 'required|file|mimes:doc,pdf,docx|max:2048',
                    'email' => 'required|email|unique:tenant.users',
                ]);

                $image = "";
                if($request->hasFile('documents'))
                {
                    $filenameWithExt = $request->file('documents')->getClientOriginalName();
                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('documents')->getClientOriginalExtension();
                    $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                    $dir = 'uploads/employee/';
                    $image_path = $dir . $fileNameToStore;
                    if (\File::exists($image_path)) {
                        \File::delete($image_path);
                    }
                    $url = '';
                    $path = Utility::upload_file($request,'documents',$fileNameToStore,$dir,[]);

                    if($path['flag'] == 1){
                        $url = $path['url'];
                    }else{
                        return redirect()->back()->with('error', __($path['msg']));
                    }
                     $image  = !empty($request->documents) ? $fileNameToStore : '';
                }
                $input['documents']=$image;

                if(empty($input['no_of_leave']))
                {
                    $leave_rule = LeaveRule::where('id',1)->first();
                    if($leave_rule)
                    {
                        $input['no_of_leave']= $leave_rule['total_leave'];
                        $input['remaining_leave']= $leave_rule['total_leave'];
                    }
                    else
                    {
                        $input['no_of_leave']= 0;
                    }
                }
                else
                {
                    $input['no_of_leave']= $request->no_of_leave;
                    $input['remaining_leave']= $request->no_of_leave;
                }

                $emp_data = Employee::create($input);

                Utility::UserDetailsUpdate($emp_data['user_id'],$input);
           }

           //update role & role type
           $roles[] = $request->role;
           $user_data->roles()->sync([$request->role]);
           $user_data->update(['type'=>$request->user_type]);
        }

        return redirect()->route('employees.index')->with(['success'=>"Employee has been updated successfully"]);

    }

    public function update_password(Request $request,$id)
    {
        $request->validate([
            'password' => 'required|required_with:confirm_password|same:confirm_password',
            'confirm_password' => 'required',
            'old_password'=>'required'
        ]);

        $user_section = $request->input('user');
        $input=$request->all();
        $input['password'] = Hash::make($request->password);

        if(!empty($user_section))
        {
            //id from user
            $user_data= User::find($id);
            $input['user_id']=$user_data['id'];
            $input['created_by']=\Auth::user()->creatorId();

            $emp_exist = Employee::where('user_id',$id)->first();
            if(!empty($emp_exist))
            {
                if($emp_exist->password != null)
                {
                    if (! Hash::check($request->old_password, $emp_exist->password)) {
                        return response()->json([
                            'error' => true,
                            'cust_error'=>1,
                            'message' => 'Old password is incorrect.'
                        ], 422);
                    }
                }

                $emp_exist->update($input);
               Utility::UserDetailsUpdate($emp_exist['user_id'],$input);
            }

        }

        return response()->json([
            'success' => 'true',
            'redirect_url' => route('employees.index')
        ]);
    }

    public function update_bank_detail(Request $request,$id)
    {
        $user_section = $request->input('user');
        $input =$request->all();

        $validatedData = $request->validate([
            'account_holder_name' => 'required',
            'account_number' => 'required|digits_between:9,18',
            'bank_name' => 'required',
            'bank_identifier_code' => 'required',
            'branch_location' => 'required',
            'account' => 'required',
            // 'salary_type' => 'required|not_in:0',
            'salary' => 'required',
        ]);

        if(!empty($user_section))
        {
            //id from user table
            $user_data= User::find($id);
            $input['user_id']=$user_data['id'];
            $input['created_by']=\Auth::user()->creatorId();

            $emp_exist = Employee::where('user_id',$id)->first();

            if(!empty($emp_exist))
            {
                $sales_dept = Department::where('id', $emp_exist->department_id)->whereRaw('LOWER(name) LIKE ?', ['%sales%'])->first();
                if ($sales_dept)
                {
                    // $request->validate([
                    //     'sales_target_id' => 'required',
                    //     'incentive'       => 'required|numeric|gt:0',
                    // ]);
                }

                $emp_exist->update($input);
            }
            else
            {
                Employee::create($input);
            }
        }
        return response()->json([
            'success' => 'true',
            'redirect_url' => route('employees.index')
        ]);
    }

    public function delete($id)
    {
        $emp = Employee::find($id);
        $emp->delete();

        $user = User::where('id',$emp['user_id'])->first();
        if(!empty($user))
        {
            $user->update(['delete_status'=>1]);
        }

        return response()->json([
            'success' => 'Employee has been deleted successfully.'
        ], 200);

        return redirect()->route('employees.index');
    }

    public function getDesignations($department_id)
    {
        $designations = Designation::where('department_id', $department_id)->get();
        return response()->json($designations);
    }


    public function map_index(Request $request,$user_id)
    {
        $data['user_id'] = $user_id;
        return view('map.index',$data);
    }

    public function getLocations(Request $request, $user_id)
    {
        $start_date = $request->start_date;
        $end_date   = $request->end_date;

        if (!$start_date || !$end_date) {
            return response()->json([
                'status' => false,
                'message' => 'Start and End date required'
            ]);
        }

        $locations = LocationHistory::where('user_id', $user_id)
            ->whereDate('date_time', '>=', $start_date)
            ->whereDate('date_time', '<=', $end_date)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('date_time', 'ASC')
            ->get([
                'latitude',
                'longitude',
                'date_time'
            ])
            ->filter(function ($location) {
                return is_numeric($location->latitude)
                    && is_numeric($location->longitude)
                    && (float) $location->latitude >= -90
                    && (float) $location->latitude <= 90
                    && (float) $location->longitude >= -180
                    && (float) $location->longitude <= 180;
            })
            ->values();

        $routeGeometry = $this->buildRoadRouteGeometry($locations);

        return response()->json([
            'status' => true,
            'locations' => $locations,
            'route_geometry' => $routeGeometry,
            'route_mode' => $routeGeometry ? 'road' : 'direct',
        ]);
    }

    private function buildRoadRouteGeometry($locations): ?array
    {
        $apiKey = config('services.openrouteservice.key');

        if (!$apiKey || $locations->count() < 2) {
            return null;
        }

        $maxWaypoints = max(2, (int) config('services.openrouteservice.max_waypoints', 40));
        $profile = config('services.openrouteservice.profile', 'driving-car');
        $baseUrl = rtrim(config('services.openrouteservice.base_url', 'https://api.openrouteservice.org'), '/');

        $coordinates = $this->reduceRouteWaypoints(
            $locations->map(function ($location) {
                return [
                    (float) $location->longitude,
                    (float) $location->latitude,
                ];
            })->all(),
            $maxWaypoints
        );

        if (count($coordinates) < 2) {
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => $apiKey,
            'Accept' => 'application/json, application/geo+json',
            'Content-Type' => 'application/json',
        ])->timeout(20)->post("{$baseUrl}/v2/directions/{$profile}/geojson", [
            'coordinates' => $coordinates,
        ]);

        if (!$response->successful()) {
            return null;
        }

        $geometry = data_get($response->json(), 'features.0.geometry.coordinates', []);

        if (!is_array($geometry) || count($geometry) < 2) {
            return null;
        }

        return collect($geometry)
            ->filter(function ($point) {
                return is_array($point)
                    && count($point) >= 2
                    && is_numeric($point[0])
                    && is_numeric($point[1]);
            })
            ->map(function ($point) {
                return [(float) $point[1], (float) $point[0]];
            })
            ->values()
            ->all();
    }

    private function reduceRouteWaypoints(array $coordinates, int $maxWaypoints): array
    {
        $count = count($coordinates);

        if ($count <= $maxWaypoints) {
            return $coordinates;
        }

        $reduced = [];

        for ($i = 0; $i < $maxWaypoints; $i++) {
            $index = (int) round(($i * ($count - 1)) / ($maxWaypoints - 1));
            $reduced[] = $coordinates[$index];
        }

        return array_values(array_map('unserialize', array_unique(array_map('serialize', $reduced))));
    }

}
