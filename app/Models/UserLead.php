<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class UserLead extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'user_id',
        'lead_id',
    ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function getLeadUser()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}
