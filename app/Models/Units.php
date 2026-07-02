<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Units extends Model
{
    use UsesTenantConnection;


    protected $fillable = [
        'name','base','type_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

}
