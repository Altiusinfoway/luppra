<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStockActivity extends Model
{
    protected $fillable=[
        'product_id',
        'date_time',
        'message',
        'user_id',
    ];

    public function created_user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Products::class,'product_id');
    }
}
