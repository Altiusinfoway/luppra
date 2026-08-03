<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class MarketplaceListing extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'product_id',
        'created_by',
        'platform',
        'platform_sku',
        'marketplace_item_id',
        'listing_title',
        'pack_size',
        'selling_price',
        'mrp',
        'base_price',
        'listing_status',
        'fulfillment_type',
        'allocated_stock',
        'reserved_stock',
        'external_orders_count',
        'external_sold_qty',
        'external_revenue',
        'external_last_synced_at',
        'external_sync_note',
    ];

    protected $casts = [
        'selling_price' => 'float',
        'mrp' => 'float',
        'base_price' => 'float',
        'allocated_stock' => 'int',
        'reserved_stock' => 'int',
        'external_orders_count' => 'int',
        'external_sold_qty' => 'float',
        'external_revenue' => 'float',
        'external_last_synced_at' => 'datetime',
    ];

    protected $appends = [
        'available_stock',
        'display_name',
    ];

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function getAvailableStockAttribute(): int
    {
        $masterStock = (int) ($this->product?->stock_qty ?? 0);
        $allocated = $this->allocated_stock;
        $reserved = max((int) $this->reserved_stock, 0);

        if ($allocated === null) {
            return max($masterStock - $reserved, 0);
        }

        return max(min($masterStock, (int) $allocated) - $reserved, 0);
    }

    public function getDisplayNameAttribute(): string
    {
        $title = trim((string) ($this->listing_title ?? ''));
        $sku = trim((string) ($this->platform_sku ?? ''));
        $platform = ucfirst((string) ($this->platform ?? ''));

        return trim($platform . ' - ' . ($sku !== '' ? $sku : $title));
    }
}
