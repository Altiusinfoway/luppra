<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalesTarget extends Model
{
     use UsesTenantConnection;

     protected $fillable=[
        'user_id','employee_id','current_month_date','sales_target_id','achieve_amount','incentive','is_eligible_target'];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

     public function getUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

     public function getSalesEmployeeTarget()
    {
        return $this->belongsTo(SalesTarget::class, 'sales_target_id');
    }


}
