<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use UsesTenantConnection;

    protected $fillable=[
        'name','amount','description'
    ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
