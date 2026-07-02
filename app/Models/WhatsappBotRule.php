<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class WhatsappBotRule extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'created_by',
        'lead_stage_id',
        'mode',
        'prompt_hint',
        'template_text',
        'is_active',
        'sort_order',
    ];

    public function stage()
    {
        return $this->belongsTo(LeadStage::class, 'lead_stage_id')->withTrashed();
    }
}
