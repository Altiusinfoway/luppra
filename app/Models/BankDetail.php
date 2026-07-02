<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class BankDetail extends Model
{
    use UsesTenantConnection;

    protected $fillable=[
        'account_holder_name','account_no','account_type','bank_name','branch_name','ifsc_code',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected static array $account_type_list = [
        'current account' => 'Current Account',
        'saving account' => 'Saving Account',
    ];


     public static function getAccountTypes()
    {
        return self::$account_type_list;
    }
}
