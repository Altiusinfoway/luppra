<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'code',
        'price',
        'billing_cycle',
        'trial_days',
        'user_limit',
        'whatsapp_limit',
        'modules',
        'is_active',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'modules' => 'array',
        'is_active' => 'boolean',
    ];
}
