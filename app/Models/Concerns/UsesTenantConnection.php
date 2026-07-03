<?php

namespace App\Models\Concerns;

trait UsesTenantConnection
{
    public function getConnectionName()
    {
        return parent::getConnectionName();
    }

    protected function shouldUseTenantConnection(): bool
    {
        return false;
    }
}
