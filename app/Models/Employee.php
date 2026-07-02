<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes, UsesTenantConnection;

    protected $fillable = [
        'user_id',
        'name',
        'dob',
        'gender',
        'phone',
        'address',
        'email',
        'password',
        'employee_id',
        'branch_id',
        'department_id',
        'designation_id',
        'company_doj',
        'documents',
        'account_holder_name',
        'account_number',
        'bank_name',
        'bank_identifier_code',
        'branch_location',
        'tax_payer_id',
        'salary_type',
        'biometric_emp_id',
        'account',
        'salary',
        'created_by',
        'is_active',
        'incentive',
        'sales_target_id',
        'no_of_leave',
        'remaining_leave',
        'lead_fetch_limit',
    ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];
    public function documents()
    {
        return $this->hasMany('App\Models\EmployeeDocument', 'employee_id', 'employee_id')->get();
    }

    public static function employee_id()
    {
        $employee = Employee::latest()->first();

        return !empty($employee) ? $employee->id + 1 : 1;
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public static function employee_salary($salary)
    {
        $employee = Employee::where("salary", $salary)->first();
        if ($employee->salary == '0' || $employee->salary == '0.0') {
            return "-";
        } else {
            return $employee->salary;
        }
    }

    public function departments()
    {
        return $this->belongsTo(Department::class,'department_id');
    }

    public function getUser()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function SalesTarget()
    {
        return $this->belongsTo(SalesTarget::class,'sales_target_id');
    }

    public function getDesignation()
    {
        return $this->belongsTo(Designation::class,'designation_id');
    }





}
