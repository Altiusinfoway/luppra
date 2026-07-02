<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class GstSlabMaster extends Model
{
    use UsesTenantConnection;

     protected $fillable=[
        'rate','created_by'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
