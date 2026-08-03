<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use UsesTenantConnection;

    protected $fillable=[
        'order_number','order_source_type','external_order_id','external_order_reference','customer_type','customer_id','date','status','transport_id','gst',
        'grand_total','is_advance_payment','payment_after_days','advance_payment','is_final',
        'notes','quote_invoice','created_by','tax_detail_json','total_tax_sum',
        'remaining_payment','payment_status','bill_number','lr_number','no_article','transport_charge','order_invoice','user_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            $latestId = optional(Order::latest('id')->first())->id ?? 0;
            $order->order_number = \App\Models\Utility::getOrderPrefix(). str_pad($latestId + 1, 6, '0', STR_PAD_LEFT);
        });
    }


    // Unpaid
    public function scopeUnpaid($query)
    {
        return $query->where('payment_status','unpaid');
    }

    // Paid
    public function scopePaid($query)
    {
        return $query->where('payment_status','paid');
    }

    // Get Customer
    public function customer()
    {
        return $this->hasOne('App\Models\Entity', 'id', 'customer_id');
    }

    public function getCustomer()
    {
        return $this->belongsTo(Entity::class,'customer_id');
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class, 'order_id');
    }

    public function getTransport()
    {
        return $this->belongsTo(Entity::class,'transport_id');
    }

    public function Orderstatus()
    {
        return $this->belongsTo(OrderStage::class,'status','id');
    }

    public function customerPhone()
    {
        return $this->belongsTo(CustomerPhone::class, 'customer_id','customer_id');
    }

    /**
     * Allow route-model binding by numeric id, ORDER-* number, or INVOICE-* number.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $query = $this->newQuery();

        if (is_numeric($value)) {
            return $query->whereKey((int) $value)->firstOrFail();
        }

        $rawValue = (string) $value;
        $normalizedOrderNumber = str_replace('INVOICE-', 'ORDER-', $rawValue);

        if (!empty($field)) {
            return $query->where($field, $rawValue)->firstOrFail();
        }

        return $query->where('order_number', $rawValue)
            ->orWhere('order_number', $normalizedOrderNumber)
            ->firstOrFail();
    }
}
