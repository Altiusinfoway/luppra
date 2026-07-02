<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Taxes extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'name',
        'percentage',
        'created_by'
    ];
}
