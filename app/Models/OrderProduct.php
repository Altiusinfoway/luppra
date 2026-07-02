<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use UsesTenantConnection;

    protected $fillable=[
        'order_id','product_id','qty','price','discount','total','created_by',
        'short_notes','unit_id','tax'
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
