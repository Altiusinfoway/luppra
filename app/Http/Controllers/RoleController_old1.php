<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\Permission;
use App\Models\Role;
use App\Support\Tenancy\TenancyManager;
use App\Services\ActivityLogger;
use Auth;

class RoleController extends Controller
{
    private function writeRoleActivity(string $action, Role $role, string $description, array $properties = []): void
    {
        ActivityLogger::writeFor('users', $action, $role, null, [
            'event_key' => 'permission.updated',
            'description' => $description,
            'properties' => $properties,
        ]);
    }

    private function ensureTenantContext(Request $request): void
    {
        if (!config('tenancy.enabled', false) || app()->bound('currentTenant')) {
            return;
        }

        $user = $request->user();
        if (!$user || $user->type === 'super admin') {
            return;
        }

        $tenantId = (int) $request->session()->get('tenant_id', 0);
        if ($tenantId <= 0) {
            $tenantId = (int) $request->session()->get('login_tenant_id', 0);
        }

        if ($tenantId <= 0) {
            abort(403, 'Tenant context is missing for this session.');
        }

        $tenant = Tenant::query()->where('id', $tenantId)->where('is_active', true)->first();
        if (!$tenant) {
            abort(403, 'Tenant is inactive or unavailable.');
        }

        app(TenancyManager::class)->initialize($tenant);
        app()->instance('currentTenant', $tenant);
    }

    private function resolveRoleOrFail($id): Role
    {
        return $this->roleQuery()->findOrFail($id);
    }

    private function roleQuery()
    {
        $query = Role::query();

        if (\Auth::check() && \Auth::user()->type === 'company') {
            $query->where('name', '!=', 'company');
        }

        if (app()->bound('currentTenant')) {
            return $query;
        }

        return $query->where('created_by', '=', \Auth::user()->creatorId());
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->ensureTenantContext(request());

        if(\Auth::user()->can('manage role'))
        {
            $roles = $this->roleQuery()->get();
            $permissionActivityTimeline = ActivityLogger::activityForModule('users', 10, [
                'event_key' => 'permission.updated',
            ], 'permission_activities_page');

            return view('role.index')->with(['roles' => $roles, 'permissionActivityTimeline' => $permissionActivityTimeline]);
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->ensureTenantContext(request());

        if(\Auth::user()->can('create role'))
        {
            $user = \Auth::user();
            if($user->type == 'company')
            {
                $permissions = Permission::all()->pluck('name', 'id')->toArray();
            }
            else
            {
                $permissions = new Collection();
                foreach($user->roles as $role)
                {
                    $permissions = $permissions->merge($role->permissions);
                }
                $permissions = $permissions->pluck('name', 'id')->toArray();
            }

            return view('role.create', ['permissions' => $permissions]);
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->ensureTenantContext($request);

        if(\Auth::user()->can('create role'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => app()->bound('currentTenant')
                                       ? 'required|max:100|unique:tenant.roles,name'
                                       : 'required|max:100|unique:tenant.roles,name,NULL,id,created_by,' . \Auth::user()->creatorId(),
                                   'permissions' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $name             = $request['name'];
            $role             = new Role();
            $role->name       = $name;
            $role->created_by = app()->bound('currentTenant') ? (int) \Auth::id() : \Auth::user()->creatorId();
            $permissions      = $request['permissions'];
            $role->save();

            foreach($permissions as $permission)
            {
                $p = Permission::where('id', '=', $permission)->firstOrFail();
                $role->givePermissionTo($p);
            }

            $this->writeRoleActivity(
                'create',
                $role,
                'Role created with permissions.',
                [
                    'role_name' => $role->name,
                    'permissions' => $role->permissions()->pluck('name')->sort()->values()->all(),
                ]
            );

            return redirect()->route('roles.index')->with('success' , 'Role successfully created.', 'Role ' . $role->name . ' added!');
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
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
    public function edit(Request $request,$id)
    {
        $this->ensureTenantContext($request);

        if(\Auth::user()->can('edit role'))
        {
            $role = $this->resolveRoleOrFail($id);
            $activityTimeline = ActivityLogger::activityForRecord($role, null, 12, 'role_activities_page');
            $user = \Auth::user();
            if($user->type == 'company')
            {
                $permissions = Permission::all()->pluck('name', 'id')->toArray();
            }
            else
            {
                $permissions = new Collection();
                foreach($user->roles as $role1)
                {
                    $permissions = $permissions->merge($role1->permissions);
                }
                $permissions = $permissions->pluck('name', 'id')->toArray();
            }

            return view('role.edit', compact('role', 'permissions', 'activityTimeline'));
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->ensureTenantContext($request);

        if(\Auth::user()->can('edit role'))
        {
            $role = $this->resolveRoleOrFail($id);
            $before = [
                'name' => $role->name,
                'permissions' => $role->permissions()->pluck('name')->sort()->values()->all(),
            ];
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => app()->bound('currentTenant')
                                       ? 'required|max:100|unique:tenant.roles,name,' . $role['id']
                                       : 'required|max:100|unique:tenant.roles,name,' . $role['id'] . ',id,created_by,' . \Auth::user()->creatorId(),
                                   'permissions' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $input       = $request->except(['permissions']);
            $permissions = $request['permissions'];
            $role->fill($input)->save();

            $p_all = Permission::all();

            foreach($p_all as $p)
            {
                $role->revokePermissionTo($p);
            }

            foreach($permissions as $permission)
            {

                $p = Permission::where('id', '=', $permission)->firstOrFail();
                $role->givePermissionTo($p);
            }

            $after = [
                'name' => $role->name,
                'permissions' => $role->permissions()->pluck('name')->sort()->values()->all(),
            ];
            $changes = ActivityLogger::diff($before, $after);
            if (!empty($changes)) {
                $this->writeRoleActivity(
                    'update',
                    $role,
                    'Role permissions updated.',
                    ['changes' => $changes]
                );
            }

            return redirect()->route('roles.index')->with('success' , 'Role successfully updated.', 'Role ' . $role->name . ' updated!');
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->ensureTenantContext(request());

        if(\Auth::user()->can('delete role'))
        {
            $role = $this->resolveRoleOrFail($id);
            $role->delete();

             return response()->json([
                'success' => 'Role successfully deleted..'
            ], 200);

            // return redirect()->route('roles.index')->with('success', __('Role successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }
}
