<?php

namespace App\Http\Controllers;

use App\Models\InvoiceTemplate;
use App\Services\TermsAndConditionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompanyInvoiceTemplateController extends Controller
{
    public function __construct(private TermsAndConditionService $termsAndConditionService)
    {
    }

    private function denyIfNotCompanySettingsUser()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->type === 'super admin') {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        if (!$user->can('manage company settings') && $user->type !== 'company') {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        return null;
    }

    public function index()
    {
        if ($deny = $this->denyIfNotCompanySettingsUser()) {
            return $deny;
        }

        $templates = InvoiceTemplate::query()
            ->with(['sections' => function ($query) {
                $query->where('is_visible', true)->orderBy('sort_order');
            }])
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $creatorId = (int) Auth::user()->creatorId();
        $selectedTemplateId = $this->getSelectedTemplateId($creatorId);
        $effectiveTemplate = $this->resolveEffectiveTemplate($templates, $selectedTemplateId);

        return view('setting.company-invoice-templates', [
            'templates' => $templates,
            'selectedTemplateId' => $selectedTemplateId,
            'effectiveTemplate' => $effectiveTemplate,
        ]);
    }

    public function show(InvoiceTemplate $invoiceTemplate)
    {
        if ($deny = $this->denyIfNotCompanySettingsUser()) {
            return $deny;
        }

        if (!$invoiceTemplate->is_active) {
            return redirect()
                ->route('setting.company-invoice-templates.index')
                ->with('error', 'This template is not active.');
        }

        $invoiceTemplate->load(['sections' => function ($query) {
            $query->where('is_visible', true)->orderBy('sort_order');
        }]);

        $creatorId = (int) Auth::user()->creatorId();
        $selectedTemplateId = $this->getSelectedTemplateId($creatorId);
        $allActiveTemplates = InvoiceTemplate::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
        $effectiveTemplate = $this->resolveEffectiveTemplate($allActiveTemplates, $selectedTemplateId);

        return view('setting.company-invoice-template-preview', [
            'template' => $invoiceTemplate,
            'selectedTemplateId' => $selectedTemplateId,
            'effectiveTemplate' => $effectiveTemplate,
            'sectionMap' => $invoiceTemplate->sections->keyBy('section_key'),
            'previewData' => $this->buildPreviewData(),
            'previewTheme' => $this->buildPreviewTheme($invoiceTemplate),
        ]);
    }

    public function select(InvoiceTemplate $invoiceTemplate)
    {
        if ($deny = $this->denyIfNotCompanySettingsUser()) {
            return $deny;
        }

        if (!$invoiceTemplate->is_active) {
            return redirect()
                ->route('setting.company-invoice-templates.index')
                ->with('error', 'Only active templates can be selected.');
        }

        $creatorId = (int) Auth::user()->creatorId();

        DB::connection('tenant')->table('settings')->updateOrInsert(
            [
                'name' => 'default_invoice_template_id',
                'created_by' => $creatorId,
            ],
            [
                'value' => (string) $invoiceTemplate->id,
            ]
        );

        return redirect()
            ->route('setting.company-invoice-templates.index')
            ->with('success', 'Invoice template updated successfully.');
    }

    private function getSelectedTemplateId(int $creatorId): int
    {
        if (!Schema::connection('tenant')->hasTable('settings')) {
            return 0;
        }

        return (int) (DB::connection('tenant')->table('settings')
            ->where('created_by', $creatorId)
            ->where('name', 'default_invoice_template_id')
            ->value('value') ?? 0);
    }

    private function resolveEffectiveTemplate($templates, int $selectedTemplateId): ?InvoiceTemplate
    {
        if ($selectedTemplateId > 0) {
            $selectedTemplate = $templates->firstWhere('id', $selectedTemplateId);

            if ($selectedTemplate) {
                return $selectedTemplate;
            }
        }

        return $templates->firstWhere('is_default', true) ?: $templates->first();
    }

    private function buildPreviewTheme(InvoiceTemplate $template): array
    {
        $headerSection = $template->sections->firstWhere('section_key', 'header');
        $style = strtolower((string) data_get($headerSection?->settings_json, 'style', $template->code));

        return match ($style) {
            'modern' => [
                'class' => 'preview-modern',
                'accent' => '#dbe9ff',
                'accent_text' => '#1e4d8f',
                'border' => '#9fb9e6',
            ],
            'compact' => [
                'class' => 'preview-compact',
                'accent' => '#eceff3',
                'accent_text' => '#334155',
                'border' => '#cbd5e1',
            ],
            default => [
                'class' => 'preview-classic',
                'accent' => '#eef1f5',
                'accent_text' => '#1f2937',
                'border' => '#cfd8e3',
            ],
        };
    }

    private function buildPreviewData(): array
    {
        return [
            'company' => [
                'name' => 'EngageNet Industries',
                'address_line_1' => 'A-12 Industrial Estate',
                'address_line_2' => 'Phase 2',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'zipcode' => '380001',
                'phone' => '+91 98765 43210',
                'gst_no' => '24ABCDE1234F1Z5',
            ],
            'invoice' => [
                'title' => 'TAX INVOICE',
                'number' => 'INV-2026-0003',
                'date' => '09 Apr 2026',
                'transport' => 'Blue Dart Logistics',
                'lr_no' => 'LR-20931',
                'article_count' => '6',
            ],
            'customer' => [
                'billing_name' => 'Pratik Components Pvt. Ltd.',
                'billing_contact' => 'Attn: Mr. Pratik Shah',
                'billing_address_line_1' => '44 Commerce Park',
                'billing_address_line_2' => 'Satellite Road',
                'billing_city' => 'Ahmedabad',
                'billing_state' => 'Gujarat',
                'billing_zipcode' => '380015',
                'shipping_name' => 'Pratik Components Warehouse',
                'shipping_contact' => 'Dispatch Desk',
                'shipping_address_line_1' => 'Plot 8 Warehouse Zone',
                'shipping_address_line_2' => 'Naroda GIDC',
                'shipping_city' => 'Ahmedabad',
                'shipping_state' => 'Gujarat',
                'shipping_zipcode' => '382330',
                'phone' => '+91 98700 11223',
                'gst_no' => '24AAACP1234B1Z7',
            ],
            'items' => [
                [
                    'name' => 'UPVC Pipe - 2 Inch Heavy Duty',
                    'hsn' => '39172300',
                    'qty' => 12,
                    'unit' => 'Nos',
                    'rate' => 2450.00,
                    'tax' => '18%',
                    'discount' => '5%',
                    'amount' => 27930.00,
                ],
                [
                    'name' => 'Connector Set - Industrial Grade',
                    'hsn' => '39174000',
                    'qty' => 20,
                    'unit' => 'Nos',
                    'rate' => 620.00,
                    'tax' => '18%',
                    'discount' => '0%',
                    'amount' => 12400.00,
                ],
                [
                    'name' => 'Installation Support Kit',
                    'hsn' => '82055990',
                    'qty' => 2,
                    'unit' => 'Set',
                    'rate' => 3150.00,
                    'tax' => '18%',
                    'discount' => '2%',
                    'amount' => 6174.00,
                ],
            ],
            'summary' => [
                'sub_total' => 46504.00,
                'cgst' => 4185.36,
                'sgst' => 4185.36,
                'transport_charge' => 850.00,
                'grand_total' => 55724.72,
                'amount_in_words' => 'Rupees Fifty Five Thousand Seven Hundred Twenty Four and Seventy Two Paise Only',
            ],
            'bank' => [
                'account_name' => 'EngageNet Industries',
                'account_number' => '123456789012',
                'ifsc' => 'HDFC0001234',
                'bank_name' => 'HDFC Bank',
            ],
            'terms' => $this->termsAndConditionService->getInvoiceTerms('tenant'),
        ];
    }
}
