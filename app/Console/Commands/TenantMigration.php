<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TenantMigration extends Command
{
    protected $signature = 'tenant:migrate-one
                            {tenant_id}
                            {path}';

    protected $description = 'Run one migration for one tenant';

    public function handle()
    {
        $this->warn('Tenant database migrations are disabled in single-company mode. Use php artisan migrate on the default database.');
        return self::SUCCESS;
    }
}
