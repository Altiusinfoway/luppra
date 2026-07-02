<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class LeadCall extends Model
{
    use UsesTenantConnection;

    protected $fillable=[
        'lead_id','audio','user_id','date_time','status','created_by','call_duration'
    ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function getAudioAttribute($value)
    {
        return asset('storage/uploads/lead_call/' . $value);
    }


}
