<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use UsesTenantConnection;

    protected $fillable=[
        'name','email','phone','address_line_1','address_line_2','city','zipcode','state','country',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];


     public function get_country()
    {
        return $this->belongsTo(Country::class,'country');
    }

    public function get_state()
    {
        return $this->belongsTo(State::class,'state');
    }

    public function get_city()
    {
        return $this->belongsTo(City::class,'city');
    }
}
