<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'name',
        'gst_no',
        'rate',
        'description',
        'is_active',
        'type',
        'specification',
        'company_name',
        'email',
        'contact',
        'avatar',
        'address_id',
        'billing_address_id',
        'shipping_address_id',
        'created_by',
        'contact_json',
        'due_amount',
        'paid_amount',
        'company_adhar_no',
        'company_udhyam_no',
        'lead_type_id',
        'user_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function scopeIsTransport($query)
    {
        return $query->where('type', 'transport')->pluck('name', 'id');
    }

    public function getAddress()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function getBillingAddress()
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    public function getShippingAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function scopeIsCustomer($query)
    {
        return $query->where('type', 'customer')->where('is_active', 1);
    }

    public function scopeIsVendor($query)
    {
        return $query->where('type', 'vendor')->where('is_active', 1);
    }

    public function scopeTransport($query)
    {
        return $query->where('type', 'transport')->where('is_active', 1);
    }

    public function scopeGetCustomer($query)
    {
        return $query->where('type', 'customer');
    }

    public function scopeGetVendor($query)
    {
        return $query->where('type', 'vendor');
    }

    //transport multiple address
    public function getEntityAddress()
    {
        return $this->hasMany(EntityAddress::class, 'entity_id');
    }

    public function scopeGetTransport($query)
    {
        return $query->where('type', 'transport');
    }

    public function getCustomerPhone()
    {
        return $this->hasMany(CustomerPhone::class, 'customer_id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'customer_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function scopeRealCustomers($query)
    {
        return $query->where('type', 'customer')
            ->where(function ($q) {
                $q->whereHas('leads', function ($leadQuery) {
                    $leadQuery->where('is_converted', 1);
                })->orWhereHas('orders');
            });
    }

    public function getLeadType()
    {
        return $this->belongsTo(LeadType::class, 'lead_type_id');
    }
}
