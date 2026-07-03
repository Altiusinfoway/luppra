<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteSignup extends Model
{
    protected $fillable = [
        'plan_id',
        'name',
        'email',
        'phone',
        'company_name',
        'amount',
        'status',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
    ];
}
