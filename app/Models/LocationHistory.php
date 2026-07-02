<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class LocationHistory extends Model
{
     use UsesTenantConnection;

     protected $fillable=['user_id','latitude','longitude','date_time'];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
