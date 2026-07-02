<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use UsesTenantConnection;

    protected $fillable=['name'];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
