<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class LeadChat extends Model
{
    use UsesTenantConnection;

    protected $fillable=[
        'lead_id','chat','stage_id','next_date','created_by',
    ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];


    public function getLeadDetail()
    {
        return $this->belongsTo(Lead::class, 'lead_id','id');
    }

    public function getLeadStatus()
    {
        return $this->belongsTo(LeadStage::class, 'stage_id','id')->withTrashed();
    }

     public function getUser()
    {
        return $this->belongsTo(User::class, 'created_by','id');
    }

}
