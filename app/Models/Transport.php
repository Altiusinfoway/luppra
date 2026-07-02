<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Transport extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'name','gst_no','state','address','city','specification','is_active'
    ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
