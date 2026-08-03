<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class QuoteProducts extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'quote_id',
        'product_id',
        'marketplace_listing_id',
        'qty',
        'mrp',
        'discount',
        'price',
        'total',
        'created_by',
        'unit_id',
        'short_notes',
        'tax',
    ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];

     public function getProduct()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function marketplaceListing()
    {
        return $this->belongsTo(MarketplaceListing::class, 'marketplace_listing_id');
    }
}
