<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenancyManager
{
    protected ?Tenant $tenant = null;

    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function initialize(Tenant $tenant): void
    {
        $this->tenant = $tenant;

        Config::set('database.connections.tenant.host', $tenant->db_host ?: env('TENANT_DB_HOST', env('DB_HOST')));
        Config::set('database.connections.tenant.port', $tenant->db_port ?: env('TENANT_DB_PORT', env('DB_PORT', 3306)));
        Config::set('database.connections.tenant.database', $tenant->database);
        Config::set('database.connections.tenant.username', $tenant->db_username ?: env('TENANT_DB_USERNAME', env('DB_USERNAME')));
        Config::set('database.connections.tenant.password', $tenant->db_password ?: env('TENANT_DB_PASSWORD', env('DB_PASSWORD')));

        DB::purge('tenant');
        DB::reconnect('tenant');

        if ((bool) config('tenancy.set_default_connection', false) === true) {
            DB::setDefaultConnection('tenant');
        }
    }

    public function end(): void
    {
        if ((bool) config('tenancy.set_default_connection', false) === true) {
            DB::setDefaultConnection(config('database.default'));
        }

        $this->tenant = null;
    }
}

