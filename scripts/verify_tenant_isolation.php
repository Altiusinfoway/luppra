<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenancyManager;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

const REQUIRED_PERMISSION_TABLES = [
    'roles',
    'permissions',
    'model_has_roles',
    'model_has_permissions',
    'role_has_permissions',
];

function bool_text(bool $value): string
{
    return $value ? 'yes' : 'no';
}

function out(string $line = ''): void
{
    fwrite(STDOUT, $line . PHP_EOL);
}

function scoped_permission_cache_key(?int $tenantId): string
{
    return 'spatie.permission.cache.' . ($tenantId ? 'tenant.' . $tenantId : 'landlord');
}

function beginTenant(Tenant $tenant): void
{
    app(TenancyManager::class)->initialize($tenant);
    app()->instance('currentTenant', $tenant);

    config(['permission.cache.key' => scoped_permission_cache_key((int) $tenant->id)]);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
}

function endTenant(): void
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    config(['permission.cache.key' => scoped_permission_cache_key(null)]);

    if (app()->bound('currentTenant')) {
        app()->forgetInstance('currentTenant');
    }

    app(TenancyManager::class)->end();
    DB::purge('tenant');
}

function table_exists(string $connection, string $table): bool
{
    try {
        return Schema::connection($connection)->hasTable($table);
    } catch (Throwable $e) {
        return false;
    }
}

function table_count(string $connection, string $table): ?int
{
    try {
        return (int) DB::connection($connection)->table($table)->count();
    } catch (Throwable $e) {
        return null;
    }
}

function table_max_id(string $connection, string $table): ?int
{
    try {
        $value = DB::connection($connection)->table($table)->max('id');
        return $value === null ? null : (int) $value;
    } catch (Throwable $e) {
        return null;
    }
}

function landlord_permission_summary(): array
{
    return [
        'roles' => table_count('landlord', 'roles'),
        'permissions' => table_count('landlord', 'permissions'),
        'model_has_roles' => table_count('landlord', 'model_has_roles'),
        'model_has_permissions' => table_count('landlord', 'model_has_permissions'),
        'users' => table_count('landlord', 'users'),
        'max_user_id' => table_max_id('landlord', 'users'),
    ];
}

function tenant_emails(Tenant $tenant): array
{
    beginTenant($tenant);

    try {
        if (!table_exists('tenant', 'users')) {
            return [];
        }

        return DB::connection('tenant')
            ->table('users')
            ->whereNotNull('email')
            ->pluck('email')
            ->map(static fn ($email) => strtolower((string) $email))
            ->filter()
            ->values()
            ->all();
    } finally {
        endTenant();
    }
}

function verify_probe_write(Tenant $tenant): array
{
    $probe = [
        'created_permission' => false,
        'created_role' => false,
        'assigned_permission_to_role' => false,
        'role_visible_in_landlord' => null,
        'permission_visible_in_landlord' => null,
        'cleanup_ok' => false,
        'error' => null,
    ];

    $suffix = 'tenant_probe_' . $tenant->id . '_' . date('Ymd_His');
    $permissionName = 'perm_' . $suffix;
    $roleName = 'role_' . $suffix;

    beginTenant($tenant);

    try {
        $permission = Permission::query()->create(['name' => $permissionName, 'guard_name' => 'web']);
        $probe['created_permission'] = true;

        $role = Role::query()->create(['name' => $roleName, 'guard_name' => 'web']);
        $probe['created_role'] = true;

        $role->givePermissionTo($permission);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $probe['assigned_permission_to_role'] = $role->fresh()->hasPermissionTo($permissionName);

        $probe['role_visible_in_landlord'] = DB::connection('landlord')
            ->table('roles')
            ->where('name', $roleName)
            ->exists();

        $probe['permission_visible_in_landlord'] = DB::connection('landlord')
            ->table('permissions')
            ->where('name', $permissionName)
            ->exists();

        DB::connection('tenant')->table('role_has_permissions')
            ->where('role_id', $role->id)
            ->where('permission_id', $permission->id)
            ->delete();

        $role->delete();
        $permission->delete();

        $probe['cleanup_ok'] = !DB::connection('tenant')->table('roles')->where('name', $roleName)->exists()
            && !DB::connection('tenant')->table('permissions')->where('name', $permissionName)->exists();
    } catch (Throwable $e) {
        $probe['error'] = $e->getMessage();
    } finally {
        endTenant();
    }

    return $probe;
}

function verify_tenant(Tenant $tenant, array $landlordSummary, bool $probeWrite): array
{
    $result = [
        'tenant_id' => (int) $tenant->id,
        'slug' => (string) $tenant->slug,
        'database' => (string) $tenant->database,
        'tables' => [],
        'users_count' => null,
        'users_max_id' => null,
        'role_count_direct' => null,
        'permission_count_direct' => null,
        'model_has_roles_count' => null,
        'model_has_permissions_count' => null,
        'user_model_connection' => null,
        'role_model_connection' => null,
        'permission_model_connection' => null,
        'role_count_eloquent' => null,
        'permission_count_eloquent' => null,
        'leaks_to_landlord_counts' => false,
        'user_id_suspicion' => false,
        'probe' => null,
        'error' => null,
    ];

    beginTenant($tenant);

    try {
        foreach (REQUIRED_PERMISSION_TABLES as $table) {
            $result['tables'][$table] = table_exists('tenant', $table);
        }

        $result['tables']['users'] = table_exists('tenant', 'users');

        $tenantUser = new User([
            'tenant_id' => (int) $tenant->id,
            'type' => 'company',
        ]);

        $result['user_model_connection'] = $tenantUser->getConnectionName();
        $result['role_model_connection'] = (new Role())->getConnectionName();
        $result['permission_model_connection'] = (new Permission())->getConnectionName();

        $result['users_count'] = table_count('tenant', 'users');
        $result['users_max_id'] = table_max_id('tenant', 'users');
        $result['role_count_direct'] = table_count('tenant', 'roles');
        $result['permission_count_direct'] = table_count('tenant', 'permissions');
        $result['model_has_roles_count'] = table_count('tenant', 'model_has_roles');
        $result['model_has_permissions_count'] = table_count('tenant', 'model_has_permissions');
        $result['role_count_eloquent'] = Role::query()->count();
        $result['permission_count_eloquent'] = Permission::query()->count();

        $result['leaks_to_landlord_counts'] =
            $result['role_count_direct'] !== null
            && $result['permission_count_direct'] !== null
            && $result['role_count_eloquent'] === $landlordSummary['roles']
            && $result['permission_count_eloquent'] === $landlordSummary['permissions']
            && (
                $result['role_count_eloquent'] !== $result['role_count_direct']
                || $result['permission_count_eloquent'] !== $result['permission_count_direct']
            );

        $result['user_id_suspicion'] =
            $result['users_max_id'] !== null
            && $landlordSummary['max_user_id'] !== null
            && $result['users_count'] !== null
            && $result['users_count'] > 0
            && $result['users_max_id'] > $landlordSummary['max_user_id']
            && $result['users_count'] <= 3;
    } catch (Throwable $e) {
        $result['error'] = $e->getMessage();
    } finally {
        endTenant();
    }

    if ($probeWrite && $result['error'] === null) {
        $result['probe'] = verify_probe_write($tenant);
    }

    return $result;
}

$probeWrite = in_array('--probe-write', $argv, true);
$landlordSummary = landlord_permission_summary();
$tenants = Tenant::query()->where('is_active', true)->orderBy('id')->get();

out('Tenant isolation verification');
out('Date: ' . date('Y-m-d H:i:s'));
out('Tenancy enabled: ' . bool_text((bool) config('tenancy.enabled')));
out('Active tenants: ' . $tenants->count());
out('Probe write mode: ' . bool_text($probeWrite));
out();
out('Landlord baseline');
out('  users=' . ($landlordSummary['users'] ?? 'n/a') . ' max_user_id=' . ($landlordSummary['max_user_id'] ?? 'n/a'));
out('  roles=' . ($landlordSummary['roles'] ?? 'n/a') . ' permissions=' . ($landlordSummary['permissions'] ?? 'n/a'));
out('  model_has_roles=' . ($landlordSummary['model_has_roles'] ?? 'n/a') . ' model_has_permissions=' . ($landlordSummary['model_has_permissions'] ?? 'n/a'));
out();

$emailTenantMap = [];
foreach ($tenants as $tenant) {
    foreach (tenant_emails($tenant) as $email) {
        $emailTenantMap[$email] ??= [];
        $emailTenantMap[$email][] = (int) $tenant->id;
    }
}

$duplicateEmails = array_filter(
    $emailTenantMap,
    static fn (array $tenantIds) => count(array_unique($tenantIds)) > 1
);

if ($duplicateEmails !== []) {
    out('Cross-tenant duplicate emails found: ' . count($duplicateEmails));
    foreach ($duplicateEmails as $email => $tenantIds) {
        out('  ' . $email . ' => tenants [' . implode(', ', array_unique($tenantIds)) . ']');
    }
} else {
    out('Cross-tenant duplicate emails found: 0');
}

out();

$failures = 0;
foreach ($tenants as $tenant) {
    $result = verify_tenant($tenant, $landlordSummary, $probeWrite);

    out('Tenant #' . $result['tenant_id'] . ' [' . $result['slug'] . '] db=' . $result['database']);
    out('  tables.users=' . bool_text((bool) ($result['tables']['users'] ?? false))
        . ' roles=' . bool_text((bool) ($result['tables']['roles'] ?? false))
        . ' permissions=' . bool_text((bool) ($result['tables']['permissions'] ?? false))
        . ' model_has_roles=' . bool_text((bool) ($result['tables']['model_has_roles'] ?? false))
        . ' model_has_permissions=' . bool_text((bool) ($result['tables']['model_has_permissions'] ?? false))
        . ' role_has_permissions=' . bool_text((bool) ($result['tables']['role_has_permissions'] ?? false)));
    out('  connections.user=' . ($result['user_model_connection'] ?? 'n/a')
        . ' role=' . ($result['role_model_connection'] ?? 'n/a')
        . ' permission=' . ($result['permission_model_connection'] ?? 'n/a'));
    out('  counts.users=' . ($result['users_count'] ?? 'n/a')
        . ' max_user_id=' . ($result['users_max_id'] ?? 'n/a')
        . ' roles=' . ($result['role_count_direct'] ?? 'n/a') . '/' . ($result['role_count_eloquent'] ?? 'n/a')
        . ' permissions=' . ($result['permission_count_direct'] ?? 'n/a') . '/' . ($result['permission_count_eloquent'] ?? 'n/a'));
    out('  pivots.model_has_roles=' . ($result['model_has_roles_count'] ?? 'n/a')
        . ' model_has_permissions=' . ($result['model_has_permissions_count'] ?? 'n/a'));
    out('  suspected_landlord_permission_leak=' . bool_text((bool) $result['leaks_to_landlord_counts']));
    out('  suspected_user_id_carryover=' . bool_text((bool) $result['user_id_suspicion']));

    if ($result['probe'] !== null) {
        out('  probe.created_permission=' . bool_text((bool) $result['probe']['created_permission'])
            . ' created_role=' . bool_text((bool) $result['probe']['created_role'])
            . ' assigned=' . bool_text((bool) $result['probe']['assigned_permission_to_role']));
        out('  probe.visible_in_landlord.role=' . bool_text((bool) $result['probe']['role_visible_in_landlord'])
            . ' permission=' . bool_text((bool) $result['probe']['permission_visible_in_landlord'])
            . ' cleanup_ok=' . bool_text((bool) $result['probe']['cleanup_ok']));

        if ($result['probe']['error']) {
            out('  probe.error=' . $result['probe']['error']);
        }
    }

    if ($result['error']) {
        out('  error=' . $result['error']);
    }

    $tenantFailed =
        $result['error'] !== null
        || in_array(false, $result['tables'], true)
        || $result['user_model_connection'] !== 'tenant'
        || $result['role_model_connection'] !== 'tenant'
        || $result['permission_model_connection'] !== 'tenant'
        || $result['leaks_to_landlord_counts'] === true
        || ($result['probe']['error'] ?? null) !== null
        || ($result['probe']['role_visible_in_landlord'] ?? false) === true
        || ($result['probe']['permission_visible_in_landlord'] ?? false) === true
        || (($result['probe'] !== null) && ($result['probe']['cleanup_ok'] ?? false) !== true);

    if ($tenantFailed) {
        $failures++;
    }

    out();
}

out('Summary');
out('  failed_tenants=' . $failures);
out('  duplicate_emails=' . count($duplicateEmails));
out('  overall_pass=' . bool_text($failures === 0));
