<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use App\Models\Country;
use App\Models\Address;
use App\Support\Tenancy\TenancyManager;
use App\Support\Tenancy\TenantUsageService;
use App\Services\ActivityLogger;

class UserController extends Controller
{
    private function enforceUniqueUserContact(string $email, string $phone, ?User $currentUser = null): void
    {
        $errors = [];

        if ($currentUser) {
            $originalEmail = (string) $currentUser->getOriginal('email');
            $originalPhone = (string) $currentUser->getOriginal('phone');

            if ($email === $originalEmail) {
                $email = '';
            }

            if ($phone === $originalPhone) {
                $phone = '';
            }
        }

        if ($email === '' && $phone === '') {
            return;
        }

        foreach ($this->userContactUniqueConnections() as $connection) {
            if (!Schema::connection($connection)->hasTable('users')) {
                continue;
            }

            if ($email !== '' && $this->userContactExists($connection, 'email', $email, $currentUser)) {
                $errors['email'] = ['This email is already registered.'];
            }

            if ($phone !== '' && Schema::connection($connection)->hasColumn('users', 'phone')
                && $this->userContactExists($connection, 'phone', $phone, $currentUser)) {
                $errors['phone'] = ['This contact no. is already registered.'];
            }
        }

        if (!empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    private function userContactUniqueConnections(): array
    {
        return [config('database.default', 'mysql')];
    }

    private function userContactExists(string $connection, string $column, string $value, ?User $currentUser = null): bool
    {
        $query = DB::connection($connection)
            ->table('users')
            ->where($column, $value);

        if ($currentUser) {
            $query->where(function ($query) use ($connection, $currentUser) {
                $query->where('id', '!=', (int) $currentUser->id);

            });
        }

        return $query->exists();
    }

    private function filterUserPayloadForConnection(array $payload, string $connection): array
    {
        $columns = array_flip(Schema::connection($connection)->getColumnListing('users'));

        return array_intersect_key($payload, $columns);
    }

    private function createTenantUserWithLandlordId(array $payload, Role $role): User
    {
        $email = (string) ($payload['email'] ?? '');
        $tenantId = (int) ($payload['tenant_id'] ?? 0);

        if ($tenantId <= 0) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'tenant_id' => ['Tenant is not assigned to this user.'],
            ]);
        }

        $landlordConnection = DB::connection();
        $tenantConnection = DB::connection();

        $existingLandlordUser = $landlordConnection
            ->table('users')
            ->where(function ($query) use ($email, $payload) {
                $query->where('email', $email);

                if (!empty($payload['phone']) && Schema::hasColumn('users', 'phone')) {
                    $query->orWhere('phone', (string) $payload['phone']);
                }
            })
            ->first();

        if ($existingLandlordUser) {
            $field = (string) ($existingLandlordUser->email ?? '') === $email ? 'email' : 'phone';

            throw \Illuminate\Validation\ValidationException::withMessages([
                $field => [$field === 'email'
                    ? 'This email is already registered in the master database.'
                    : 'This contact no. is already registered in the master database.'
                ],
            ]);
        }

        $landlordConnection->beginTransaction();
        $tenantConnection->beginTransaction();

        try {
            $now = now();
            $payload['created_at'] = $payload['created_at'] ?? $now;
            $payload['updated_at'] = $payload['updated_at'] ?? $now;

            $landlordPayload = $this->filterUserPayloadForConnection($payload, 'landlord');
            unset($landlordPayload['id']);

            $landlordUserId = (int) $landlordConnection
                ->table('users')
                ->insertGetId($landlordPayload);

            $existingTenantUserId = $tenantConnection
                ->table('users')
                ->where('id', $landlordUserId)
                ->value('id');

            if ($existingTenantUserId) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => ['Tenant user id is already used. Please resync tenant users before creating this user.'],
                ]);
            }

            $tenantPayload = $this->filterUserPayloadForConnection($payload, 'tenant');
            $tenantPayload['id'] = $landlordUserId;
            $tenantPayload['tenant_id'] = $tenantId;

            $tenantConnection
                ->table('users')
                ->insert($tenantPayload);

            $tenantUser = User::query()->findOrFail($landlordUserId);
            $tenantUser->assignRole($role);

            $tenantConnection->commit();
            $landlordConnection->commit();

            return $tenantUser;
        } catch (\Throwable $e) {
            $tenantConnection->rollBack();
            $landlordConnection->rollBack();

            throw $e;
        }
    }

    private function syncTenantUserToLandlord(User $tenantUser, ?string $previousEmail = null): void
    {
        if (!app()->bound('currentTenant')) {
            return;
        }

        $tenantId = (int) ($tenantUser->tenant_id ?? 0);
        if ($tenantId <= 0) {
            return;
        }

        $landlordConnection = DB::connection();

        $landlordUser = $landlordConnection
            ->table('users')
            ->where('id', (int) $tenantUser->id)
            ->first();

        if (!$landlordUser) {
            $landlordUser = $landlordConnection
                ->table('users')
                ->where('email', $previousEmail ?: $tenantUser->email)
                ->where('tenant_id', $tenantId)
                ->first();
        }

        if (!$landlordUser) {
            $landlordUser = $landlordConnection
                ->table('users')
                ->where('email', $tenantUser->email)
                ->first();
        }

        if ($landlordUser && $landlordUser->type === 'super admin') {
            return;
        }

        $payload = [
            'name' => $tenantUser->name,
            'email' => $tenantUser->email,
            'phone' => $tenantUser->phone,
            'password' => $tenantUser->password,
            'type' => $tenantUser->type,
            'tenant_id' => $tenantId,
            'created_by' => (int) ($tenantUser->created_by ?? 0),
            'is_active' => (int) ($tenantUser->is_active ?? 1),
            'delete_status' => (int) ($tenantUser->delete_status ?? 1),
            'lang' => $tenantUser->lang,
            'email_verified_at' => $tenantUser->email_verified_at,
        ];

        if (Schema::hasColumn('users', 'is_enable_login')) {
            $payload['is_enable_login'] = (int) ($tenantUser->is_enable_login ?? 1);
        }

        if ($landlordUser) {
            $landlordConnection
                ->table('users')
                ->where('id', $landlordUser->id)
                ->update($payload);
            return;
        }

        $landlordConnection->table('users')->insert($payload);
    }

    private function removeTenantUserFromLandlord(User $tenantUser): void
    {
        if (!app()->bound('currentTenant')) {
            return;
        }

        DB::connection()
            ->table('users')
            ->where('email', $tenantUser->email)
            ->where('tenant_id', (int) ($tenantUser->tenant_id ?? 0))
            ->delete();
    }

    private function writeUserActivity(string $action, string $eventKey, User $user, string $description, array $properties = []): void
    {
        ActivityLogger::writeFor('users', $action, $user, null, [
            'event_key' => $eventKey,
            'description' => $description,
            'properties' => $properties,
        ]);
    }

    private function writeSettingsActivity(string $eventKey, string $description, int $subjectId, array $properties = []): void
    {
        ActivityLogger::writeFor('settings', 'update', 'settings', $subjectId, [
            'event_key' => $eventKey,
            'description' => $description,
            'properties' => $properties,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
         if ($request->ajax())
        {
            try
            {
                $user = \Auth::user();
                 if (\Auth::user()->type == 'company') {
                    $query = User::Isdeleted()->where('created_by', '=', $user->creatorId())->select('id','name','type','avatar','email','phone');
                } else {
                    $query = User::Isdeleted()->where('created_by', '=', $user->creatorId())->select('id','name','type','avatar','email','phone');
                }

                $data = $query->orderBy('id', 'desc')->get();

                return DataTables::of($data)
                ->addIndexColumn()
                 ->addColumn('name_detail', function ($row) {
                        $default_img = \App\Models\Utility::defaultImage();
                        $avatar = $row->avatar ? $row->avatar : $default_img;
                            return '
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-light rounded p-1">
                                            <img src="' . $avatar . '"
                                                alt="avatar"
                                                class="img-fluid d-block"
                                                height="70" width="70">
                                        </div>
                                    </div>

                                    <div class="flex-grow-1">
                                        <h5 class="fs-14 mb-1">
                                            <a href="#" class="text-body">' . $row->name . '</a>
                                        </h5>

                                        <p class="text-muted mb-0">
                                            Mail : <span class="fw-medium">' . $row->email . '</span>
                                        </p>

                                        <p class="text-muted mb-0">
                                            Contact No : <span class="fw-medium">' . $row->phone . '</span>
                                        </p>
                                    </div>
                                </div>
                            ';

                    })
                    ->addColumn('role_nm', function ($row) {
                        return ucfirst($row->type) ?? '';
                    })
                    ->addColumn('action', function($row) {
                        $editUrl = route('users.edit', [$row->id]);


                        return '<div class="dropdown d-inline-block">
                                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ri-more-fill align-middle"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a href="' . $editUrl . '" class="dropdown-item edit-item-btn">
                                                <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                            </a>
                                        </li>

                                    </ul>
                                </div>';
                    })
                    ->rawColumns(['action','name_detail','role_nm'])
                    ->setRowClass('main-row')
                    ->make(true);
            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        }
        return view('user.index');

        //User::defaultEmail();

        // $user = \Auth::user();
        // if (\Auth::user()->can('manage user')) {
        //     if (\Auth::user()->type == 'super admin') {
        //         $users = User::Isdeleted()->where('created_by', '=', $user->creatorId())->where('type', '=', 'company')->get();
        //     } else {
        //         $users = User::Isdeleted()->where('created_by', '=', $user->creatorId())->where('type', '!=', 'client')->get();
        //     }

        //     return view('user.index')->with('users', $users);
        // } else {
        //     return redirect()->back();
        // }


    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $req_type = $request->input('type');
        $user = \Auth::user();
        $roleQuery = Role::query()->whereNotIn('name', ['client', 'super admin','company']);
        if ($user->type === 'company') {
            $roleQuery->where('name', '!=', 'company');
        }
        // if (!app()->bound('currentTenant')) {
        //     $roleQuery->where('created_by', '=', $user->creatorId());
        // }
        $roles = $roleQuery->orderBy('name')->get()->pluck('name', 'id');
        $user_type_list = User::$predefineUserTypeList;
        if (\Auth::user()->can('create user')) {
            return view('user.create', compact('roles','req_type','user_type_list'));
        } else {
            return redirect()->back();
        }

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (\Auth::user()->can('create user')) {

            // $usage = app(TenantUsageService::class);
            // if (!$usage->canCreateUser()) {
            //     return response()->json([
            //         'error' => 'User limit reached for your current plan.',
            //     ], 403);
            // }

            $objUser = \Auth::user()->creatorId();

                $emailRule = 'required|email|unique:users,email';

                $request->validate([
                    'name' => 'required',
                    'email' => $emailRule,
                    'role' => 'required',
                    'user_type' => ['required', \Illuminate\Validation\Rule::in(array_keys(User::$predefineUserTypeList))],
                    'password' => 'required|min:6',
                    'phone' => 'required|digits:10',
                ]);

                $this->enforceUniqueUserContact(
                    (string) $request->input('email'),
                    (string) $request->input('phone')
                );



                $objUser = User::find($objUser);
                $user = User::find(\Auth::user()->created_by);
                $userpassword = $request->input('password');

                $role_r = Role::findById($request->role);
                if (\Auth::user()->type === 'company' && strtolower((string) $role_r->name) === 'company') {
                    return response()->json([
                        'errors' => [
                            'role' => ['Company role is not allowed.'],
                        ],
                    ], 422);
                }
                $request['password'] = !empty($userpassword)?\Hash::make($userpassword) : null;
                $request['type'] = $request->input('user_type');
                $request['lang'] = 'en';
                $request['created_by'] = \Auth::user()->creatorId();
                // $request['tenant_id'] = \Auth::user()->tenant_id;
                if (Schema::hasColumn('users', 'is_enable_login')) {
                    $request['is_enable_login'] = 1;
                }
                if (Schema::hasColumn('users', 'is_active')) {
                    $request['is_active'] = 1;
                }
                if (Schema::hasColumn('users', 'delete_status')) {
                    $request['delete_status'] = 1;
                }
                $request['email_verified_at'] = date('Y-m-d H:i:s');

                 if ($request->hasFile('avatar_final'))
                {

                    $filenameWithExt = $request->file('avatar_final')->getClientOriginalName();

                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('avatar_final')->getClientOriginalExtension();
                    $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                    $dir = 'uploads/avatar/';
                    $image_path = $dir . $fileNameToStore;

                    $url = '';
                    $path = Utility::upload_file($request,'avatar_final',$fileNameToStore,$dir,[]);

                    if ($path['flag'] == 1) {

                        $url = $path['url'];
                    } else {
                        return redirect()->back()->with('error', __($path['msg']));
                    }

                    $request['avatar']=$fileNameToStore??null;

                }

                // if (app()->bound('currentTenant')) {
                //     $user = $this->createTenantUserWithLandlordId($request->all(), $role_r);
                // } else {
                    $user = User::create($request->all());
                    $user->assignRole($role_r);
                    // $this->syncTenantUserToLandlord($user);
                // }

                $this->writeUserActivity(
                    'create',
                    'user.created',
                    $user,
                    'User created.',
                    [
                        'role_id' => $role_r->id,
                        'role_name' => $role_r->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'type' => $user->type,
                    ]
                );

                if ($request['type'] != 'client') {

                    \App\Models\Utility::employeeDetails($user->id, \Auth::user()->creatorId());

                }

            // return redirect()->route('users.index')->with('success', __('User successfully created.'));
             return response()->json([
            'success' => 'User successfully created.',
            'redirect_url' => route('users.index'),
        ]);

        } else {
            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $authUser = \Auth::user();
        $roleQuery = Role::query()->whereNotIn('name', ['client', 'super admin','company']);
        if ($authUser->type === 'company') {
            $roleQuery->where('name', '!=', 'company');
        }
        // if (!app()->bound('currentTenant')) {
        //     $roleQuery->where('created_by', '=', $authUser->creatorId());
        // }
        $roles = $roleQuery->orderBy('name')->get()->pluck('name', 'id');
        $user_type_list = User::$predefineUserTypeList;
        // where('created_by', '=', $user->creatorId())->where('name', '!=', 'client')
        // if (\Auth::user()->can('edit user')) {
            $user = User::findOrFail($id);
            $activityTimeline = ActivityLogger::activityForRecord($user, null, 12, 'user_activities_page');

            return view('user.edit', compact('user', 'roles', 'user_type_list', 'activityTimeline'));
        // } else {
        //     return redirect()->back();
        // }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // if (\Auth::user()->can('edit user')) {

            $user = User::findOrFail($id);
            $before = [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'type' => $user->type,
            ];
            $beforeStatus = [
                'is_enable_login' => $user->is_enable_login ?? null,
                'is_active' => $user->is_active ?? null,
                'delete_status' => $user->delete_status ?? null,
            ];
            $beforeRoles = $user->roles()->pluck('name')->sort()->values()->all();
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required|max:120',
                    'email' => 'required|email',
                    'phone' => 'required|digits:10',
                    'role' => 'required',
                    'user_type' => ['required', \Illuminate\Validation\Rule::in(array_keys(User::$predefineUserTypeList))],
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            try {
                $this->enforceUniqueUserContact(
                    (string) $request->input('email'),
                    (string) $request->input('phone'),
                    $user
                );
            } catch (\Illuminate\Validation\ValidationException $e) {
                $messages = collect($e->errors())->flatten();

                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors($e->errors())
                    ->with('error', (string) $messages->first());
            }

            $role = Role::findById($request->role);
            // if (\Auth::user()->type === 'company' && strtolower((string) $role->name) === 'company') {
            //     return redirect()->back()->with('error', __('Company role is not allowed.'));
            // }
            $input = $request->all();

            $input['type'] = $request->input('user_type');

             if ($request->hasFile('avatar') && $request->avatar != null)
            {
                $filenameWithExt = $request->file('avatar')->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('avatar')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $dir = 'uploads/avatar/';

                $oldAvatar = $user->getRawOriginal('avatar');
                $oldAvatarPath = storage_path($dir . $oldAvatar);
                if (!empty($oldAvatar) && File::exists($oldAvatarPath)) {
                    File::delete($oldAvatarPath);
                }

                $path = Utility::upload_file($request, 'avatar', $fileNameToStore, $dir, []);
                if ($path['flag'] == 1) {
                    $input['avatar'] = $fileNameToStore;
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }
            }

            if ($request->has('password') && !empty($request->password)) {

                $userpassword = $request->input('password');

                $input['password'] = \Hash::make($userpassword);
            } else {

                $input['password'] = $user->password;
            }


            $previousEmail = (string) $user->getOriginal('email');

            $user->fill($input)->save();
            // $this->syncTenantUserToLandlord($user, $previousEmail);

            Utility::employeeDetailsUpdate($user->id, \Auth::user()->creatorId());

            $roles[] = $request->role;
            $user->roles()->sync($roles);

            $after = [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'type' => $user->type,
            ];
            $afterStatus = [
                'is_enable_login' => $user->is_enable_login ?? null,
                'is_active' => $user->is_active ?? null,
                'delete_status' => $user->delete_status ?? null,
            ];
            $afterRoles = $user->roles()->pluck('name')->sort()->values()->all();

            $userChanges = ActivityLogger::diff($before, $after);
            if (!empty($userChanges)) {
                $this->writeUserActivity(
                    'update',
                    'user.updated',
                    $user,
                    'User details updated.',
                    ['changes' => $userChanges]
                );
            }

            $statusChanges = ActivityLogger::diff($beforeStatus, $afterStatus);
            if (!empty($statusChanges)) {
                $this->writeUserActivity(
                    'change_status',
                    'user.status_changed',
                    $user,
                    'User status updated.',
                    ['changes' => $statusChanges]
                );
            }

            if ($beforeRoles !== $afterRoles) {
                $this->writeUserActivity(
                    'update',
                    'user.role_changed',
                    $user,
                    'User role updated.',
                    [
                        'before' => ['roles' => $beforeRoles],
                        'after' => ['roles' => $afterRoles],
                    ]
                );
            }

            return redirect()->route('users.index')->with(
                'success', 'User successfully updated.'
            );

        // } else {
        //     return redirect()->back();
        // }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (\Auth::user()->can('delete user')) {
            if ($id == 2) {
                return redirect()->back()->with('error', __('You can not delete By default Company'));
            }

            $user = User::find($id);
            if ($user) {
                // $tenantUserSnapshot = clone $user;

                if (\Auth::user()->type == 'company') {

                    $employee = Employee::where(['user_id' => $user->id])->delete();
                    if ($employee) {
                        $delete_user = User::where(['id' => $user->id])->delete();
                        // $this->removeTenantUserFromLandlord($tenantUserSnapshot);

                        if ($delete_user) {
                            return redirect()->route('users.index')->with('success', __('User successfully deleted .'));
                        } else {
                            return redirect()->back()->with('error', __('Something is wrong.'));
                        }
                    } else {
                        return redirect()->back()->with('error', __('Something is wrong.'));
                    }
                }
                return redirect()->route('users.index')->with('success', __('User successfully deleted .'));

            } else {
                return redirect()->back()->with('error', __('Something is wrong.'));
            }
        } else {
            return redirect()->back();
        }

    }

    public function company_profile(Request $request, $id)
    {
        $settingsConnection = config('database.default', 'mysql');
        $creatorId = (int) \Auth::user()->creatorId();
        $data['setting_rcd'] = DB::connection($settingsConnection)
            ->table('settings')
            ->where('created_by', $creatorId)
            ->pluck('value', 'name')
            ->toArray();
        $data['country_list'] = Country::isActive()->pluck('name', 'id');

        $address_data = [];

        $company_address_id = $data['setting_rcd']['company_address_id'] ?? null;
        $billing_address_id = $data['setting_rcd']['billing_address_id'] ?? null;

        $address_data[] = $company_address_id
            ? Address::find($company_address_id)
            : (object)[
                'country' => '',
                'state' => '',
                'city' => '',
                'zipcode' => '',
                'address_line_1' => '',
                'address_line_2' => '',
            ];

        $address_data[] = $billing_address_id
            ? Address::find($billing_address_id)
            : (object)[
                'country' => '',
                'state' => '',
                'city' => '',
                'zipcode' => '',
                'address_line_1' => '',
                'address_line_2' => '',
            ];

        $data['address_list'] = $address_data;
        $data['settingsActivityTimeline'] = ActivityLogger::activityForModule('settings', 10, [
            'event_key' => 'settings.company_updated',
            'subject' => 'settings',
            'subject_id' => $creatorId,
        ], 'company_settings_activities_page');

        return view('user.setting', $data);
    }

    public function company_profile_update(Request $request, string $id)
    {
        $settingsConnection = config('database.default', 'mysql');
        $regionTablePrefix = '';

        $creatorId = (int) \Auth::user()->creatorId();
        $existingSettings = DB::connection($settingsConnection)->table('settings')
            ->where('created_by', $creatorId)
            ->whereIn('name', [
                'website_name',
                'website_url',
                'website_short_name',
                'email',
                'phone',
                'gst_no',
                'pan_no',
                'website_logo',
                'company_address_id',
                'billing_address_id',
                'is_allowed_discount',
                'india_mart_key',
                'facebook_spreadsheet_id',
            ])
            ->pluck('value', 'name')
            ->toArray();
        $upsertSetting = static function (string $name, $value) use ($settingsConnection, $creatorId): void {
            DB::connection($settingsConnection)->table('settings')->updateOrInsert(
                ['name' => $name, 'created_by' => $creatorId],
                ['value' => $value]
            );
        };

        $rules = [
            'website_name' => 'required',
            'website_url' => 'required',
            'website_short_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required|digits:10',
            'pan_no' => 'required',
            'gst_no' => [
                'required',
                'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'
            ],

            // Company address (index 0) — ALWAYS REQUIRED
            'india_mart_key' => 'nullable|string|max:255',
            'facebook_spreadsheet_id' => 'nullable|string|max:255',
            'country.0' => 'required|exists:' . $regionTablePrefix . 'countries,id',
            'state.0'   => 'required|exists:' . $regionTablePrefix . 'states,id',
            'city.0'    => 'required|exists:' . $regionTablePrefix . 'cities,id',
            'zipcode.0' => 'required|string|max:10',
            'address_line_1.0' => 'required',
        ];

        // Billing address ONLY if checkbox NOT checked
        if (!$request->has('is_same_adr')) {
            $rules += [
                'country.1' => 'required|exists:' . $regionTablePrefix . 'countries,id',
                'state.1'   => 'required|exists:' . $regionTablePrefix . 'states,id',
                'city.1'    => 'required|exists:' . $regionTablePrefix . 'cities,id',
                'zipcode.1' => 'required|string|max:10',
                'address_line_1.1' => 'required',
            ];
        }

        $request->validate($rules);

        if (!$request->has('is_same_adr')) {
            $rules += [
                'country.1' => 'required|exists:' . $regionTablePrefix . 'countries,id',
                'state.1'   => 'required|exists:' . $regionTablePrefix . 'states,id',
                'city.1'    => 'required|exists:' . $regionTablePrefix . 'cities,id',
                'zipcode.1' => 'required|string|max:10',
                'address_line_1.1' => 'required',
            ];
        }

        if ($request->hasFile('image_final') && $request->file('image_final')->isValid()) {

            $file = $request->file('image_final');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            $destinationPath = public_path('website_logo');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $existingImage = DB::connection($settingsConnection)->table('settings')
                ->where('name', 'website_logo')
                ->where('created_by', $creatorId)
                ->value('value');
            if (!empty($existingImage)) {
                $oldFile = $destinationPath . '/' . $existingImage;
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            $file->move($destinationPath, $fileNameToStore);
            $upsertSetting('website_logo', $fileNameToStore);
        }


        $upsertSetting('website_name', $request->website_name);
        $upsertSetting('website_url', $request->website_url);
        $upsertSetting('website_short_name', $request->website_short_name);
        $upsertSetting('email', $request->email);
        $upsertSetting('phone', $request->phone);
        $upsertSetting('gst_no', $request->gst_no);
        $upsertSetting('pan_no', $request->pan_no);
        $upsertSetting('is_allowed_discount', $request->has('is_allowed_discount') ? '1' : '0');
        $upsertSetting('india_mart_key', trim((string) $request->india_mart_key));
        $upsertSetting('facebook_spreadsheet_id', trim((string) $request->facebook_spreadsheet_id));

        $address_ids = [];

        // -------- COMPANY ADDRESS (INDEX 0) --------
        $companyData = [
            'country'        => $request->country[0],
            'state'          => $request->state[0],
            'city'           => $request->city[0],
            'zipcode'        => $request->zipcode[0],
            'address_line_1' => $request->address_line_1[0],
            'address_line_2' => $request->address_line_2[0] ?? null,
        ];

        if (!empty($request->address_id[0])) {
            Address::where('id', $request->address_id[0])->update($companyData);
            $companyAddressId = $request->address_id[0];
        } else {
            $companyAddressId = Address::create($companyData)->id;
        }

        $address_ids[0] = $companyAddressId;

        // -------- BILLING ADDRESS (INDEX 1) --------
        if ($request->has('is_same_adr')) {

            // SAME AS COMPANY
            if (!empty($request->address_id[1])) {
                Address::where('id', $request->address_id[1])->update($companyData);
                $billingAddressId = $request->address_id[1];
            } else {
                $billingAddressId = Address::create($companyData)->id;
            }

        } else {

            // SEPARATE BILLING ADDRESS
            $billingData = [
                'country'        => $request->country[1],
                'state'          => $request->state[1],
                'city'           => $request->city[1],
                'zipcode'        => $request->zipcode[1],
                'address_line_1' => $request->address_line_1[1],
                'address_line_2' => $request->address_line_2[1] ?? null,
            ];

            if (!empty($request->address_id[1])) {
                Address::where('id', $request->address_id[1])->update($billingData);
                $billingAddressId = $request->address_id[1];
            } else {
                $billingAddressId = Address::create($billingData)->id;
            }
        }

        $address_ids[1] = $billingAddressId;

        $upsertSetting('company_address_id', $address_ids[0]);
        $upsertSetting('billing_address_id', $address_ids[1]);

        $updatedSettings = [
            'website_name' => $request->website_name,
            'website_url' => $request->website_url,
            'website_short_name' => $request->website_short_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'gst_no' => $request->gst_no,
            'pan_no' => $request->pan_no,
            'is_allowed_discount' => $request->has('is_allowed_discount') ? '1' : '0',
            'india_mart_key' => trim((string) $request->india_mart_key),
            'facebook_spreadsheet_id' => trim((string) $request->facebook_spreadsheet_id),
            'website_logo' => DB::connection($settingsConnection)->table('settings')
                ->where('name', 'website_logo')
                ->where('created_by', $creatorId)
                ->value('value'),
            'company_address_id' => (string) $address_ids[0],
            'billing_address_id' => (string) $address_ids[1],
        ];
        $settingsChanges = ActivityLogger::diff($existingSettings, $updatedSettings);
        if (!empty($settingsChanges)) {
            $this->writeSettingsActivity(
                'settings.company_updated',
                'Company settings updated.',
                $creatorId,
                ['changes' => $settingsChanges]
            );
        }

        return response()->json([
            'success' => 'Settings has been updated successfully.',
            'redirect_url' => route('settings.edit', \Auth::user()->id),
        ]);
    }

    public function user_profile(Request $request, $id)
    {
        $data['user'] = User::where('id', $id)->first();
        if (!$data['user']) {
            $data['user'] = \Auth::user();
        }

        if (!$data['user']) {
            abort(404, 'User not found.');
        }

        return view('user.user_profile', $data);
    }

    public function user_profile_update(Request $request, $id)
    {

        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required|digits:10',
        ]);


        $user = User::find($id);
        if (!$user) {
            if ($request->ajax()) {
                return response()->json(['error' => 'User not found.'], 404);
            }
            return redirect()->back()->with('error', __('User not found.'));
        }

        $this->enforceUniqueUserContact(
            (string) $request->input('email'),
            (string) $request->input('phone'),
            $user
        );

        $input = $request->all();

        //image
        if ($request->hasFile('image_final'))
        {

            $filenameWithExt = $request->file('image_final')->getClientOriginalName();

            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('image_final')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            $dir = 'uploads/avatar/';
            $image_path = $dir . $fileNameToStore;
            if (\File::exists($image_path)) {
                \File::delete($image_path);
            }
            $url = '';
            $path = Utility::upload_file($request, 'image_final', $fileNameToStore, $dir, []);

            if ($path['flag'] == 1) {
                $url = $path['url'];
            } else {
                return redirect()->back()->with('error', __($path['msg']));
            }

            $input['avatar']  = !empty($request->image_final) ? $fileNameToStore : '';
        }

        //password
        if ($request->password != null) {
            $input['password'] = \Hash::make($request->password);
        } else {
            $input['password'] = $user->password;
        }

        $previousEmail = (string) $user->getOriginal('email');

        $user->update($input);
        // $this->syncTenantUserToLandlord($user, $previousEmail);

        //emp detail update
        $emp_id = Employee::where('user_id',$id)->first();
        $emp_data['name'] = $request->name;
        $emp_data['email'] = $request->email;
        $emp_data['phone'] = $request->phone;
        $emp_data['password'] = $user->password;
        if(isset($emp_id))
        {
            $emp_id->update($emp_data);
        }

        return response()->json([
            'success' => 'Profile has been updated successfully.',
            'redirect_url' => route('user_profile.edit', \Auth::user()->id),
        ]);
    }
}
