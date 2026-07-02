<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use UsesTenantConnection;

    protected $fillable=[
        'employee_id','start_date','end_date','total_days','leave_type','hours_leave','reason','status','remark',
    ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function employees()
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }

    public function get_leave_type()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type', 'id');
    }
}
