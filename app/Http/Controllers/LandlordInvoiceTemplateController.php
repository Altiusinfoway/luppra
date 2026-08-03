<?php

namespace App\Http\Controllers;

use App\Models\InvoiceTemplate;
use App\Models\InvoiceTemplateSection;
use App\Services\TermsAndConditionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LandlordInvoiceTemplateController extends Controller
{
    public function __construct(private TermsAndConditionService $termsAndConditionService)
    {
    }

    private function denyIfNotSuperAdmin()
    {
        if (!auth()->check() || auth()->user()->type !== 'super admin') {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        return null;
    }

    public function index()
    {
        if ($deny = $this->denyIfNotSuperAdmin()) {
            return $deny;
        }

        $templates = InvoiceTemplate::query()
            ->with(['sections' => function ($query) {
                $query->orderBy('sort_order');
            }])
            ->orderByDesc('is_default')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('setting.invoice-templates', [
            'templates' => $templates,
        ]);
    }

    public function create()
    {
        if ($deny = $this->denyIfNotSuperAdmin()) {
            return $deny;
        }

        return view('setting.invoice-template-create', [
            'defaultSections' => $this->defaultSectionFormRows(),
        ]);
    }

    public function show(InvoiceTemplate $invoiceTemplate)
    {
        if ($deny = $this->denyIfNotSuperAdmin()) {
            return $deny;
        }

        $invoiceTemplate->load(['sections' => function ($query) {
            $query->orderBy('sort_order');
        }]);

        $defaultTemplate = InvoiceTemplate::query()
            ->with(['sections' => function ($query) {
                $query->orderBy('sort_order');
            }])
            ->where('is_default', true)
            ->first();

        return view('setting.invoice-template-view', [
            'template' => $invoiceTemplate,
            'defaultTemplate' => $defaultTemplate,
            'differences' => $this->buildDifferences($invoiceTemplate, $defaultTemplate),
            'sectionMap' => $invoiceTemplate->sections->keyBy('section_key'),
            'previewData' => $this->buildPreviewData(),
            'previewTheme' => $this->buildPreviewTheme($invoiceTemplate),
        ]);
    }

    public function edit(InvoiceTemplate $invoiceTemplate)
    {
        if ($deny = $this->denyIfNotSuperAdmin()) {
            return $deny;
        }

        $invoiceTemplate->load(['sections' => function ($query) {
            $query->orderBy('sort_order');
        }]);

        return view('setting.invoice-template-edit', [
            'template' => $invoiceTemplate,
        ]);
    }

    public function store(Request $request)
    {
        if ($deny = $this->denyIfNotSuperAdmin()) {
            return $deny;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('invoice_templates', 'code'),
            ],
            'paper_size' => ['required', 'string', 'max:50'],
            'orientation' => ['required', 'in:portrait,landscape'],
            'view_name' => ['required', 'string', 'max:255'],
            'sections' => ['nullable', 'array'],
            'sections.*.id' => ['nullable', 'integer'],
            'sections.*.section_key' => ['nullable', 'string', 'max:100'],
            'sections.*.section_label' => ['nullable', 'string', 'max:255'],
            'sections.*.sort_order' => ['nullable', 'integer'],
            'sections.*.settings_json' => ['nullable', 'string'],
        ]);

        $normalizedSections = $this->normalizeSections($request->input('sections', []));

        if (empty($normalizedSections)) {
            $normalizedSections = $this->defaultSectionDefinitions();
        }

        $template = null;

        DB::connection()->transaction(function () use ($request, $validated, $normalizedSections, &$template) {
            $shouldBeDefault = $request->boolean('is_default')
                || !InvoiceTemplate::query()->where('is_default', true)->exists();

            if ($shouldBeDefault) {
                InvoiceTemplate::query()->update(['is_default' => false]);
            }

            $template = InvoiceTemplate::query()->create([
                'name' => $validated['name'],
                'code' => Str::slug($validated['code'], '_'),
                'paper_size' => $validated['paper_size'],
                'orientation' => $validated['orientation'],
                'view_name' => $validated['view_name'],
                'is_active' => $request->boolean('is_active'),
                'is_default' => $shouldBeDefault,
            ]);

            foreach ($normalizedSections as $sectionData) {
                $template->sections()->create([
                    'section_key' => $sectionData['section_key'],
                    'section_label' => $sectionData['section_label'],
                    'is_visible' => $sectionData['is_visible'],
                    'sort_order' => $sectionData['sort_order'],
                    'settings_json' => $sectionData['settings_json'],
                ]);
            }
        });

        return redirect()
            ->route('setting.invoice-templates.show', $template->id)
            ->with('success', 'Invoice template created successfully.');
    }

    public function update(Request $request, InvoiceTemplate $invoiceTemplate)
    {
        if ($deny = $this->denyIfNotSuperAdmin()) {
            return $deny;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('invoice_templates', 'code')->ignore($invoiceTemplate->id),
            ],
            'paper_size' => ['required', 'string', 'max:50'],
            'orientation' => ['required', 'in:portrait,landscape'],
            'view_name' => ['required', 'string', 'max:255'],
            'sections' => ['nullable', 'array'],
            'sections.*.id' => ['nullable', 'integer'],
            'sections.*.section_key' => ['nullable', 'string', 'max:100'],
            'sections.*.section_label' => ['nullable', 'string', 'max:255'],
            'sections.*.sort_order' => ['nullable', 'integer'],
            'sections.*.settings_json' => ['nullable', 'string'],
        ]);

        $normalizedSections = $this->normalizeSections($request->input('sections', []));

        DB::connection()->transaction(function () use ($request, $invoiceTemplate, $validated, $normalizedSections) {
            $shouldBeDefault = $request->boolean('is_default')
                || !InvoiceTemplate::query()
                    ->where('id', '!=', $invoiceTemplate->id)
                    ->where('is_default', true)
                    ->exists();

            if ($shouldBeDefault) {
                InvoiceTemplate::query()->where('id', '!=', $invoiceTemplate->id)->update(['is_default' => false]);
            }

            $invoiceTemplate->update([
                'name' => $validated['name'],
                'code' => Str::slug($validated['code'], '_'),
                'paper_size' => $validated['paper_size'],
                'orientation' => $validated['orientation'],
                'view_name' => $validated['view_name'],
                'is_active' => $request->boolean('is_active'),
                'is_default' => $shouldBeDefault,
            ]);

            $existingSections = $invoiceTemplate->sections()->get();
            $existingById = $existingSections->keyBy('id');
            $existingByKey = $existingSections->keyBy('section_key');
            $keptIds = [];

            foreach ($normalizedSections as $sectionData) {
                $section = null;

                if (!empty($sectionData['id']) && $existingById->has((int) $sectionData['id'])) {
                    $section = $existingById->get((int) $sectionData['id']);
                } elseif ($existingByKey->has($sectionData['section_key'])) {
                    $section = $existingByKey->get($sectionData['section_key']);
                }

                if (!$section) {
                    $section = new InvoiceTemplateSection();
                    $section->invoice_template_id = $invoiceTemplate->id;
                }

                $section->section_key = $sectionData['section_key'];
                $section->section_label = $sectionData['section_label'];
                $section->is_visible = $sectionData['is_visible'];
                $section->sort_order = $sectionData['sort_order'];
                $section->settings_json = $sectionData['settings_json'];
                $section->save();

                $keptIds[] = $section->id;
            }

            $invoiceTemplate->sections()
                ->when(!empty($keptIds), function ($query) use ($keptIds) {
                    $query->whereNotIn('id', $keptIds);
                }, function ($query) {
                    return $query;
                })
                ->delete();
        });

        return redirect()
            ->route('setting.invoice-templates.show', $invoiceTemplate->id)
            ->with('success', 'Invoice template updated successfully.');
    }

    private function buildDifferences(InvoiceTemplate $template, ?InvoiceTemplate $defaultTemplate): array
    {
        if (!$defaultTemplate || $template->id === $defaultTemplate->id) {
            return [];
        }

        $differences = [];
        $defaultSections = $defaultTemplate->sections->keyBy('section_key');

        foreach ($template->sections as $section) {
            $defaultSection = $defaultSections->get($section->section_key);

            if (!$defaultSection) {
                $differences[] = [
                    'section' => $section->section_label,
                    'field' => 'section',
                    'default' => 'Not present',
                    'current' => 'Present',
                ];
                continue;
            }

            if ((bool) $section->is_visible !== (bool) $defaultSection->is_visible) {
                $differences[] = [
                    'section' => $section->section_label,
                    'field' => 'visibility',
                    'default' => $defaultSection->is_visible ? 'Visible' : 'Hidden',
                    'current' => $section->is_visible ? 'Visible' : 'Hidden',
                ];
            }

            if ((int) $section->sort_order !== (int) $defaultSection->sort_order) {
                $differences[] = [
                    'section' => $section->section_label,
                    'field' => 'sort_order',
                    'default' => (string) $defaultSection->sort_order,
                    'current' => (string) $section->sort_order,
                ];
            }

            $currentSettings = is_array($section->settings_json) ? $section->settings_json : [];
            $defaultSettings = is_array($defaultSection->settings_json) ? $defaultSection->settings_json : [];

            foreach (array_unique(array_merge(array_keys($defaultSettings), array_keys($currentSettings))) as $key) {
                $defaultValue = $this->stringifyValue($defaultSettings[$key] ?? null);
                $currentValue = $this->stringifyValue($currentSettings[$key] ?? null);

                if ($defaultValue !== $currentValue) {
                    $differences[] = [
                        'section' => $section->section_label,
                        'field' => 'settings.' . $key,
                        'default' => $defaultValue,
                        'current' => $currentValue,
                    ];
                }
            }
        }

        foreach ($defaultTemplate->sections as $defaultSection) {
            if (!$template->sections->firstWhere('section_key', $defaultSection->section_key)) {
                $differences[] = [
                    'section' => $defaultSection->section_label,
                    'field' => 'section',
                    'default' => 'Present',
                    'current' => 'Not present',
                ];
            }
        }

        return $differences;
    }

    private function stringifyValue($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        if ($value === null || $value === '') {
            return '-';
        }

        return (string) $value;
    }

    private function normalizeSections(array $sections): array
    {
        $normalized = [];
        $seenKeys = [];

        foreach ($sections as $index => $section) {
            $sectionLabel = trim((string) ($section['section_label'] ?? ''));
            $sectionKey = trim((string) ($section['section_key'] ?? ''));
            $sectionKey = $sectionKey !== '' ? Str::slug($sectionKey, '_') : Str::slug($sectionLabel, '_');
            $settingsJsonText = trim((string) ($section['settings_json'] ?? ''));

            if ($sectionLabel === '' && $sectionKey === '' && $settingsJsonText === '') {
                continue;
            }

            if ($sectionLabel === '') {
                throw ValidationException::withMessages([
                    "sections.$index.section_label" => 'Section label is required.',
                ]);
            }

            if ($sectionKey === '') {
                throw ValidationException::withMessages([
                    "sections.$index.section_key" => 'Section key is required.',
                ]);
            }

            if (in_array($sectionKey, $seenKeys, true)) {
                throw ValidationException::withMessages([
                    "sections.$index.section_key" => 'Section key must be unique in this template.',
                ]);
            }

            $seenKeys[] = $sectionKey;

            $settingsJson = [];

            if ($settingsJsonText !== '') {
                $settingsJson = json_decode($settingsJsonText, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw ValidationException::withMessages([
                        "sections.$index.settings_json" => 'Settings JSON must be valid JSON.',
                    ]);
                }

                if (!is_array($settingsJson)) {
                    $settingsJson = ['value' => $settingsJson];
                }
            }

            $normalized[] = [
                'id' => isset($section['id']) && $section['id'] !== '' ? (int) $section['id'] : null,
                'section_key' => $sectionKey,
                'section_label' => $sectionLabel,
                'is_visible' => isset($section['is_visible']) && (string) $section['is_visible'] === '1',
                'sort_order' => isset($section['sort_order']) && $section['sort_order'] !== ''
                    ? (int) $section['sort_order']
                    : (($index + 1) * 10),
                'settings_json' => $settingsJson,
            ];
        }

        usort($normalized, function ($left, $right) {
            return ($left['sort_order'] <=> $right['sort_order']) ?: strcmp($left['section_key'], $right['section_key']);
        });

        return $normalized;
    }

    private function defaultSectionFormRows(): array
    {
        return array_map(function ($section) {
            return [
                'id' => '',
                'section_key' => $section['section_key'],
                'section_label' => $section['section_label'],
                'is_visible' => $section['is_visible'] ? '1' : '0',
                'sort_order' => $section['sort_order'],
                'settings_json' => json_encode($section['settings_json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            ];
        }, $this->defaultSectionDefinitions());
    }

    private function defaultSectionDefinitions(): array
    {
        return [
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
        ];
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
                'name' => 'Luppra Industries',
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
                'account_name' => 'Luppra Industries',
                'account_number' => '123456789012',
                'ifsc' => 'HDFC0001234',
                'bank_name' => 'HDFC Bank',
            ],
            'terms' => $this->termsAndConditionService->getInvoiceTerms('landlord'),
        ];
    }
}
