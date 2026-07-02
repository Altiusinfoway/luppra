<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;

class Permission extends \Spatie\Permission\Models\Permission
{
    use UsesTenantConnection;
}
