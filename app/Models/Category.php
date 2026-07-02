<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use UsesTenantConnection;

    protected $fillable = ["parent_id", "name"];


    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

      protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
