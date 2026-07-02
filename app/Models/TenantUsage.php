<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantUsage extends Model
{
    protected $connection = 'landlord';

    protected $fillable = [
        'tenant_id',
        'metric',
        'period_key',
        'value',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
