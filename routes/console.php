<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\User;
use App\Support\Tenancy\TenancyManager;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote');




Schedule::command('google-sheet:import')
    ->everyTenMinutes()
    ->withoutOverlapping();

Schedule::command('india-mart-sheet:import')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

Artisan::command('tenancy:bootstrap-existing', function () {
    $companies = User::query()->where('type', 'company')->get();

    if ($companies->isEmpty()) {
        $this->warn('No company users found.');
        return;
    }

    foreach ($companies as $company) {
        $slug = Str::slug($company->name ?: ('company-' . $company->id));
        if (empty($slug)) {
            $slug = 'company-' . $company->id;
        }

        $tenant = Tenant::query()->firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $company->name ?: ('Company ' . $company->id),
                'database' => 'tenant_' . $company->id,
                'db_host' => env('TENANT_DB_HOST', env('DB_HOST', '127.0.0.1')),
                'db_port' => (int) env('TENANT_DB_PORT', env('DB_PORT', 3306)),
                'db_username' => env('TENANT_DB_USERNAME', env('DB_USERNAME', 'root')),
                'db_password' => env('TENANT_DB_PASSWORD', env('DB_PASSWORD', '')),
                'is_active' => true,
            ]
        );

        User::query()
            ->where(function ($q) use ($company) {
                $q->where('id', $company->id)->orWhere('created_by', $company->id);
            })
            ->update(['tenant_id' => $tenant->id]);

        $this->info("Mapped company #{$company->id} -> tenant #{$tenant->id} ({$tenant->database})");
    }

    $this->info('Tenancy bootstrap complete.');
})->purpose('Create tenant records for existing company users and map tenant_id');

Artisan::command('tenancy:provision-sales {tenant? : Tenant ID or slug (optional)} {--sync-data : Copy scoped sales data from landlord} {--force : Recreate target tables before sync}', function () {
    $tenantRef = $this->argument('tenant');

    $tenantQuery = Tenant::query()->where('is_active', true);

    if (!empty($tenantRef)) {
        if (is_numeric($tenantRef)) {
            $tenantQuery->where('id', (int) $tenantRef);
        } else {
            $tenantQuery->where('slug', $tenantRef);
        }
    }

    $tenants = $tenantQuery->get();

    if ($tenants->isEmpty()) {
        $this->warn('No active tenants found for provisioning.');
        return;
    }

    $landlordConnection = DB::connection('landlord');
    $landlordDatabase = (string) config('database.connections.landlord.database');

    $tablesToProvision = [
        'users',
        'permissions',
        'roles',
        'model_has_permissions',
        'model_has_roles',
        'role_has_permissions',
        'customer_price_histories',
        'entity_addresses',
        'transports',
        'terms_and_conditions',
        'settings',
        'countries',
        'states',
        'cities',
        'lead_stages',
        'lead_sources',
        'lead_types',
        'products',
        'departments',
        'designations',
        'employees',
        'holidays',
        'leaves',
        'attendances',
        'sales_targets',
        'working_hours',
        'leave_rules',
        'leave_types',
        'employee_salary_details',
        'employee_sales_targets',
        'employee_payment_histories',
        'user_logins',
        'payments',
        'bank_details',
        'location_histories',
        'advertisements',
        'entities',
        'addresses',
        'customer_phones',
        'order_stages',
        'units',
        'unit_types',
        'categories',
        'gst_slab_masters',
        'leads',
        'user_leads',
        'lead_products',
        'lead_chats',
        'lead_calls',
        'lead_activities',
        'activity_logs',
        'quotes',
        'quote_products',
        'orders',
        'order_products',
        'order_payments',
        'order_activities',
        'jobs',
        'job_batches',
        'password_reset_tokens',
        'personal_access_tokens',
        'sessions',
        'devices',
        'whatsapp_bot_rules',
        'whatsapp_bot_knowledge',
        'third_parties',
        'vendor_products',
        'taxes',
    ];

    $chunkInsert = function (string $table, array $rows): void {
        if (empty($rows)) {
            return;
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::connection('tenant')->table($table)->insert($chunk);
        }
    };

    $fetchRows = function (string $table, ?string $column = null, array $ids = [], array $creatorIds = []): array {
        $query = DB::connection('landlord')->table($table);

        if (!empty($column) && !empty($ids)) {
            $query->whereIn($column, $ids);
        } elseif (!empty($creatorIds) && Schema::connection('landlord')->hasColumn($table, 'created_by')) {
            $query->whereIn('created_by', $creatorIds);
        } else {
            return [];
        }

        return $query->get()->map(static fn ($item) => (array) $item)->all();
    };

    foreach ($tenants as $tenant) {
        if (empty($tenant->database)) {
            $this->error("Tenant #{$tenant->id} has empty database name. Skipped.");
            continue;
        }

        $tenantDatabase = str_replace('`', '``', (string) $tenant->database);
        $landlordConnection->statement("CREATE DATABASE IF NOT EXISTS `{$tenantDatabase}`");

        app(TenancyManager::class)->initialize($tenant);

        foreach ($tablesToProvision as $table) {
            if (!Schema::connection('landlord')->hasTable($table)) {
                continue;
            }

            if ($this->option('force') && Schema::connection('tenant')->hasTable($table)) {
                DB::connection('tenant')->statement("DROP TABLE `{$table}`");
            }

            if (!Schema::connection('tenant')->hasTable($table)) {
                DB::connection('tenant')->statement("CREATE TABLE `{$table}` LIKE `{$landlordDatabase}`.`{$table}`");
            }
        }

        if ($this->option('sync-data')) {
            $creatorIds = User::query()
                ->where('tenant_id', $tenant->id)
                ->where('type', 'company')
                ->pluck('id')
                ->map(static fn ($id) => (int) $id)
                ->all();

            if (empty($creatorIds)) {
                $creatorIds = User::query()
                    ->where('tenant_id', $tenant->id)
                    ->whereNotNull('created_by')
                    ->distinct()
                    ->pluck('created_by')
                    ->map(static fn ($id) => (int) $id)
                    ->all();
            }

            if (empty($creatorIds)) {
                $this->warn("Tenant #{$tenant->id}: no creator IDs found, skipping data sync.");
                continue;
            }

            foreach ($tablesToProvision as $table) {
                if (Schema::connection('tenant')->hasTable($table)) {
                    DB::connection('tenant')->table($table)->truncate();
                }
            }

            $tenantUserRows = [];
            $tenantUserIds = [];
            if (Schema::connection('tenant')->hasTable('users') && Schema::connection('landlord')->hasTable('users')) {
                $tenantUserRows = $fetchRows('users', null, [], $creatorIds);
                $tenantUserIds = array_values(array_unique(array_map(static fn ($row) => (int) ($row['id'] ?? 0), $tenantUserRows)));
                $chunkInsert('users', $tenantUserRows);
            }

            $masterTablesByCreator = [
                'lead_stages',
                'lead_sources',
                'lead_types',
                'products',
                'entities',
                'order_stages',
            ];

            foreach ($masterTablesByCreator as $table) {
                if (Schema::connection('tenant')->hasTable($table)) {
                    $chunkInsert($table, $fetchRows($table, null, [], $creatorIds));
                }
            }

            $globalTables = ['units', 'unit_types', 'categories', 'gst_slab_masters', 'countries', 'states', 'cities'];
            foreach ($globalTables as $table) {
                if (!Schema::connection('tenant')->hasTable($table) || !Schema::connection('landlord')->hasTable($table)) {
                    continue;
                }

                $rows = DB::connection('landlord')->table($table)->get()->map(static fn ($item) => (array) $item)->all();
                $chunkInsert($table, $rows);
            }

            // Permission catalog is global; copy fully to each tenant DB.
            if (Schema::connection('tenant')->hasTable('permissions') && Schema::connection('landlord')->hasTable('permissions')) {
                $permissionRows = DB::connection('landlord')->table('permissions')->get()->map(static fn ($item) => (array) $item)->all();
                $chunkInsert('permissions', $permissionRows);
            }

            // Roles are tenant-owned by created_by when available.
            $roleIds = [];
            if (Schema::connection('tenant')->hasTable('roles') && Schema::connection('landlord')->hasTable('roles')) {
                $rolesQuery = DB::connection('landlord')->table('roles');
                if (Schema::connection('landlord')->hasColumn('roles', 'created_by')) {
                    $rolesQuery->where(function ($q) use ($creatorIds) {
                        $q->whereIn('created_by', $creatorIds)
                            ->orWhereIn('name', ['company', 'client', 'super admin', 'Sales', 'HRM', 'Employee']);
                    });
                }
                $roleRows = $rolesQuery->get()->map(static fn ($item) => (array) $item)->all();
                $chunkInsert('roles', $roleRows);
                $roleIds = array_values(array_unique(array_map(static fn ($row) => (int) ($row['id'] ?? 0), $roleRows)));
            }

            if (!empty($roleIds) && Schema::connection('tenant')->hasTable('role_has_permissions') && Schema::connection('landlord')->hasTable('role_has_permissions')) {
                $rolePermRows = DB::connection('landlord')->table('role_has_permissions')
                    ->whereIn('role_id', $roleIds)
                    ->get()
                    ->map(static fn ($item) => (array) $item)
                    ->all();
                $chunkInsert('role_has_permissions', $rolePermRows);
            }

            if (!empty($tenantUserIds) && !empty($roleIds) && Schema::connection('tenant')->hasTable('model_has_roles') && Schema::connection('landlord')->hasTable('model_has_roles')) {
                $modelRoleRows = DB::connection('landlord')->table('model_has_roles')
                    ->whereIn('role_id', $roleIds)
                    ->whereIn('model_id', $tenantUserIds)
                    ->whereIn('model_type', ['App\\Models\\User', 'App\\User', 'AppModelsUser'])
                    ->get()
                    ->map(function ($item) {
                        $row = (array) $item;
                        if (($row['model_type'] ?? null) === 'AppModelsUser') {
                            $row['model_type'] = 'App\\Models\\User';
                        }
                        return $row;
                    })
                    ->all();
                $chunkInsert('model_has_roles', $modelRoleRows);
            }

            if (!empty($tenantUserIds) && Schema::connection('tenant')->hasTable('model_has_permissions') && Schema::connection('landlord')->hasTable('model_has_permissions')) {
                $modelPermissionRows = DB::connection('landlord')->table('model_has_permissions')
                    ->whereIn('model_id', $tenantUserIds)
                    ->whereIn('model_type', ['App\\Models\\User', 'App\\User', 'AppModelsUser'])
                    ->get()
                    ->map(function ($item) {
                        $row = (array) $item;
                        if (($row['model_type'] ?? null) === 'AppModelsUser') {
                            $row['model_type'] = 'App\\Models\\User';
                        }
                        return $row;
                    })
                    ->all();
                $chunkInsert('model_has_permissions', $modelPermissionRows);
            }

            if (Schema::connection('tenant')->hasTable('customer_phones') && Schema::connection('landlord')->hasTable('customer_phones')) {
                $entityIds = DB::connection('tenant')->table('entities')->pluck('id')->map(static fn ($id) => (int) $id)->all();
                if (!empty($entityIds) && Schema::connection('landlord')->hasColumn('customer_phones', 'customer_id')) {
                    $customerPhoneRows = DB::connection('landlord')->table('customer_phones')->whereIn('customer_id', $entityIds)->get()->map(static fn ($item) => (array) $item)->all();
                    $chunkInsert('customer_phones', $customerPhoneRows);
                }
            }

            if (Schema::connection('tenant')->hasTable('addresses') && Schema::connection('landlord')->hasTable('addresses') && Schema::connection('tenant')->hasTable('entities')) {
                $addressIds = DB::connection('tenant')->table('entities')
                    ->select('address_id', 'billing_address_id', 'shipping_address_id')
                    ->get()
                    ->flatMap(function ($row) {
                        return [$row->address_id, $row->billing_address_id, $row->shipping_address_id];
                    })
                    ->filter()
                    ->map(static fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();

                if (!empty($addressIds)) {
                    $addressRows = DB::connection('landlord')->table('addresses')
                        ->whereIn('id', $addressIds)
                        ->get()
                        ->map(static fn ($item) => (array) $item)
                        ->all();
                    $chunkInsert('addresses', $addressRows);
                }
            }

            if (Schema::connection('tenant')->hasTable('entity_addresses') && Schema::connection('landlord')->hasTable('entity_addresses')) {
                $entityIds = DB::connection('tenant')->table('entities')->pluck('id')->map(static fn ($id) => (int) $id)->all();
                if (!empty($entityIds) && Schema::connection('landlord')->hasColumn('entity_addresses', 'entity_id')) {
                    $entityAddressRows = DB::connection('landlord')->table('entity_addresses')
                        ->whereIn('entity_id', $entityIds)
                        ->get()
                        ->map(static fn ($item) => (array) $item)
                        ->all();
                    $chunkInsert('entity_addresses', $entityAddressRows);
                }
            }

            if (Schema::connection('tenant')->hasTable('transports') && Schema::connection('landlord')->hasTable('transports')) {
                $transportRows = DB::connection('landlord')->table('transports')
                    ->get()
                    ->map(static fn ($item) => (array) $item)
                    ->all();
                $chunkInsert('transports', $transportRows);
            }

            $leadRows = $fetchRows('leads', null, [], $creatorIds);
            $leadIds = array_map(static fn ($row) => (int) $row['id'], $leadRows);
            $chunkInsert('leads', $leadRows);
            $chunkInsert('user_leads', $fetchRows('user_leads', 'lead_id', $leadIds));
            $chunkInsert('lead_products', $fetchRows('lead_products', 'lead_id', $leadIds));
            $chunkInsert('lead_chats', $fetchRows('lead_chats', 'lead_id', $leadIds));
            $chunkInsert('lead_calls', $fetchRows('lead_calls', 'lead_id', $leadIds));
            $chunkInsert('lead_activities', $fetchRows('lead_activities', 'lead_id', $leadIds));

            $quoteRows = $fetchRows('quotes', null, [], $creatorIds);
            $quoteIds = array_map(static fn ($row) => (int) $row['id'], $quoteRows);
            $chunkInsert('quotes', $quoteRows);
            $chunkInsert('quote_products', $fetchRows('quote_products', 'quote_id', $quoteIds));

            $orderRows = $fetchRows('orders', null, [], $creatorIds);
            $orderIds = array_map(static fn ($row) => (int) $row['id'], $orderRows);
            $chunkInsert('orders', $orderRows);
            $chunkInsert('order_products', $fetchRows('order_products', 'order_id', $orderIds));
            $chunkInsert('order_payments', $fetchRows('order_payments', 'order_id', $orderIds));
            $chunkInsert('order_activities', $fetchRows('order_activities', 'order_id', $orderIds));

            if (Schema::connection('tenant')->hasTable('customer_price_histories') && Schema::connection('landlord')->hasTable('customer_price_histories')) {
                $customerIds = DB::connection('tenant')->table('entities')
                    ->where('type', 'customer')
                    ->pluck('id')
                    ->map(static fn ($id) => (int) $id)
                    ->all();
                if (!empty($customerIds) && Schema::connection('landlord')->hasColumn('customer_price_histories', 'customer_id')) {
                    $customerPriceRows = DB::connection('landlord')->table('customer_price_histories')
                        ->whereIn('customer_id', $customerIds)
                        ->get()
                        ->map(static fn ($item) => (array) $item)
                        ->all();
                    $chunkInsert('customer_price_histories', $customerPriceRows);
                }
            }

            if (Schema::connection('tenant')->hasTable('vendor_products') && Schema::connection('landlord')->hasTable('vendor_products')) {
                $vendorIds = DB::connection('tenant')->table('entities')
                    ->where('type', 'vendor')
                    ->pluck('id')
                    ->map(static fn ($id) => (int) $id)
                    ->all();
                if (!empty($vendorIds) && Schema::connection('landlord')->hasColumn('vendor_products', 'vendor_id')) {
                    $vendorProductRows = DB::connection('landlord')->table('vendor_products')
                        ->whereIn('vendor_id', $vendorIds)
                        ->get()
                        ->map(static fn ($item) => (array) $item)
                        ->all();
                    $chunkInsert('vendor_products', $vendorProductRows);
                }
            }

            if (Schema::connection('tenant')->hasTable('third_parties') && Schema::connection('landlord')->hasTable('third_parties')) {
                $thirdPartyRows = DB::connection('landlord')->table('third_parties')->get()->map(static fn ($item) => (array) $item)->all();
                $chunkInsert('third_parties', $thirdPartyRows);
            }

            if (Schema::connection('tenant')->hasTable('taxes') && Schema::connection('landlord')->hasTable('taxes')) {
                $taxRows = DB::connection('landlord')->table('taxes')->get()->map(static fn ($item) => (array) $item)->all();
                $chunkInsert('taxes', $taxRows);
            }

            $handledTables = [
                'lead_stages',
                'lead_sources',
                'lead_types',
                'products',
                'entities',
                'addresses',
                'customer_phones',
                'order_stages',
                'units',
                'unit_types',
                'categories',
                'gst_slab_masters',
                'permissions',
                'customer_price_histories',
                'entity_addresses',
                'transports',
                'vendor_products',
                'third_parties',
                'taxes',
                'roles',
                'role_has_permissions',
                'users',
                'model_has_roles',
                'model_has_permissions',
                'leads',
                'user_leads',
                'lead_products',
                'lead_chats',
                'lead_calls',
                'lead_activities',
                'quotes',
                'quote_products',
                'orders',
                'order_products',
                'order_payments',
                'order_activities',
                'jobs',
                'job_batches',
                'password_reset_tokens',
                'personal_access_tokens',
                'sessions',
            ];

            foreach (array_diff($tablesToProvision, $handledTables) as $table) {
                if (!Schema::connection('tenant')->hasTable($table) || !Schema::connection('landlord')->hasTable($table)) {
                    continue;
                }

                if (Schema::connection('landlord')->hasColumn($table, 'created_by')) {
                    $chunkInsert($table, $fetchRows($table, null, [], $creatorIds));
                    continue;
                }

                // For operational tables without created_by, keep schema-only provisioning.
                // Cross-tenant data copy is intentionally skipped to avoid leakage.
            }

            $this->info("Tenant #{$tenant->id}: synced leads (" . count($leadRows) . "), quotes (" . count($quoteRows) . "), orders (" . count($orderRows) . ").");
        } else {
            $alwaysSeedMasterTables = ['units', 'unit_types', 'categories', 'gst_slab_masters', 'permissions', 'third_parties', 'taxes'];
            foreach ($alwaysSeedMasterTables as $table) {
                if (!Schema::connection('tenant')->hasTable($table) || !Schema::connection('landlord')->hasTable($table)) {
                    continue;
                }

                $hasRows = DB::connection('tenant')->table($table)->count() > 0;
                if ($hasRows) {
                    continue;
                }

                $rows = DB::connection('landlord')->table($table)->get()->map(static fn ($item) => (array) $item)->all();
                foreach (array_chunk($rows, 500) as $chunk) {
                    DB::connection('tenant')->table($table)->insert($chunk);
                }
            }

            $this->info("Tenant #{$tenant->id}: database and tables provisioned (no data sync).");
        }
    }

    $this->info('Provisioning finished.');
})->purpose('Create tenant DB + sales tables and optionally sync scoped leads/quotes/orders data');

Artisan::command('tenancy:seed-masters {tenant : Tenant ID or slug} {--template-company-id= : Company user id to copy seeded masters from}', function () {
    $tenantRef = (string) $this->argument('tenant');
    $tenant = is_numeric($tenantRef)
        ? Tenant::query()->find((int) $tenantRef)
        : Tenant::query()->where('slug', $tenantRef)->first();

    if (!$tenant) {
        $this->error("Tenant '{$tenantRef}' not found.");
        return;
    }

    app(TenancyManager::class)->initialize($tenant);

    $templateCompanyId = $this->option('template-company-id');
    if (empty($templateCompanyId)) {
        $templateCompanyId = User::query()->where('type', 'company')->orderBy('id')->value('id');
    }

    $createdByTables = [
        'lead_stages',
        'lead_sources',
        'lead_types',
        'order_stages',
        'products',
        'gst_slab_masters',
    ];

    $globalTables = ['units', 'unit_types', 'categories', 'countries', 'states', 'cities', 'third_parties', 'taxes'];

    foreach (array_merge($createdByTables, $globalTables) as $table) {
        if (!Schema::connection('tenant')->hasTable($table) || !Schema::connection('landlord')->hasTable($table)) {
            continue;
        }

        DB::connection('tenant')->table($table)->truncate();
    }

    if (!empty($templateCompanyId)) {
        foreach ($createdByTables as $table) {
            if (!Schema::connection('tenant')->hasTable($table) || !Schema::connection('landlord')->hasTable($table)) {
                continue;
            }

            $query = DB::connection('landlord')->table($table);
            if (Schema::connection('landlord')->hasColumn($table, 'created_by')) {
                $query->where('created_by', (int) $templateCompanyId);
            }

            $rows = $query->get()->map(static fn ($item) => (array) $item)->all();
            foreach (array_chunk($rows, 500) as $chunk) {
                DB::connection('tenant')->table($table)->insert($chunk);
            }
        }
    }

    foreach ($globalTables as $table) {
        if (!Schema::connection('tenant')->hasTable($table) || !Schema::connection('landlord')->hasTable($table)) {
            continue;
        }

        $rows = DB::connection('landlord')->table($table)->get()->map(static fn ($item) => (array) $item)->all();
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::connection('tenant')->table($table)->insert($chunk);
        }
    }

    $this->info("Tenant #{$tenant->id} master seed complete.");
})->purpose('Seed tenant master tables (stages/sources/products/etc.) from landlord template');

Artisan::command('tenancy:seed-permissions {tenant : Tenant ID or slug} {--template-company-id= : Company user id to copy role base from}', function () {
    $tenantRef = (string) $this->argument('tenant');
    $tenant = is_numeric($tenantRef)
        ? Tenant::query()->find((int) $tenantRef)
        : Tenant::query()->where('slug', $tenantRef)->first();

    if (!$tenant) {
        $this->error("Tenant '{$tenantRef}' not found.");
        return;
    }

    app(TenancyManager::class)->initialize($tenant);

    $templateCompanyId = $this->option('template-company-id');
    if (empty($templateCompanyId)) {
        $templateCompanyId = User::query()->where('type', 'company')->orderBy('id')->value('id');
    }

    if (!Schema::connection('tenant')->hasTable('permissions') || !Schema::connection('landlord')->hasTable('permissions')) {
        $this->warn('Permissions table missing on landlord or tenant. Skipped.');
        app(TenancyManager::class)->end();
        return;
    }

    $rows = DB::connection('landlord')->table('permissions')->get()->map(static fn ($item) => (array) $item)->all();
    if (Schema::connection('tenant')->hasTable('permissions')) {
        DB::connection('tenant')->table('permissions')->truncate();
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::connection('tenant')->table('permissions')->insert($chunk);
        }
    }

    if (Schema::connection('landlord')->hasTable('roles') && Schema::connection('tenant')->hasTable('roles')) {
        $rolesQuery = DB::connection('landlord')->table('roles');
        if (!empty($templateCompanyId) && Schema::connection('landlord')->hasColumn('roles', 'created_by')) {
            $rolesQuery->where(function ($q) use ($templateCompanyId) {
                $q->where('created_by', (int) $templateCompanyId)
                    ->orWhereIn('name', ['company', 'client', 'super admin', 'Sales', 'HRM', 'Employee']);
            });
        } else {
            $rolesQuery->whereIn('name', ['company', 'client', 'super admin', 'Sales', 'HRM', 'Employee']);
        }

        $roleRows = $rolesQuery->get()->map(static fn ($item) => (array) $item)->all();
        if (!empty($roleRows)) {
            DB::connection('tenant')->table('roles')->truncate();
            foreach (array_chunk($roleRows, 500) as $chunk) {
                DB::connection('tenant')->table('roles')->insert($chunk);
            }
        }
    } else {
        $this->warn('Roles table missing on landlord or tenant. Skipped role seed.');
    }

    $roleIds = [];
    if (Schema::connection('tenant')->hasTable('roles') && Schema::connection('landlord')->hasTable('roles')) {
        $roleIds = DB::connection('tenant')->table('roles')
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->all();
    }

    if (!empty($roleIds) && Schema::connection('landlord')->hasTable('role_has_permissions') && Schema::connection('tenant')->hasTable('role_has_permissions')) {
        $rolePermRows = DB::connection('landlord')->table('role_has_permissions')
            ->whereIn('role_id', $roleIds)
            ->get()
            ->map(static fn ($item) => (array) $item)
            ->all();
        DB::connection('tenant')->table('role_has_permissions')->truncate();
        foreach (array_chunk($rolePermRows, 500) as $chunk) {
            DB::connection('tenant')->table('role_has_permissions')->insert($chunk);
        }

        // Ensure sales role has complete sales-module access even when landlord mappings are sparse.
        $salesRoleId = (int) (DB::connection('tenant')->table('roles')->where('name', 'Sales')->value('id') ?? 0);
        if ($salesRoleId > 0) {
            $allPerms = DB::connection('tenant')->table('permissions')->select('id', 'name')->get();
            $existingSalesPermIds = DB::connection('tenant')->table('role_has_permissions')
                ->where('role_id', $salesRoleId)
                ->pluck('permission_id')
                ->map(static fn ($id) => (int) $id)
                ->all();

            $allowKeywords = [
                'lead',
                'quote',
                'order',
                'payment',
                'spanko',
                'follow-up',
                'customer',
                'vender',
                'transport',
                'product & service',
                'category',
                'invoice',
                'device',
                'report',
                'sales target',
                'sales_employee_target',
                'sales employee target',
            ];

            $denyKeywords = [
                'manage role',
                'create role',
                'edit role',
                'delete role',
                'sdelete role',
                'manage company settings',
                'manage user',
                'create user',
                'edit user',
                'delete user',
                'sdelete user',
                'manage employee',
                'manage department',
                'manage designation',
                'manage attendance',
                'manage leave',
                'manage holiday',
                'manage payroll',
                'manage working hours',
                'manage leave rule',
                'manage leave type',
            ];

            $ensurePermIds = $allPerms
                ->filter(function ($perm) use ($allowKeywords, $denyKeywords) {
                    $name = strtolower(trim((string) ($perm->name ?? '')));
                    if ($name === '') {
                        return false;
                    }

                    if (Str::contains($name, $denyKeywords)) {
                        return false;
                    }

                    return Str::contains($name, $allowKeywords);
                })
                ->pluck('id')
                ->map(static fn ($id) => (int) $id)
                ->all();

            $missingSalesPermIds = array_values(array_diff($ensurePermIds, $existingSalesPermIds));
            if (!empty($missingSalesPermIds)) {
                $rows = array_map(static fn ($permId) => [
                    'permission_id' => (int) $permId,
                    'role_id' => $salesRoleId,
                ], $missingSalesPermIds);

                foreach (array_chunk($rows, 500) as $chunk) {
                    DB::connection('tenant')->table('role_has_permissions')->insert($chunk);
                }
            }
        }
    } else {
        $this->warn('Role permissions table missing on landlord or tenant.');
    }

    $tenantUserIds = Schema::connection('tenant')->hasTable('users')
        ? DB::connection('tenant')->table('users')->pluck('id')->map(static fn ($id) => (int) $id)->all()
        : [];

    if (!empty($tenantUserIds) && Schema::connection('landlord')->hasTable('model_has_roles') && Schema::connection('tenant')->hasTable('model_has_roles')) {
        // Preserve tenant-side assignments, merge landlord mirror rows, then backfill by user type.
        $tenantModelRoleRows = DB::connection('tenant')->table('model_has_roles')
            ->whereIn('model_id', $tenantUserIds)
            ->whereIn('model_type', ['App\\Models\\User', 'App\\User', 'AppModelsUser'])
            ->whereIn('role_id', $roleIds)
            ->get()
            ->map(function ($item) {
                $row = (array) $item;
                if (($row['model_type'] ?? null) === 'AppModelsUser' || ($row['model_type'] ?? null) === 'App\\User') {
                    $row['model_type'] = 'App\\Models\\User';
                }
                return $row;
            })
            ->all();

        $landlordModelRoleRows = DB::connection('landlord')->table('model_has_roles')
            ->whereIn('model_id', $tenantUserIds)
            ->whereIn('model_type', ['App\\Models\\User', 'App\\User', 'AppModelsUser'])
            ->whereIn('role_id', $roleIds)
            ->get()
            ->map(function ($item) {
                $row = (array) $item;
                if (($row['model_type'] ?? null) === 'AppModelsUser' || ($row['model_type'] ?? null) === 'App\\User') {
                    $row['model_type'] = 'App\\Models\\User';
                }
                return $row;
            })
            ->all();

        $mergedModelRoleRows = [];
        foreach (array_merge($tenantModelRoleRows, $landlordModelRoleRows) as $row) {
            $key = ((int) ($row['role_id'] ?? 0)) . '|' . (string) ($row['model_type'] ?? '') . '|' . ((int) ($row['model_id'] ?? 0));
            $mergedModelRoleRows[$key] = [
                'role_id' => (int) ($row['role_id'] ?? 0),
                'model_type' => (string) ($row['model_type'] ?? 'App\\Models\\User'),
                'model_id' => (int) ($row['model_id'] ?? 0),
            ];
        }

        $assignedUserIds = array_values(array_unique(array_map(
            static fn ($row) => (int) ($row['model_id'] ?? 0),
            array_values($mergedModelRoleRows)
        )));

        $missingRoleUserIds = array_values(array_diff($tenantUserIds, $assignedUserIds));
        if (!empty($missingRoleUserIds)) {
            $roleNameToId = DB::connection('tenant')->table('roles')
                ->whereIn('name', ['company', 'client', 'super admin', 'Sales', 'HRM', 'Employee'])
                ->pluck('id', 'name')
                ->map(static fn ($id) => (int) $id)
                ->all();

            $typeToRoleName = [
                'company' => 'company',
                'client' => 'client',
                'super admin' => 'super admin',
                'sales' => 'Sales',
                'hrm' => 'HRM',
                'employee' => 'Employee',
            ];

            $missingUsers = DB::connection('tenant')->table('users')
                ->whereIn('id', $missingRoleUserIds)
                ->select('id', 'type')
                ->get();

            foreach ($missingUsers as $missingUser) {
                $typeKey = strtolower(trim((string) ($missingUser->type ?? '')));
                $roleName = $typeToRoleName[$typeKey] ?? (string) ($missingUser->type ?? '');
                $roleId = (int) ($roleNameToId[$roleName] ?? 0);
                if ($roleId <= 0) {
                    continue;
                }

                $key = $roleId . '|App\\Models\\User|' . ((int) $missingUser->id);
                $mergedModelRoleRows[$key] = [
                    'role_id' => $roleId,
                    'model_type' => 'App\\Models\\User',
                    'model_id' => (int) $missingUser->id,
                ];
            }
        }

        DB::connection('tenant')->table('model_has_roles')->truncate();
        foreach (array_chunk(array_values($mergedModelRoleRows), 500) as $chunk) {
            DB::connection('tenant')->table('model_has_roles')->insert($chunk);
        }
    }

    if (!empty($tenantUserIds) && Schema::connection('landlord')->hasTable('model_has_permissions') && Schema::connection('tenant')->hasTable('model_has_permissions')) {
                $modelPermissionRows = DB::connection('landlord')->table('model_has_permissions')
                    ->whereIn('model_id', $tenantUserIds)
                    ->whereIn('model_type', ['App\\Models\\User', 'App\\User', 'AppModelsUser'])
                    ->get()
                    ->map(function ($item) {
                        $row = (array) $item;
                        if (($row['model_type'] ?? null) === 'AppModelsUser') {
                            $row['model_type'] = 'App\\Models\\User';
                        }
                        return $row;
                    })
                    ->all();
        DB::connection('tenant')->table('model_has_permissions')->truncate();
        foreach (array_chunk($modelPermissionRows, 500) as $chunk) {
            DB::connection('tenant')->table('model_has_permissions')->insert($chunk);
        }
    }

    app(TenancyManager::class)->end();
    $this->info("Tenant permissions seeded: tenant={$tenant->id}");
})->purpose('Seed tenant permission tables and role mappings from landlord template');

Artisan::command('tenancy:repair-permission-model-types {tenant? : Tenant ID or slug}', function () {
    $tenantRef = trim((string) $this->argument('tenant'));

    $tenants = $tenantRef === ''
        ? Tenant::query()->where('is_active', true)->get()
        : (is_numeric($tenantRef)
            ? Tenant::query()->where('id', (int) $tenantRef)->where('is_active', true)->get()
            : Tenant::query()->where('slug', $tenantRef)->where('is_active', true)->get());

    if ($tenants->isEmpty()) {
        $this->warn('No active tenant found to repair.');
        return;
    }

    $tenantTables = ['model_has_roles', 'model_has_permissions'];
    $landlordRepaired = 0;

    foreach ($tenantTables as $table) {
        if (Schema::connection('landlord')->hasTable($table)) {
            $landlordRepaired += DB::connection('landlord')->table($table)
                ->whereIn('model_type', ['App\\User', 'AppModelsUser'])
                ->update(['model_type' => 'App\\Models\\User']);
        }
    }

    foreach ($tenants as $tenant) {
        app(TenancyManager::class)->initialize($tenant);

        $tenantRepaired = 0;
        foreach ($tenantTables as $table) {
            if (Schema::connection('tenant')->hasTable($table)) {
                $tenantRepaired += DB::connection('tenant')->table($table)
                    ->whereIn('model_type', ['App\\User', 'AppModelsUser'])
                    ->update(['model_type' => 'App\\Models\\User']);
            }
        }

        app(TenancyManager::class)->end();
        $this->info("Repaired tenant #{$tenant->id}: {$tenantRepaired} rows.");
    }

    $this->info("Landlord permission model_type rows repaired: {$landlordRepaired}");
})->purpose('Repair invalid permission model_type values to App\\\\Models\\\\User');

Artisan::command('tenancy:create {name : Tenant name} {--slug= : Tenant slug} {--database= : Database name} {--domain= : Optional primary domain} {--db-host= : Tenant DB host} {--db-port= : Tenant DB port} {--db-username= : Tenant DB username} {--db-password= : Tenant DB password} {--with-seed : Seed master data after provisioning} {--template-company-id= : Company user id to seed from}', function () {
    $name = trim((string) $this->argument('name'));
    $slugBase = trim((string) ($this->option('slug') ?: Str::slug($name)));
    $slugBase = $slugBase !== '' ? $slugBase : 'tenant';

    $slug = $slugBase;
    $suffix = 1;
    while (Tenant::query()->where('slug', $slug)->exists()) {
        $slug = $slugBase . '-' . $suffix;
        $suffix++;
    }

    $databaseBase = trim((string) ($this->option('database') ?: ('tenant_' . str_replace('-', '_', $slug))));
    $databaseBase = $databaseBase !== '' ? $databaseBase : ('tenant_' . time());
    $database = $databaseBase;
    $dbSuffix = 1;
    while (Tenant::query()->where('database', $database)->exists()) {
        $database = $databaseBase . '_' . $dbSuffix;
        $dbSuffix++;
    }

    $tenant = Tenant::query()->create([
        'name' => $name,
        'slug' => $slug,
        'database' => $database,
        'db_host' => $this->option('db-host') ?: env('TENANT_DB_HOST', env('DB_HOST', '127.0.0.1')),
        'db_port' => (int) ($this->option('db-port') ?: env('TENANT_DB_PORT', env('DB_PORT', 3306))),
        'db_username' => $this->option('db-username') ?: env('TENANT_DB_USERNAME', env('DB_USERNAME', 'root')),
        'db_password' => $this->option('db-password') ?: env('TENANT_DB_PASSWORD', env('DB_PASSWORD', '')),
        'is_active' => true,
    ]);

    $domain = trim((string) $this->option('domain'));
    if ($domain !== '') {
        TenantDomain::query()->firstOrCreate(
            ['domain' => strtolower($domain)],
            ['tenant_id' => $tenant->id, 'is_primary' => true]
        );
    }

    DB::connection('landlord')->statement("CREATE DATABASE IF NOT EXISTS `" . str_replace('`', '``', $tenant->database) . "`");

    Artisan::call('tenancy:provision-sales', [
        'tenant' => (string) $tenant->id,
    ]);
    $this->line(trim(Artisan::output()));

    // Always copy geo masters so fresh tenant DB can use region forms immediately.
    app(TenancyManager::class)->initialize($tenant);
    foreach (['countries', 'states', 'cities'] as $table) {
        if (!Schema::connection('tenant')->hasTable($table) || !Schema::connection('landlord')->hasTable($table)) {
            continue;
        }

        DB::connection('tenant')->table($table)->truncate();
        $geoRows = DB::connection('landlord')->table($table)->get()->map(static fn ($item) => (array) $item)->all();
        foreach (array_chunk($geoRows, 500) as $chunk) {
            DB::connection('tenant')->table($table)->insert($chunk);
        }
    }
    app(TenancyManager::class)->end();

    if ($this->option('with-seed')) {
        Artisan::call('tenancy:seed-permissions', [
            'tenant' => (string) $tenant->id,
            '--template-company-id' => $this->option('template-company-id'),
        ]);
        $this->line(trim(Artisan::output()));
    }

    $this->info("Tenant created: id={$tenant->id}, slug={$tenant->slug}, db={$tenant->database}");
})->purpose('Create a tenant DB record and provision base sales tables (optionally with tenant access seed)');

Artisan::command('tenancy:seed-settings {tenant : Tenant ID or slug} {--creator-id= : Tenant company user id} {--template-creator-id= : Landlord company user id to copy settings from} {--website-name= : Website/company name for tenant settings} {--email= : Company email for tenant settings} {--phone= : Company phone for tenant settings} {--website-logo=engage-logo.png : Default website logo filename}', function () {
    $tenantRef = (string) $this->argument('tenant');
    $tenant = is_numeric($tenantRef)
        ? Tenant::query()->find((int) $tenantRef)
        : Tenant::query()->where('slug', $tenantRef)->first();

    if (!$tenant) {
        $this->error("Tenant '{$tenantRef}' not found.");
        return 1;
    }

    $creatorId = (int) ($this->option('creator-id') ?: 0);
    if ($creatorId <= 0) {
        $creatorId = (int) (
            User::query()
                ->where('tenant_id', (int) $tenant->id)
                ->where('type', 'company')
                ->orderBy('id')
                ->value('id') ?? 0
        );
    }

    if ($creatorId <= 0) {
        $this->error('Unable to resolve tenant company user (creator id).');
        return 1;
    }

    app(TenancyManager::class)->initialize($tenant);
    app()->instance('currentTenant', $tenant);

    try {
        if (!Schema::connection('landlord')->hasTable('settings')) {
            $this->error('Landlord settings table not found.');
            return 1;
        }

        if (!Schema::connection('tenant')->hasTable('settings')) {
            $landlordDatabase = (string) config('database.connections.landlord.database');
            DB::connection('tenant')->statement("CREATE TABLE `settings` LIKE `{$landlordDatabase}`.`settings`");
            $this->line('Created tenant settings table.');
        }

        $settingsQuery = DB::connection('landlord')->table('settings')->orderBy('id');
        $hasCreatedBy = Schema::connection('landlord')->hasColumn('settings', 'created_by');
        $templateCreatorId = 0;

        if ($hasCreatedBy) {
            $templateCreatorId = (int) ($this->option('template-creator-id') ?: 0);

            if ($templateCreatorId <= 0) {
                $templateCreatorId = (int) (
                    User::on('landlord')
                        ->where('type', 'company')
                        ->orderBy('id')
                        ->value('id') ?? 0
                );
            }
        }

        $now = now();

        $orderStageTemplateCreatorId = $templateCreatorId;
        if (
            Schema::connection('landlord')->hasTable('order_stages')
            && Schema::connection('landlord')->hasColumn('order_stages', 'created_by')
        ) {
            $hasTemplateOrderStages = $orderStageTemplateCreatorId > 0
                ? DB::connection('landlord')
                    ->table('order_stages')
                    ->where('created_by', $orderStageTemplateCreatorId)
                    ->exists()
                : false;

            if (!$hasTemplateOrderStages) {
                $orderStageTemplateCreatorId = (int) (
                    DB::connection('landlord')
                        ->table('order_stages')
                        ->whereNotNull('created_by')
                        ->orderBy('created_by')
                        ->value('created_by') ?? 0
                );
            }
        }

        if (
            $orderStageTemplateCreatorId > 0
            && Schema::connection('tenant')->hasTable('order_stages')
            && Schema::connection('landlord')->hasTable('order_stages')
            && Schema::connection('tenant')->hasColumn('order_stages', 'created_by')
            && Schema::connection('landlord')->hasColumn('order_stages', 'created_by')
        ) {
            $tenantHasOrderColumn = Schema::connection('tenant')->hasColumn('order_stages', 'order');
            $landlordHasOrderColumn = Schema::connection('landlord')->hasColumn('order_stages', 'order');

            $tenantTemplateStages = DB::connection('tenant')
                ->table('order_stages')
                ->where('created_by', $orderStageTemplateCreatorId)
                ->orderBy('id')
                ->get();

            foreach ($tenantTemplateStages as $stage) {
                $creatorStage = DB::connection('tenant')
                    ->table('order_stages')
                    ->where('created_by', $creatorId)
                    ->where('name', $stage->name)
                    ->first();

                if ($creatorStage) {
                    $updates = [];

                    if (empty($creatorStage->color) && !empty($stage->color)) {
                        $updates['color'] = $stage->color;
                    }

                    if (
                        $tenantHasOrderColumn
                        && (int) ($creatorStage->order ?? 0) <= 0
                        && (int) ($stage->order ?? 0) > 0
                    ) {
                        $updates['order'] = (int) $stage->order;
                    }

                    if (!empty($updates)) {
                        DB::connection('tenant')
                            ->table('order_stages')
                            ->where('id', $creatorStage->id)
                            ->update($updates);
                    }

                    DB::connection('tenant')
                        ->table('order_stages')
                        ->where('id', $stage->id)
                        ->delete();

                    continue;
                }

                $updates = ['created_by' => $creatorId];

                if (
                    $tenantHasOrderColumn
                    && (int) ($stage->order ?? 0) <= 0
                ) {
                    $updates['order'] = (int) (
                        DB::connection('tenant')->table('order_stages')->max('order') ?? 0
                    ) + 1;
                }

                DB::connection('tenant')
                    ->table('order_stages')
                    ->where('id', $stage->id)
                    ->update($updates);
            }

            $creatorStageNames = DB::connection('tenant')
                ->table('order_stages')
                ->where('created_by', $creatorId)
                ->pluck('name')
                ->filter()
                ->values()
                ->all();

            $landlordTemplateStages = DB::connection('landlord')
                ->table('order_stages')
                ->where('created_by', $orderStageTemplateCreatorId)
                ->orderBy('id')
                ->get();

            $nextOrder = $tenantHasOrderColumn
                ? (int) (DB::connection('tenant')->table('order_stages')->max('order') ?? 0)
                : 0;

            foreach ($landlordTemplateStages as $stage) {
                if (in_array($stage->name, $creatorStageNames, true)) {
                    continue;
                }

                $payload = [
                    'name' => $stage->name,
                    'color' => $stage->color,
                    'created_by' => $creatorId,
                    'created_at' => $stage->created_at ?? $now,
                    'updated_at' => $now,
                ];

                if ($tenantHasOrderColumn) {
                    $payload['order'] = $landlordHasOrderColumn && (int) ($stage->order ?? 0) > 0
                        ? (int) $stage->order
                        : ++$nextOrder;
                }

                DB::connection('tenant')->table('order_stages')->insert($payload);
                $creatorStageNames[] = $stage->name;
            }

            $canRebuildOrderStageIds = !Schema::connection('tenant')->hasTable('orders')
                || DB::connection('tenant')->table('orders')->count() === 0;

            if ($canRebuildOrderStageIds) {
                $creatorStages = DB::connection('tenant')
                    ->table('order_stages')
                    ->where('created_by', $creatorId)
                    ->when(
                        $tenantHasOrderColumn,
                        static fn ($query) => $query->orderBy('order')->orderBy('id'),
                        static fn ($query) => $query->orderBy('id')
                    )
                    ->get();

                if ($creatorStages->isNotEmpty()) {
                    $normalizedOrderStages = [];
                    $normalizedOrder = 0;

                    foreach ($creatorStages as $stage) {
                        $payload = [
                            'name' => $stage->name,
                            'color' => $stage->color,
                            'created_by' => $creatorId,
                            'created_at' => $stage->created_at ?? $now,
                            'updated_at' => $stage->updated_at ?? $now,
                        ];

                        if ($tenantHasOrderColumn) {
                            $normalizedOrder++;
                            $payload['order'] = (int) ($stage->order ?? 0) > 0
                                ? (int) $stage->order
                                : $normalizedOrder;
                        }

                        $normalizedOrderStages[] = $payload;
                    }

                    DB::connection('tenant')->table('order_stages')->delete();

                    try {
                        DB::connection('tenant')->statement('ALTER TABLE `order_stages` AUTO_INCREMENT = 1');
                    } catch (\Throwable $e) {
                    }

                    DB::connection('tenant')->table('order_stages')->insert($normalizedOrderStages);
                }
            }
        }

        $sourceRows = $settingsQuery->get()->map(static fn ($item) => (array) $item)->all();
        $templateRows = collect($sourceRows)
            ->groupBy(static fn (array $row) => (string) ($row['name'] ?? ''))
            ->map(function ($rows, string $name) use ($hasCreatedBy, $templateCreatorId) {
                if ($name === '') {
                    return null;
                }

                $rows = collect($rows);

                if ($hasCreatedBy && $templateCreatorId > 0) {
                    $templateRow = $rows->first(static fn (array $row) => (int) ($row['created_by'] ?? 0) === $templateCreatorId);
                    if ($templateRow) {
                        return $templateRow;
                    }
                }

                if ($hasCreatedBy) {
                    $globalRow = $rows->first(static fn (array $row) => (int) ($row['created_by'] ?? 0) === 0);
                    if ($globalRow) {
                        return $globalRow;
                    }
                }

                return $rows->first();
            })
            ->filter()
            ->values()
            ->all();
        $inserted = 0;
        foreach ($templateRows as $row) {
            $name = (string) ($row['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $payload = [
                'value' => $row['value'] ?? null,
                'updated_at' => $now,
            ];
            if (Schema::connection('tenant')->hasColumn('settings', 'created_at')) {
                $payload['created_at'] = $row['created_at'] ?? $now;
            }

            $inserted += DB::connection('tenant')->table('settings')->updateOrInsert(
                ['name' => $name, 'created_by' => $creatorId],
                $payload
            ) ? 1 : 0;
        }

        $overrides = [
            'website_name' => null,
            'website_url' => null,
            'website_short_name' => null,
            'email' => null,
            'phone' => null,
            'gst_no' => null,
            'pan_no' => null,
            'website_logo' => null,
            'company_address_id' => null,
            'billing_address_id' => null,
            'is_allowed_discount' => (string) \App\Models\Utility::isDiscountAllowed($creatorId),
        ];

        foreach ($overrides as $name => $value) {
            $payload = [
                'value' => $value,
                'updated_at' => $now,
            ];
            if (Schema::connection('tenant')->hasColumn('settings', 'created_at')) {
                $payload['created_at'] = $now;
            }

            $inserted += DB::connection('tenant')->table('settings')->updateOrInsert(
                ['name' => $name, 'created_by' => $creatorId],
                $payload
            ) ? 1 : 0;
        }

        $this->info("Tenant settings seeded for tenant={$tenant->id}, creator_id={$creatorId}, rows={$inserted}");
        return 0;
    } finally {
        app(TenancyManager::class)->end();
        app()->forgetInstance('currentTenant');
    }
})->purpose('Ensure tenant settings table exists and seed defaults from landlord template');

Artisan::command('tenancy:health {tenant? : Tenant ID or slug (optional)} {--strict : Mark missing required tables as failure}', function () {
    $tenantRef = $this->argument('tenant');
    $query = Tenant::query()->where('is_active', true);

    if (!empty($tenantRef)) {
        if (is_numeric((string) $tenantRef)) {
            $query->where('id', (int) $tenantRef);
        } else {
            $query->where('slug', (string) $tenantRef);
        }
    }

    $tenants = $query->get();
    if ($tenants->isEmpty()) {
        $this->warn('No active tenants found for health check.');
        return;
    }

    $requiredTables = (array) config('tenancy.health_required_tables', []);
    $strict = (bool) $this->option('strict');
    $hasFailure = false;

    foreach ($tenants as $tenant) {
        $this->line("Checking tenant #{$tenant->id} ({$tenant->slug}) db={$tenant->database}");

        try {
            app(TenancyManager::class)->initialize($tenant);
            DB::connection('tenant')->select('SELECT 1');
            $this->info('  DB connection: OK');
        } catch (\Throwable $e) {
            $hasFailure = true;
            $this->error('  DB connection: FAIL - ' . $e->getMessage());
            continue;
        }

        $missing = [];
        foreach ($requiredTables as $table) {
            if (!Schema::connection('tenant')->hasTable($table)) {
                $missing[] = $table;
            }
        }

        if (empty($missing)) {
            $this->info('  Required tables: OK');
        } else {
            $msg = '  Missing tables: ' . implode(', ', $missing);
            if ($strict) {
                $hasFailure = true;
                $this->error($msg);
            } else {
                $this->warn($msg);
            }
        }

        foreach (['leads', 'quotes', 'orders'] as $table) {
            if (Schema::connection('tenant')->hasTable($table)) {
                $count = DB::connection('tenant')->table($table)->count();
                $this->line("  {$table}: {$count}");
            }
        }
    }

    if ($hasFailure) {
        $this->error('Tenancy health check completed with failures.');
    } else {
        $this->info('Tenancy health check passed.');
    }
})->purpose('Check tenant DB connectivity, required tables, and core row counts');

Artisan::command('tenancy:audit {tenant? : Tenant ID or slug (optional)} {--skip-sync : Skip sync-data during provisioning} {--force : Recreate tables before sync}', function () {
    $tenantRef = $this->argument('tenant');
    $tenantArg = is_string($tenantRef) && trim($tenantRef) !== '' ? ['tenant' => trim($tenantRef)] : [];

    $this->info('Running tenancy audit...');
    $this->line('  Step 1/2: tenancy:provision-sales');

    $provisionArgs = $tenantArg;
    if (!$this->option('skip-sync')) {
        $provisionArgs['--sync-data'] = true;
    }
    if ($this->option('force')) {
        $provisionArgs['--force'] = true;
    }

    Artisan::call('tenancy:provision-sales', $provisionArgs);
    $this->line(trim(Artisan::output()));

    $this->line('  Step 2/2: tenancy:health --strict');
    $healthArgs = $tenantArg + ['--strict' => true];
    Artisan::call('tenancy:health', $healthArgs);
    $this->line(trim(Artisan::output()));

    $hasFailure = str_contains((string) Artisan::output(), 'completed with failures.');
    if ($hasFailure) {
        $this->warn('Tenancy audit completed with failures.');
    } else {
        $this->info('Tenancy audit completed successfully.');
    }
})->purpose('Run tenant provisioning (optional sync) and strict health check in one command');

Artisan::command('tenancy:sync-support-data {tenant? : Tenant ID or slug (optional)} {--fresh : Truncate support tables before sync}', function () {
    $tenantRef = $this->argument('tenant');
    $query = Tenant::query()->where('is_active', true);

    if (!empty($tenantRef)) {
        if (is_numeric((string) $tenantRef)) {
            $query->where('id', (int) $tenantRef);
        } else {
            $query->where('slug', (string) $tenantRef);
        }
    }

    $tenants = $query->get();
    if ($tenants->isEmpty()) {
        $this->warn('No active tenants found.');
        return;
    }

    $fresh = (bool) $this->option('fresh');

    foreach ($tenants as $tenant) {
        $this->line("Syncing tenant #{$tenant->id} ({$tenant->slug})...");
        app(TenancyManager::class)->initialize($tenant);

        $creatorIds = User::query()
            ->where('tenant_id', $tenant->id)
            ->where('type', 'company')
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        if (empty($creatorIds)) {
            $creatorIds = User::query()
                ->where('tenant_id', $tenant->id)
                ->whereNotNull('created_by')
                ->distinct()
                ->pluck('created_by')
                ->map(static fn ($id) => (int) $id)
                ->all();
        }

        $tenantUserIds = User::query()
            ->where('tenant_id', $tenant->id)
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        if (empty($creatorIds) && empty($tenantUserIds)) {
            $this->warn("  No tenant users found. Skipped.");
            app(TenancyManager::class)->end();
            continue;
        }

        $syncTable = function (string $table, array $rows) use ($fresh): int {
            if (!Schema::connection('landlord')->hasTable($table) || !Schema::connection('tenant')->hasTable($table)) {
                return 0;
            }
            if (empty($rows)) {
                return 0;
            }

            $tenantColumns = Schema::connection('tenant')->getColumnListing($table);
            $rows = array_values(array_filter(array_map(static function ($row) use ($tenantColumns) {
                $arr = (array) $row;
                $filtered = [];
                foreach ($tenantColumns as $col) {
                    if (array_key_exists($col, $arr)) {
                        $filtered[$col] = $arr[$col];
                    }
                }
                return $filtered;
            }, $rows), static fn ($r) => !empty($r)));

            if (empty($rows)) {
                return 0;
            }

            if ($fresh) {
                DB::connection('tenant')->table($table)->truncate();
            }

            if (in_array('id', $tenantColumns, true)) {
                foreach (array_chunk($rows, 500) as $chunk) {
                    DB::connection('tenant')->table($table)->upsert($chunk, ['id']);
                }
            } else {
                foreach (array_chunk($rows, 500) as $chunk) {
                    DB::connection('tenant')->table($table)->insert($chunk);
                }
            }

            return count($rows);
        };

        $fetchByCreator = function (string $table) use ($creatorIds): array {
            if (empty($creatorIds) || !Schema::connection('landlord')->hasTable($table) || !Schema::connection('landlord')->hasColumn($table, 'created_by')) {
                return [];
            }
            return DB::connection('landlord')->table($table)->whereIn('created_by', $creatorIds)->get()->map(static fn ($x) => (array) $x)->all();
        };

        $stats = [];

        $userRows = [];
        if (Schema::connection('landlord')->hasTable('users')) {
            $userRows = DB::connection('landlord')->table('users')
                ->where('tenant_id', $tenant->id)
                ->get()
                ->map(static fn ($x) => (array) $x)
                ->all();
        }
        $stats['users'] = $syncTable('users', $userRows);

        foreach (['countries', 'states', 'cities'] as $table) {
            $rows = [];
            if (Schema::connection('landlord')->hasTable($table)) {
                $rows = DB::connection('landlord')->table($table)->get()->map(static fn ($x) => (array) $x)->all();
            }
            $stats[$table] = $syncTable($table, $rows);
        }

        $settingsRows = [];
        if (Schema::connection('landlord')->hasTable('settings')) {
            $settingsQuery = DB::connection('landlord')->table('settings');
            if (!empty($creatorIds) && Schema::connection('landlord')->hasColumn('settings', 'created_by')) {
                $settingsQuery->whereIn('created_by', $creatorIds);
            }
            $settingsRows = $settingsQuery->get()->map(static fn ($x) => (array) $x)->all();
        }
        $stats['settings'] = $syncTable('settings', $settingsRows);

        foreach ([
            'departments',
            'employees',
            'holidays',
            'sales_targets',
            'working_hours',
            'leave_rules',
            'leave_types',
            'payments',
            'bank_details',
            'advertisements',
        ] as $table) {
            $stats[$table] = $syncTable($table, $fetchByCreator($table));
        }

        $deviceRows = [];
        if (!empty($tenantUserIds) && Schema::connection('landlord')->hasTable('devices') && Schema::connection('landlord')->hasColumn('devices', 'user_id')) {
            $deviceRows = DB::connection('landlord')->table('devices')->whereIn('user_id', $tenantUserIds)->get()->map(static fn ($x) => (array) $x)->all();
        }
        $stats['devices'] = $syncTable('devices', $deviceRows);

        foreach (['whatsapp_bot_rules', 'whatsapp_bot_knowledge'] as $table) {
            $rows = [];
            if (!empty($creatorIds) && Schema::connection('landlord')->hasTable($table) && Schema::connection('landlord')->hasColumn($table, 'created_by')) {
                $rows = DB::connection('landlord')->table($table)->whereIn('created_by', $creatorIds)->get()->map(static fn ($x) => (array) $x)->all();
            }
            $stats[$table] = $syncTable($table, $rows);
        }

        $deptIds = Schema::connection('tenant')->hasTable('departments')
            ? DB::connection('tenant')->table('departments')->pluck('id')->map(static fn ($id) => (int) $id)->all()
            : [];
        $employeeIds = Schema::connection('tenant')->hasTable('employees')
            ? DB::connection('tenant')->table('employees')->pluck('id')->map(static fn ($id) => (int) $id)->all()
            : [];
        $paymentIds = Schema::connection('tenant')->hasTable('payments')
            ? DB::connection('tenant')->table('payments')->pluck('id')->map(static fn ($id) => (int) $id)->all()
            : [];

        $designationRows = [];
        if (Schema::connection('landlord')->hasTable('designations')) {
            $q = DB::connection('landlord')->table('designations');
            if (!empty($deptIds) && Schema::connection('landlord')->hasColumn('designations', 'department_id')) {
                $q->whereIn('department_id', $deptIds);
            } elseif (!empty($creatorIds) && Schema::connection('landlord')->hasColumn('designations', 'created_by')) {
                $q->whereIn('created_by', $creatorIds);
            } else {
                $q->whereRaw('1=0');
            }
            $designationRows = $q->get()->map(static fn ($x) => (array) $x)->all();
        }
        $stats['designations'] = $syncTable('designations', $designationRows);

        $attendanceRows = [];
        if (!empty($employeeIds) && Schema::connection('landlord')->hasTable('attendances') && Schema::connection('landlord')->hasColumn('attendances', 'employee_id')) {
            $attendanceRows = DB::connection('landlord')->table('attendances')->whereIn('employee_id', $employeeIds)->get()->map(static fn ($x) => (array) $x)->all();
        }
        $stats['attendances'] = $syncTable('attendances', $attendanceRows);

        $leaveRows = [];
        if (!empty($employeeIds) && Schema::connection('landlord')->hasTable('leaves') && Schema::connection('landlord')->hasColumn('leaves', 'employee_id')) {
            $leaveRows = DB::connection('landlord')->table('leaves')->whereIn('employee_id', $employeeIds)->get()->map(static fn ($x) => (array) $x)->all();
        }
        $stats['leaves'] = $syncTable('leaves', $leaveRows);

        $salaryRows = [];
        if (!empty($employeeIds) && Schema::connection('landlord')->hasTable('employee_salary_details') && Schema::connection('landlord')->hasColumn('employee_salary_details', 'employee_id')) {
            $salaryRows = DB::connection('landlord')->table('employee_salary_details')->whereIn('employee_id', $employeeIds)->get()->map(static fn ($x) => (array) $x)->all();
        }
        $stats['employee_salary_details'] = $syncTable('employee_salary_details', $salaryRows);

        $salaryIds = Schema::connection('tenant')->hasTable('employee_salary_details')
            ? DB::connection('tenant')->table('employee_salary_details')->pluck('id')->map(static fn ($id) => (int) $id)->all()
            : [];

        $employeeSalesTargetRows = [];
        if (Schema::connection('landlord')->hasTable('employee_sales_targets')) {
            $q = DB::connection('landlord')->table('employee_sales_targets');
            $hasCondition = false;
            if (!empty($employeeIds) && Schema::connection('landlord')->hasColumn('employee_sales_targets', 'employee_id')) {
                $q->whereIn('employee_id', $employeeIds);
                $hasCondition = true;
            }
            if (!empty($tenantUserIds) && Schema::connection('landlord')->hasColumn('employee_sales_targets', 'user_id')) {
                if ($hasCondition) {
                    $q->orWhereIn('user_id', $tenantUserIds);
                } else {
                    $q->whereIn('user_id', $tenantUserIds);
                    $hasCondition = true;
                }
            }
            if (!$hasCondition) {
                $q->whereRaw('1=0');
            }
            $employeeSalesTargetRows = $q->get()->map(static fn ($x) => (array) $x)->all();
        }
        $stats['employee_sales_targets'] = $syncTable('employee_sales_targets', $employeeSalesTargetRows);

        $employeePaymentHistoryRows = [];
        if (Schema::connection('landlord')->hasTable('employee_payment_histories')) {
            $q = DB::connection('landlord')->table('employee_payment_histories');
            $hasCondition = false;
            if (!empty($salaryIds) && Schema::connection('landlord')->hasColumn('employee_payment_histories', 'employee_salary_detail_id')) {
                $q->whereIn('employee_salary_detail_id', $salaryIds);
                $hasCondition = true;
            }
            if (!empty($paymentIds) && Schema::connection('landlord')->hasColumn('employee_payment_histories', 'payment_id')) {
                if ($hasCondition) {
                    $q->orWhereIn('payment_id', $paymentIds);
                } else {
                    $q->whereIn('payment_id', $paymentIds);
                    $hasCondition = true;
                }
            }
            if (!$hasCondition) {
                $q->whereRaw('1=0');
            }
            $employeePaymentHistoryRows = $q->get()->map(static fn ($x) => (array) $x)->all();
        }
        $stats['employee_payment_histories'] = $syncTable('employee_payment_histories', $employeePaymentHistoryRows);

        $locationRows = [];
        if (!empty($tenantUserIds) && Schema::connection('landlord')->hasTable('location_histories') && Schema::connection('landlord')->hasColumn('location_histories', 'user_id')) {
            $locationRows = DB::connection('landlord')->table('location_histories')->whereIn('user_id', $tenantUserIds)->get()->map(static fn ($x) => (array) $x)->all();
        }
        $stats['location_histories'] = $syncTable('location_histories', $locationRows);

        $loginRows = [];
        if (!empty($tenantUserIds) && Schema::connection('landlord')->hasTable('user_logins') && Schema::connection('landlord')->hasColumn('user_logins', 'user_id')) {
            $loginRows = DB::connection('landlord')->table('user_logins')->whereIn('user_id', $tenantUserIds)->get()->map(static fn ($x) => (array) $x)->all();
        }
        $stats['user_logins'] = $syncTable('user_logins', $loginRows);

        $this->info('  Synced rows: ' . json_encode($stats));
        app(TenancyManager::class)->end();
    }

    $this->info('Support module sync finished.');
})->purpose('Sync HR/accounts support tables from landlord to tenant DB using safe upsert');
