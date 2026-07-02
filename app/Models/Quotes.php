<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Quotes extends Model
{
    use UsesTenantConnection;


    protected $fillable = [
        'code',
        'customer_type',
        'lead_id',
        'date',
        'status',
        'transport_id',
        'gst',
        'grand_total',
        'is_advance_payment',
        'payment_after_days',
        'advance_payment',
        'is_final',
        'created_by',
        'notes',
        'quote_invoice',
        'customer_id',
        'where_from',
        'tax_detail_json',
        'total_tax_sum',
        'user_id',
    ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quote) {
            $latestId = optional(Quotes::latest('id')->first())->id ?? 0;
            $quote->code = \App\Models\Utility::getQuotePrefix() . str_pad($latestId + 1, 6, '0', STR_PAD_LEFT);
        });
    }

    public function lead()
    {
        return $this->hasOne('App\Models\Lead', 'id', 'lead_id');
    }

     public function quoteProducts()
    {
        return $this->hasMany(QuoteProducts::class, 'quote_id', 'id');
    }

     public function customerPhone()
    {
        return $this->belongsTo(CustomerPhone::class, 'customer_id','customer_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function customer()
    {
        return $this->belongsTo(Entity::class, 'customer_id');
    }

    public function get_user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

      public function get_transport()
    {
        return $this->belongsTo(Entity::class, 'transport_id');
    }

}
