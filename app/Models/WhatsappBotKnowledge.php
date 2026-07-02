<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class WhatsappBotKnowledge extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'created_by',
        'title',
        'keywords',
        'answer',
        'is_active',
        'sort_order',
    ];
}
