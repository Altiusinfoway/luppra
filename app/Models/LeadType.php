<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class LeadType extends Model
{
    use UsesTenantConnection;

     protected $fillable=[
        'name','created_by',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
