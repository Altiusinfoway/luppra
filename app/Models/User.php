<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Traits\HasRoles;
use Throwable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, UsesTenantConnection;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    // protected $appends = ['profile'];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'type',
        'storage_limit',
        'avatar',
        'lang',
        'mode',
        'delete_status',
        'plan',
        'email_verified_at',
        'plan_expire_date',
        'requested_plan',
        'is_active',
        'referral_code',
        'used_referral_code',
        'commission_amount',
        'paid_amount',
        'is_enable_login',
        'last_login_at',
        'created_by',
        'tenant_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    /* protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    } */

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public $settings;

    public function getAvatarAttribute($value)
    {
        if (!empty($value)) {
            return asset('storage/uploads/avatar/' . $value);
        } else {
            return '';
        }
    }


    public function authId()
    {
        return $this->id;
    }

    public function getConnectionName()
    {
        if ($this->type === 'super admin') {
            return 'landlord';
        }

        $currentTenantId = app()->bound('currentTenant')
            ? (int) data_get(app('currentTenant'), 'id', 0)
            : 0;

        if ($currentTenantId > 0) {
            $userTenantId = (int) ($this->tenant_id ?? 0);

            if ($userTenantId === 0 || $userTenantId === $currentTenantId) {
                if ($this->shouldUseTenantConnectionForUser()) {
                    return 'tenant';
                }
            }
        }

        if ((int) ($this->tenant_id ?? 0) <= 0) {
            return 'landlord';
        }

        if ($this->shouldUseTenantConnectionForUser()) {
            return 'tenant';
        }

        return 'landlord';
    }

    private function shouldUseTenantConnectionForUser(): bool
    {
        if (!config('tenancy.enabled', false)) {
            return false;
        }

        if (!app()->bound('currentTenant')) {
            return false;
        }

        try {
            return Schema::connection('tenant')->hasTable($this->getTable());
        } catch (Throwable $e) {
            return false;
        }
    }

    public function creatorId()
    {
        if ($this->type == 'company' || $this->type == 'super admin') {
            return $this->id;
        } else {
            return $this->created_by;
        }
    }

    public function ownerId()
    {
        if ($this->type == 'company' || $this->type == 'super admin') {
            return $this->id;
        } else {
            return $this->created_by;
        }
    }

    public function ownerDetails()
    {

        if ($this->type == 'company' || $this->type == 'super admin') {
            return User::where('id', $this->id)->first();
        } else {
            return User::where('id', $this->created_by)->first();
        }
    }

    public function currentLanguage()
    {
        return $this->lang;
    }

    public function scopeIsdeleted($query)
    {
        return $query->where('delete_status', '!=', 0);
    }

    public function scopeEmployees($query)
    {
        return $query->where('type',"Employee");
    }

    public function employee()
    {
        return $this->hasOne('App\Models\Employee', 'user_id', 'id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public static $predefineUserTypeList = [
        'HRM' => 'HRM',
        'Sales' => 'Sales',
        'Employee' => 'Employee',
        'accountant'=>'accountant'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

}
