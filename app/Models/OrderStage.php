<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class OrderStage extends Model
{
    use UsesTenantConnection;

    protected $fillable=[
        'name','color','created_by','order'
    ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
