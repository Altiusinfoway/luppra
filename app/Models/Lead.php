<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use League\CommonMark\Extension\SmartPunct\Quote;
use Masterminds\HTML5\Entities;

class Lead extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'name',
        'email',
        'subject',
        'user_id',
        'stage_id',
        'sources',
        'products',
        'notes',
        'labels',
        'order',
        'created_by',
        'is_active',
        'date',
        'phone',
        'next_contact_date',
        'is_duplicate',
        'from_lead_id',
        'gst_no',
        'won_date',
        'customer_id',
        'lead_id',
        'lead_type_id',
    ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];


    public function stage()
    {
        return $this->hasOne('App\Models\LeadStage', 'id', 'stage_id')->withTrashed();
    }

    public function sources()
    {
        if($this->sources)
        {
            return LeadSource::withTrashed()->whereIn('id', explode(',', $this->sources))->get();
        }

        return collect();
    }

     // Return origanal product name and price.
    public function products()
    {
        if($this->products)
        {
            return Products::whereIn('id', explode(',', $this->products))->get();
        }

        return [];
    }


    // Return product name, price, qty entered on lead.
    // public function product()
    // {

    //     return $this->belongsToMany('App\Models\Products', 'lead_products', 'lead_id', 'product_id')->withPivot(['id','price', 'qty']);

    // }

     // Return product name, price, qty entered on lead.
    public function product()
    {
        return $this->belongsToMany('App\Models\Products', 'lead_products', 'lead_id', 'product_id')->withPivot(['id','price', 'qty', 'unit_id']);
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'user_leads', 'lead_id', 'user_id');
    }

    public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }

    public function scopeIsConverted($query)
    {
        return $query->where('is_converted', 1);
    }

    public function scopeIsNotConverted($query)
    {
        return $query->where('is_converted', 0);
    }

    public function customer()
    {
        return $this->belongsTo(Entity::class, 'customer_id');
    }

    public function customerPhone()
    {
        return $this->belongsTo(CustomerPhone::class, 'customer_id','customer_id');
    }

     public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

     public function getLeadType()
    {
        return $this->belongsTo(LeadType::class,'lead_type_id');
    }

    public function getSourceListAttribute()
    {
        if (!$this->sources) {
            return [];
        }

        $ids = explode(',', $this->sources);

        return LeadSource::withTrashed()->whereIn('id', $ids)->select('id', 'name')->get();
    }

    public function getLeadChat()
    {
        return $this->hasMany(LeadChat::class, 'lead_id', 'id');
    }

    public function getCustomerAllPhone()
    {
         return $this->hasMany(CustomerPhone::class, 'customer_id', 'customer_id');
    }

    public function getLeadCall()
    {
        return $this->hasMany(LeadCall::class, 'lead_id', 'id');
    }

     public function getQuoteAll()
    {
        return $this->hasMany(Quotes::class, 'lead_id', 'id');
    }

     public function getLeadProduct()
    {
        return $this->hasMany(LeadProducts::class, 'lead_id','id');
    }

    public function getLeadStatus()
    {
        return $this->belongsTo(LeadStage::class, 'stage_id')->withTrashed();
    }

    public function getLeadActivity()
    {
        return $this->hasMany(LeadActivity::class, 'lead_id', 'id');
    }

}
