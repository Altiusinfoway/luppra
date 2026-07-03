<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;

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
    }

    public function end(): void
    {
        $this->tenant = null;
    }
}
