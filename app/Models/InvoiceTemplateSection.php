<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceTemplateSection extends Model
{
    protected $connection = 'landlord';

    protected $fillable = [
        'invoice_template_id',
        'section_key',
        'section_label',
        'is_visible',
        'sort_order',
        'settings_json',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
        'settings_json' => 'array',
    ];

    public function template()
    {
        return $this->belongsTo(InvoiceTemplate::class, 'invoice_template_id');
    }
}
