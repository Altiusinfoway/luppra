<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantAuditLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'event',
        'message',
        'ip_address',
        'user_agent',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
