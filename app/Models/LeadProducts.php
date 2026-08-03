<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class LeadProducts extends Model
{
    use UsesTenantConnection;


    protected $fillable = [
        'lead_id',
        'product_id',
        'marketplace_listing_id',
        'price',
        'qty',
        'created_by',
        'unit_id'
    ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function products()
    {
        if($this->product_id)
        {
            return Products::where('id', $this->product_id)->get();
        }

        return [];
    }

    public function getProduct()
    {
        return $this->belongsTo(Products::class, 'product_id','id');
    }

    public function marketplaceListing()
    {
        return $this->belongsTo(MarketplaceListing::class, 'marketplace_listing_id');
    }

     public function getLead()
    {
        return $this->belongsTo(Lead::class, 'lead_id','id');
    }

    public function getUnit()
    {
        return $this->belongsTo(Units::class,'unit_id','id');
    }


}
