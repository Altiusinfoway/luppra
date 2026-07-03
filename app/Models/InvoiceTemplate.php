<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceTemplate extends Model
{
    protected $fillable = [
        'name',
        'code',
        'paper_size',
        'orientation',
        'view_name',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function sections()
    {
        return $this->hasMany(InvoiceTemplateSection::class, 'invoice_template_id')->orderBy('sort_order');
    }
}
