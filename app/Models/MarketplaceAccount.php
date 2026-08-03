<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketplaceAccount extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'created_by',
        'platform',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
    ];

    public function listings(): HasMany
    {
        return $this->hasMany(MarketplaceListing::class, 'marketplace_account_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return ucfirst((string) $this->platform) . ' / ' . $this->name;
    }
}
