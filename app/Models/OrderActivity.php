<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class OrderActivity extends Model
{
    use UsesTenantConnection;

    protected $fillable=[
        'user_id','order_id','action','message'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
