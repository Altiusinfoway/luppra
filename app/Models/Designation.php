<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use UsesTenantConnection;

    protected $fillable=[
        'department_id','name',
    ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function departments()
    {
        return $this->belongsTo(Department::class,'department_id');
    }
}
