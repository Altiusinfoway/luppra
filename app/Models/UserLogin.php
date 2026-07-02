<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class UserLogin extends Model
{
    use UsesTenantConnection;

    protected $fillable=[
        'user_id','login_date_time','logout_date_time','is_web_app_login','browser_detail','ip_number'
    ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
