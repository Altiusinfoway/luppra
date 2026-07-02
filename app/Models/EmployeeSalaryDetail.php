<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryDetail extends Model
{
    use UsesTenantConnection;

    protected $fillable=[
        'employee_id','final_salary','holiday_json','office_working_hours','office_working_days','emp_per_day_sal','emp_working_hours',
        'generate_salary_cur_date','sales_bonus','payment_status','salary_month','payment_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function getEmployee()
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }

}
