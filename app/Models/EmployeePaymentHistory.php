<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class EmployeePaymentHistory extends Model
{
    use UsesTenantConnection;

    protected $fillable=[
        'employee_salary_detail_id','payment_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function getEmployeeSalaryDetail()
    {
        return $this->belongsTo(EmployeeSalaryDetail::class,'employee_salary_detail_id');
    }

    public function getPayment()
    {
        return $this->belongsTo(Payments::class,'payment_id');
    }
}
