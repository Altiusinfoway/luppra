<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class EntityAddress extends Model
{
    use UsesTenantConnection;

    protected $fillable=[
        'entity_id','state','city','country','address_line_1','address_line_2',
        'zipcode',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
