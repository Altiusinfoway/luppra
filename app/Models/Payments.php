<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'amount',
        'payment_date',
        'transaction_id',
        'cheque_no',
        'payment_method',
        'description',
        'payment_status',
        'payment_type',
        'bank_detail_id',
        'payment_details',
        'created_by',
        'attachment',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected static array $paymentTypes = [
        'order' => "Order",
        'purchase' => 'Purchase'
    ];

    protected static array $paymentMethods = [
        'bank' => "Bank",
        'upi' => 'UPI',
        'cash' => 'CASH',
        'cheque' => 'Cheque',
    ];

    protected static array $paymentStatus = [
        'unpaid' => 'Unpaid',
        'paid' => 'Paid'
    ];

    public static function getPaymentTypes(): array
    {
        return self::$paymentTypes;
    }

    public static function getPaymentMethods(): array
    {
        return self::$paymentMethods;
    }

    public static function getPaymentStatus(): array
    {
        return self::$paymentStatus;
    }


    protected static array $account_transaction_type_list = [
        'credit' => 'Credit',
        'debit' => 'Debit',
    ];


    public static function getAccountTransactionTypes()
    {
        return self::$account_transaction_type_list;
    }

    public function getPayee()
    {
       if ($this->payee_type === 'employee') {
            return $this->belongsTo(Employee::class, 'payee_id');
        } elseif ($this->type === 'entity') {
            return $this->belongsTo(Entity::class, 'payee_id');
        }
        return null;
    }

    /* public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    } */


    public function getAttachmentAttribute($value)
    {
        if (!empty($value)) {
            return storage_path('uploads/attachment/' . $value);
        } else {
            return '';
        }
    }



}
