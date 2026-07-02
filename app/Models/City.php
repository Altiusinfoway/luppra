<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use SoftDeletes, UsesTenantConnection;

    protected $fillable=[
        'state_id','name','is_active',
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

    public function getState()
    {
        return $this->belongsTo(State::class,'state_id');
    }
}
