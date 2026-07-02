<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class CustomerPhone extends Model
{
    use UsesTenantConnection;

    protected $fillable=[
        'customer_id','phone','is_primary','is_secondary','is_whatsapp',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function customer()
    {
        return $this->belongsTo(Entity::class, 'customer_id');
    }

}
