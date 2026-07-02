<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
   use UsesTenantConnection;

   protected $fillable=[
        'order_id','payment_id','payment_status','amount'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
