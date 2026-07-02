<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;


class TenantMigration extends Command
{
    protected $signature = 'tenant:migrate-one
                            {tenant_id}
                            {path}';

    protected $description = 'Run one migration for one tenant';

    public function handle()
    {
        $tenantId = $this->argument('tenant_id');
        $path = $this->argument('path');

        // Read tenant from landlord database
        $tenant = DB::connection('landlord')
            ->table('tenants')
            ->where('id', $tenantId)
            ->first();

        if (!$tenant) {
            $this->error("Tenant not found.");
            return;
        }

        Config::set('database.connections.tenant.host', $tenant->db_host);
        Config::set('database.connections.tenant.port', $tenant->db_port);
        Config::set('database.connections.tenant.database', $tenant->database);
        Config::set('database.connections.tenant.username', $tenant->db_username);
        Config::set('database.connections.tenant.password', $tenant->db_password);

        DB::purge('tenant');
        DB::reconnect('tenant');

        $this->info("Connected Database : ".$tenant->database);

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => $path,
            '--force' => true,
        ]);

        $this->line(Artisan::output());

        $this->info('Migration Completed');
    }
}
