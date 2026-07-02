<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class CustomerPriceHistory extends Model
{
    use UsesTenantConnection;

     protected $fillable=[
        'customer_id','product_id','price','discount',
    ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

}
