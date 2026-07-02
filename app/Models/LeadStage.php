<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadStage extends Model
{
    use SoftDeletes, UsesTenantConnection;

    protected $fillable = [
        'name',
        'color',
        'created_by',
        'order',
        'is_editable',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'is_editable' => 'integer',
    ];

    public function lead()
    {
        if(\Auth::user()->type=='company'){
            return Lead::select('leads.*')->where('leads.created_by', '=', \Auth::user()->creatorId())->where('leads.stage_id', '=', $this->id)->orderBy('leads.order')->get();
        }else{
            return Lead::select('leads.*')->join('user_leads', 'user_leads.lead_id', '=', 'leads.id')->where('user_leads.user_id', '=', \Auth::user()->id)->where('leads.stage_id', '=', $this->id)->orderBy('leads.order')->get();

        }

    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'stage_id', 'id');
    }
}
