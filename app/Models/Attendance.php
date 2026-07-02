<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use UsesTenantConnection;

    protected $fillable=[
        'employee_id','date','check_in','check_out','total_hours','note','leave_reason','is_present','location'
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
