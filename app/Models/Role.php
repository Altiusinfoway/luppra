<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;

class Role extends \Spatie\Permission\Models\Role
{
    use UsesTenantConnection;
}
