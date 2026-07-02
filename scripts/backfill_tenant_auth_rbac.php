<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use App\Support\Tenancy\TenancyManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$landlordConnection = 'landlord';
$tenantConnection = 'tenant';
$now = now();

$stats = [
    'tenants_processed' => 0,
    'tenant_permission_rows_synced' => 0,
    'tenant_role_rows_synced' => 0,
    'tenant_role_permission_rows_synced' => 0,
    'landlord_users_created' => 0,
    'landlord_users_updated' => 0,
    'tenant_users_created' => 0,
    'tenant_users_updated' => 0,
    'tenant_role_links_created' => 0,
    'conflicts' => [],
    'errors' => [],
];

$landlordPermissions = DB::connection($landlordConnection)
    ->table('permissions')
    ->orderBy('id')
    ->get();

$landlordRoles = DB::connection($landlordConnection)
    ->table('roles')
    ->orderBy('id')
    ->get();

$landlordRolePermissionMap = DB::connection($landlordConnection)
    ->table('role_has_permissions')
    ->get()
    ->groupBy('role_id');

$tenantManager = app(TenancyManager::class);
$tenants = Tenant::query()->where('is_active', true)->orderBy('id')->get();

foreach ($tenants as $tenant) {
    $stats['tenants_processed']++;

    try {
        $tenantManager->initialize($tenant);
        app()->instance('currentTenant', $tenant);

        foreach ($landlordPermissions as $permission) {
            DB::connection($tenantConnection)->table('permissions')->updateOrInsert(
                ['id' => $permission->id],
                [
                    'name' => $permission->name,
                    'guard_name' => $permission->guard_name,
                    'created_at' => $permission->created_at ?? $now,
                    'updated_at' => $now,
                ]
            );
            $stats['tenant_permission_rows_synced']++;
        }

        foreach ($landlordRoles as $role) {
            DB::connection($tenantConnection)->table('roles')->updateOrInsert(
                ['id' => $role->id],
                [
                    'name' => $role->name,
                    'guard_name' => $role->guard_name,
                    'created_by' => $role->created_by ?? 0,
                    'created_at' => $role->created_at ?? $now,
                    'updated_at' => $now,
                ]
            );
            $stats['tenant_role_rows_synced']++;

            DB::connection($tenantConnection)->table('role_has_permissions')
                ->where('role_id', $role->id)
                ->delete();

            foreach (($landlordRolePermissionMap[$role->id] ?? collect()) as $pivot) {
                DB::connection($tenantConnection)->table('role_has_permissions')->updateOrInsert(
                    [
                        'permission_id' => $pivot->permission_id,
                        'role_id' => $role->id,
                    ],
                    []
                );
                $stats['tenant_role_permission_rows_synced']++;
            }
        }

        $tenantUsers = DB::connection($tenantConnection)
            ->table('users')
            ->orderBy('id')
            ->get();

        foreach ($tenantUsers as $tenantUser) {
            $landlordByEmail = DB::connection($landlordConnection)
                ->table('users')
                ->where('email', $tenantUser->email)
                ->first();

            if ($landlordByEmail && (int) ($landlordByEmail->tenant_id ?? 0) !== (int) $tenant->id) {
                $stats['conflicts'][] = [
                    'type' => 'landlord_email_conflict',
                    'tenant_id' => $tenant->id,
                    'tenant_slug' => $tenant->slug,
                    'email' => $tenantUser->email,
                    'existing_landlord_tenant_id' => (int) ($landlordByEmail->tenant_id ?? 0),
                ];
                continue;
            }

            $landlordPayload = [
                'name' => $tenantUser->name,
                'email' => $tenantUser->email,
                'phone' => $tenantUser->phone,
                'password' => $tenantUser->password,
                'type' => $tenantUser->type,
                'tenant_id' => (int) $tenant->id,
                'created_by' => (int) ($tenantUser->created_by ?? 0),
                'is_active' => (int) ($tenantUser->is_active ?? 1),
                'delete_status' => (int) ($tenantUser->delete_status ?? 1),
                'lang' => $tenantUser->lang,
                'email_verified_at' => $tenantUser->email_verified_at,
                'updated_at' => $now,
            ];

            if (Schema::connection($landlordConnection)->hasColumn('users', 'is_enable_login')) {
                $landlordPayload['is_enable_login'] = (int) ($tenantUser->is_enable_login ?? 1);
            }

            if ($landlordByEmail) {
                DB::connection($landlordConnection)
                    ->table('users')
                    ->where('id', $landlordByEmail->id)
                    ->update($landlordPayload);
                $stats['landlord_users_updated']++;
                $landlordUserId = (int) $landlordByEmail->id;
            } else {
                $landlordPayload['created_at'] = $tenantUser->created_at ?? $now;
                DB::connection($landlordConnection)->table('users')->insert($landlordPayload);
                $stats['landlord_users_created']++;
                $landlordUserId = (int) DB::connection($landlordConnection)->getPdo()->lastInsertId();
            }

            $tenantPayload = [
                'name' => $tenantUser->name,
                'email' => $tenantUser->email,
                'phone' => $tenantUser->phone,
                'password' => $tenantUser->password,
                'type' => $tenantUser->type,
                'tenant_id' => (int) $tenant->id,
                'created_by' => (int) ($tenantUser->created_by ?: $landlordUserId),
                'is_active' => (int) ($tenantUser->is_active ?? 1),
                'delete_status' => (int) ($tenantUser->delete_status ?? 1),
                'lang' => $tenantUser->lang,
                'email_verified_at' => $tenantUser->email_verified_at,
                'updated_at' => $now,
            ];

            if (Schema::connection($tenantConnection)->hasColumn('users', 'is_enable_login')) {
                $tenantPayload['is_enable_login'] = (int) ($tenantUser->is_enable_login ?? 1);
            }

            $existingTenantUser = DB::connection($tenantConnection)
                ->table('users')
                ->where('email', $tenantUser->email)
                ->first();

            if ($existingTenantUser) {
                DB::connection($tenantConnection)
                    ->table('users')
                    ->where('id', $existingTenantUser->id)
                    ->update($tenantPayload);
                $stats['tenant_users_updated']++;
                $tenantUserId = (int) $existingTenantUser->id;
            } else {
                $tenantPayload['created_at'] = $tenantUser->created_at ?? $now;
                DB::connection($tenantConnection)->table('users')->insert($tenantPayload);
                $stats['tenant_users_created']++;
                $tenantUserId = (int) DB::connection($tenantConnection)->getPdo()->lastInsertId();
            }

            $roleName = $tenantUser->type === 'company' ? 'company' : $tenantUser->type;
            $tenantRoleId = (int) (DB::connection($tenantConnection)
                ->table('roles')
                ->where('name', $roleName)
                ->value('id') ?? 0);

            if ($tenantRoleId > 0) {
                DB::connection($tenantConnection)->table('model_has_roles')->updateOrInsert(
                    [
                        'role_id' => $tenantRoleId,
                        'model_type' => 'App\Models\User',
                        'model_id' => $tenantUserId,
                    ],
                    []
                );
                $stats['tenant_role_links_created']++;
            } else {
                $stats['conflicts'][] = [
                    'type' => 'missing_tenant_role',
                    'tenant_id' => $tenant->id,
                    'tenant_slug' => $tenant->slug,
                    'email' => $tenantUser->email,
                    'expected_role' => $roleName,
                ];
            }
        }
    } catch (\Throwable $e) {
        $stats['errors'][] = [
            'tenant_id' => $tenant->id,
            'tenant_slug' => $tenant->slug,
            'message' => $e->getMessage(),
        ];
    } finally {
        $tenantManager->end();
        app()->forgetInstance('currentTenant');
    }
}

echo json_encode($stats, JSON_PRETTY_PRINT) . PHP_EOL;
