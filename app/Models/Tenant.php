<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $connection = 'landlord';

    protected $fillable = [
        'name',
        'slug',
        'database',
        'db_host',
        'db_port',
        'db_username',
        'db_password',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function domains()
    {
        return $this->hasMany(TenantDomain::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function currentSubscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }
}
