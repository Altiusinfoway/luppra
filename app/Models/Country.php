<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use SoftDeletes, UsesTenantConnection;

    protected $fillable=[
        'code','name','calling_code','is_active'
    ];

     protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function scopeIsActive($query)
    {
        return $query->where('is_active','1');
    }
}
