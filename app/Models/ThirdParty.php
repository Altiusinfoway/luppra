<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class ThirdParty extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'name','value',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
