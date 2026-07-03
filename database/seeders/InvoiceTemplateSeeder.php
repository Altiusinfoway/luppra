<?php

namespace Database\Seeders;

use App\Models\InvoiceTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Classic',
                'code' => 'classic',
                'paper_size' => 'A4',
                'orientation' => 'portrait',
                'view_name' => 'invoice_templates.render.pdf',
                'is_active' => true,
                'is_default' => true,
                'sections' => [
                    ['section_key' => 'header', 'section_label' => 'Header', 'is_visible' => true, 'sort_order' => 10, 'settings_json' => ['title' => 'TAX INVOICE', 'style' => 'classic']],
                    ['section_key' => 'company_info', 'section_label' => 'Company Info', 'is_visible' => true, 'sort_order' => 20, 'settings_json' => ['show_logo' => true, 'show_gst' => true]],
                    ['section_key' => 'invoice_meta', 'section_label' => 'Invoice Meta', 'is_visible' => true, 'sort_order' => 30, 'settings_json' => ['fields' => ['invoice_number', 'invoice_date']]],
                    ['section_key' => 'customer_info', 'section_label' => 'Customer Info', 'is_visible' => true, 'sort_order' => 40, 'settings_json' => ['show_shipping' => true, 'show_billing' => true]],
                    ['section_key' => 'items_table', 'section_label' => 'Items Table', 'is_visible' => true, 'sort_order' => 50, 'settings_json' => ['show_hsn' => true, 'show_tax_columns' => true, 'show_discount_column' => true]],
                    ['section_key' => 'tax_summary', 'section_label' => 'Tax Summary', 'is_visible' => true, 'sort_order' => 60, 'settings_json' => ['layout' => 'detailed']],
                    ['section_key' => 'totals', 'section_label' => 'Totals', 'is_visible' => true, 'sort_order' => 70, 'settings_json' => ['show_transport_charge' => true, 'highlight_grand_total' => true]],
                    ['section_key' => 'amount_in_words', 'section_label' => 'Amount In Words', 'is_visible' => true, 'sort_order' => 80, 'settings_json' => ['label' => 'Amount in Words']],
                    ['section_key' => 'bank_details', 'section_label' => 'Bank Details', 'is_visible' => true, 'sort_order' => 90, 'settings_json' => ['show_account_name' => true, 'show_account_number' => true, 'show_ifsc' => true]],
                    ['section_key' => 'signature', 'section_label' => 'Signature', 'is_visible' => true, 'sort_order' => 100, 'settings_json' => ['label' => 'Authorised Signatory']],
                    ['section_key' => 'terms_conditions', 'section_label' => 'Terms & Conditions', 'is_visible' => true, 'sort_order' => 110, 'settings_json' => ['label' => 'Terms & Conditions', 'show_notes_block' => true]],
                ],
            ],
            [
                'name' => 'Modern',
                'code' => 'modern',
                'paper_size' => 'A4',
                'orientation' => 'portrait',
                'view_name' => 'invoice_templates.render.pdf',
                'is_active' => true,
                'is_default' => false,
                'sections' => [
                    ['section_key' => 'header', 'section_label' => 'Header', 'is_visible' => true, 'sort_order' => 10, 'settings_json' => ['title' => 'TAX INVOICE', 'style' => 'modern']],
                    ['section_key' => 'company_info', 'section_label' => 'Company Info', 'is_visible' => true, 'sort_order' => 20, 'settings_json' => ['show_logo' => true, 'show_gst' => true]],
                    ['section_key' => 'invoice_meta', 'section_label' => 'Invoice Meta', 'is_visible' => true, 'sort_order' => 30, 'settings_json' => ['fields' => ['invoice_number', 'invoice_date', 'transport']]],
                    ['section_key' => 'customer_info', 'section_label' => 'Customer Info', 'is_visible' => true, 'sort_order' => 40, 'settings_json' => ['show_shipping' => true, 'show_billing' => true]],
                    ['section_key' => 'items_table', 'section_label' => 'Items Table', 'is_visible' => true, 'sort_order' => 50, 'settings_json' => ['show_hsn' => false, 'show_tax_columns' => true, 'show_discount_column' => true]],
                    ['section_key' => 'tax_summary', 'section_label' => 'Tax Summary', 'is_visible' => true, 'sort_order' => 60, 'settings_json' => ['layout' => 'stacked']],
                    ['section_key' => 'totals', 'section_label' => 'Totals', 'is_visible' => true, 'sort_order' => 70, 'settings_json' => ['show_transport_charge' => true, 'highlight_grand_total' => true]],
                    ['section_key' => 'amount_in_words', 'section_label' => 'Amount In Words', 'is_visible' => true, 'sort_order' => 80, 'settings_json' => ['label' => 'Invoice Amount in Words']],
                    ['section_key' => 'bank_details', 'section_label' => 'Bank Details', 'is_visible' => true, 'sort_order' => 90, 'settings_json' => ['show_account_name' => true, 'show_account_number' => true, 'show_ifsc' => true]],
                    ['section_key' => 'signature', 'section_label' => 'Signature', 'is_visible' => true, 'sort_order' => 100, 'settings_json' => ['label' => 'For Authorised Use Only']],
                    ['section_key' => 'terms_conditions', 'section_label' => 'Terms & Conditions', 'is_visible' => true, 'sort_order' => 110, 'settings_json' => ['label' => 'Terms & Conditions', 'show_notes_block' => true]],
                ],
            ],
            [
                'name' => 'Compact',
                'code' => 'compact',
                'paper_size' => 'A4',
                'orientation' => 'portrait',
                'view_name' => 'invoice_templates.render.pdf',
                'is_active' => true,
                'is_default' => false,
                'sections' => [
                    ['section_key' => 'header', 'section_label' => 'Header', 'is_visible' => true, 'sort_order' => 10, 'settings_json' => ['title' => 'TAX INVOICE', 'style' => 'compact']],
                    ['section_key' => 'company_info', 'section_label' => 'Company Info', 'is_visible' => true, 'sort_order' => 20, 'settings_json' => ['show_logo' => true, 'show_gst' => true]],
                    ['section_key' => 'invoice_meta', 'section_label' => 'Invoice Meta', 'is_visible' => true, 'sort_order' => 30, 'settings_json' => ['fields' => ['invoice_number', 'invoice_date']]],
                    ['section_key' => 'customer_info', 'section_label' => 'Customer Info', 'is_visible' => true, 'sort_order' => 40, 'settings_json' => ['show_shipping' => false, 'show_billing' => true]],
                    ['section_key' => 'items_table', 'section_label' => 'Items Table', 'is_visible' => true, 'sort_order' => 50, 'settings_json' => ['show_hsn' => false, 'show_tax_columns' => true, 'show_discount_column' => false]],
                    ['section_key' => 'tax_summary', 'section_label' => 'Tax Summary', 'is_visible' => true, 'sort_order' => 60, 'settings_json' => ['layout' => 'compact']],
                    ['section_key' => 'totals', 'section_label' => 'Totals', 'is_visible' => true, 'sort_order' => 70, 'settings_json' => ['show_transport_charge' => false, 'highlight_grand_total' => true]],
                    ['section_key' => 'amount_in_words', 'section_label' => 'Amount In Words', 'is_visible' => true, 'sort_order' => 80, 'settings_json' => ['label' => 'Amount in Words']],
                    ['section_key' => 'bank_details', 'section_label' => 'Bank Details', 'is_visible' => true, 'sort_order' => 90, 'settings_json' => ['show_account_name' => true, 'show_account_number' => true, 'show_ifsc' => false]],
                    ['section_key' => 'signature', 'section_label' => 'Signature', 'is_visible' => true, 'sort_order' => 100, 'settings_json' => ['label' => 'Signature']],
                    ['section_key' => 'terms_conditions', 'section_label' => 'Terms & Conditions', 'is_visible' => true, 'sort_order' => 110, 'settings_json' => ['label' => 'Terms & Conditions', 'show_notes_block' => false]],
                ],
            ],
        ];

        InvoiceTemplate::query()->update(['is_default' => false]);

        foreach ($templates as $templateData) {
            $sections = $templateData['sections'];
            unset($templateData['sections']);

            $template = InvoiceTemplate::query()->updateOrCreate(
                ['code' => $templateData['code']],
                $templateData
            );

            foreach ($sections as $sectionData) {
                DB::connection()->table('invoice_template_sections')->updateOrInsert(
                    [
                        'invoice_template_id' => $template->id,
                        'section_key' => $sectionData['section_key'],
                    ],
                    [
                        'section_label' => $sectionData['section_label'],
                        'is_visible' => $sectionData['is_visible'],
                        'sort_order' => $sectionData['sort_order'],
                        'settings_json' => json_encode($sectionData['settings_json'], JSON_UNESCAPED_UNICODE),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
