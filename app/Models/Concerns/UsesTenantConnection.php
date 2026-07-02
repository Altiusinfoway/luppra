<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Schema;
use Throwable;

trait UsesTenantConnection
{
    protected static array $tenantTableAvailability = [];

    public function getConnectionName()
    {
        if (!$this->shouldUseTenantConnection()) {
            return parent::getConnectionName();
        }

        return 'tenant';
    }

    protected function shouldUseTenantConnection(): bool
    {
        if (!config('tenancy.enabled', false)) {
            return false;
        }

        if (!app()->bound('currentTenant')) {
            return false;
        }

        try {
            $tenantId = (int) data_get(app('currentTenant'), 'id', 0);
            $key = $tenantId . ':' . $this->getTable();

            if (!array_key_exists($key, static::$tenantTableAvailability)) {
                static::$tenantTableAvailability[$key] = Schema::connection('tenant')->hasTable($this->getTable());
            }

            return static::$tenantTableAvailability[$key];
        } catch (Throwable $e) {
            return false;
        }
    }
}
