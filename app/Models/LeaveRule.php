<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class LeaveRule extends Model
{
    use UsesTenantConnection;

    protected $fillable=['total_leave'];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
