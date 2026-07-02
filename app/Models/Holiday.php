<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use UsesTenantConnection;

    protected $fillable=[
        'name','start_date','end_date','description','is_active'
    ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];

}
