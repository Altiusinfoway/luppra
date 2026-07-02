<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class WorkingHours extends Model
{
    use UsesTenantConnection;

    protected $fillable=['day','start_time','end_time'];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
