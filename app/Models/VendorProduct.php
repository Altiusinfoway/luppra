<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class VendorProduct extends Model
{
    use UsesTenantConnection;

    protected $fillable=[
        'vendor_id','product_id','price','type','unit_id',
    ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
