<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class LeadActivity extends Model
{
    use UsesTenantConnection;

    protected $fillable=[
        'lead_id','user_id','desc','date_time','action',
    ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function users()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
