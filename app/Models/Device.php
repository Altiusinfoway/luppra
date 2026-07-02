<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use App\Autoload\HasUid;
use Illuminate\Support\Str;

class Device extends Model
{
    use HasUid, UsesTenantConnection;

    protected $fillable = [
        'uuid','user_id','is_lead_mobile_number','name','phone','user_name','qr','meta','hook_url','status'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function($model){
            $model->uuid = Str::uuid()->toString();
        });
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function whatsappSessionId(): string
    {
        return 'device_' . $this->uuid;
    }

    public function whatsappLegacySessionId(): string
    {
        return 'device_' . $this->id;
    }

}
