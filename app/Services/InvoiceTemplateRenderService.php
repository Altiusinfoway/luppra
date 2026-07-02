<?php

namespace App\Services;

use App\Models\BankDetail;
use App\Models\InvoiceTemplate;
use App\Models\Order;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class InvoiceTemplateRenderService
{
    public function __construct(private TermsAndConditionService $termsAndConditionService)
    {
    }

    public function renderHtml(Order $order): string
    {
        return view('pdf.invoice.master', $this->buildViewData($order))->render();
    }

    public function buildViewData(Order $order): array
    {
        $order->loadMissing([
            'orderProducts.product',
            'getCustomer.getBillingAddress.get_city',
            'getCustomer.getBillingAddress.get_state',
            'getCustomer.getShippingAddress.get_city',
            'getCustomer.getShippingAddress.get_state',
            'getTransport',
        ]);

        $creatorId = $this->resolveCreatorId($order);
        $template = $this->resolveTemplate($creatorId);
        $sections = $template->sections()
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->get();

        return [
            'order' => $order,
            'template' => $template,
            'sections' => $sections,
            'sectionMap' => $sections->keyBy('section_key'),
            'invoice' => $this->buildInvoicePayload($order),
            'styleView' => $this->resolveStyleView($template),
        ];
    }

    private function resolveCreatorId(Order $order): int
    {
        $creatorId = (int) ($order->created_by ?? 0);

        if ($creatorId > 0) {
            return $creatorId;
        }

        if (Auth::check()) {
            return (int) Auth::user()->creatorId();
        }

        return 1;
    }

    private function resolveTemplate(int $creatorId): InvoiceTemplate
    {
        $selectedTemplateId = $this->getSelectedTemplateId($creatorId);

        if ($selectedTemplateId > 0) {
            $selectedTemplate = InvoiceTemplate::query()
                ->where('id', $selectedTemplateId)
                ->where('is_active', true)
                ->first();

            if ($selectedTemplate) {
                return $selectedTemplate;
            }
        }

        $defaultTemplate = InvoiceTemplate::query()
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();

        if ($defaultTemplate) {
            return $defaultTemplate;
        }

        $fallbackTemplate = InvoiceTemplate::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first();

        if ($fallbackTemplate) {
            return $fallbackTemplate;
        }

        throw new RuntimeException('No active invoice template found for the new invoice template renderer.');
    }

    private function getSelectedTemplateId(int $creatorId): int
    {
        if (!Schema::connection('tenant')->hasTable('settings')) {
            return 0;
        }

        return (int) (DB::connection('tenant')
            ->table('settings')
            ->where('created_by', $creatorId)
            ->where('name', 'default_invoice_template_id')
            ->value('value') ?? 0);
    }

    private function resolveStyleView(InvoiceTemplate $template): string
    {
        $headerSection = $template->sections()->where('section_key', 'header')->first();
        $style = strtolower((string) data_get($headerSection?->settings_json, 'style', $template->code));

        return match ($style) {
            'modern' => 'pdf.invoice.styles.modern',
            'compact' => 'pdf.invoice.styles.compact',
            default => 'pdf.invoice.styles.classic',
        };
    }

    private function buildInvoicePayload(Order $order): array
    {
        $customer = $order->getCustomer;
        $billingAddress = optional($customer)->getBillingAddress;
        $shippingAddress = optional($customer)->getShippingAddress ?: $billingAddress;
        $primaryPhone = optional($customer)->getCustomerPhone()->where('is_primary', 1)->first();
        $transport = $order->getTransport;
        $transportContacts = json_decode((string) ($transport->contact_json ?? ''), true);
        $bankDetail = BankDetail::query()->orderBy('id')->first();
        $companyAddress = Utility::getCompanyBillingAddress();
        $invoiceNumber = $order->bill_number ?: str_replace('ORDER', 'INV', (string) $order->order_number);

        $items = [];
        $subTotal = 0.0;
        $discountTotal = 0.0;

        foreach ($order->orderProducts as $index => $orderProduct) {
            $product = $orderProduct->product;
            $baseAmount = (float) $orderProduct->qty * (float) $orderProduct->price;
            $discountAmount = $baseAmount * ((float) $orderProduct->discount / 100);
            $lineAmount = $baseAmount - $discountAmount;

            $subTotal += $baseAmount;
            $discountTotal += $discountAmount;

            $items[] = [
                'sr_no' => $index + 1,
                'description' => trim((string) (($product->sku_code ?? '') !== '' ? ($product->sku_code . ' - ') : '') . ($product->name ?? 'Item')),
                'notes' => (string) ($orderProduct->short_notes ?? ''),
                'hsn' => (string) ($product->hsn_code ?? ''),
                'qty' => (float) $orderProduct->qty,
                'unit' => (string) Utility::getUnitName((int) $orderProduct->unit_id),
                'rate' => (float) $orderProduct->price,
                'discount_percent' => (float) ($orderProduct->discount ?? 0),
                'discount_amount' => round($discountAmount, 2),
                'tax_percent' => (float) ($orderProduct->tax ?? 0),
                'amount' => round($lineAmount, 2),
            ];
        }

        $taxTotal = (float) ($order->gst ?? 0);
        $transportCharge = (float) ($order->transport_charge ?? 0);
        $taxableTotal = round($subTotal - $discountTotal, 2);
        $grandTotal = (float) ($order->grand_total ?? 0);

        if ($grandTotal <= 0) {
            $grandTotal = round($taxableTotal + $taxTotal + $transportCharge, 2);
        }

        return [
            'company' => [
                'name' => (string) (Utility::getSetting('website_name') ?: Utility::WebsiteName()),
                'short_name' => (string) Utility::getSetting('website_short_name'),
                'phone' => (string) Utility::getSetting('phone'),
                'email' => (string) Utility::getSetting('email'),
                'gst_no' => (string) Utility::getSetting('gst_no'),
                'logo_path' => Utility::websiteLogo(true),
                'address_lines' => $this->buildAddressLines($companyAddress),
            ],
            'meta' => [
                'title' => 'TAX INVOICE',
                'invoice_number' => $invoiceNumber,
                'invoice_date' => Utility::getDateFormated($order->date),
                'order_number' => (string) $order->order_number,
                'transport_name' => (string) ($transport->name ?? ''),
                'transport_contact' => is_array($transportContacts) ? (string) ($transportContacts[0] ?? '') : '',
                'lr_number' => (string) ($order->lr_number ?? ''),
                'article_count' => (string) ($order->no_article ?? ''),
                'payment_terms' => $order->is_advance_payment ? 'Advance Payment' : trim(((int) ($order->payment_after_days ?? 0)) . ' Days'),
            ],
            'customer' => [
                'billing_name' => (string) ($customer->company_name ?: $customer->name),
                'billing_contact' => (string) ($customer->company_name ? ('Attn: ' . ($customer->name ?? '')) : ($customer->name ?? '')),
                'billing_phone' => (string) ($primaryPhone->phone ?? ''),
                'billing_gst_no' => (string) ($customer->gst_no ?? ''),
                'billing_address_lines' => $this->buildAddressLines($billingAddress),
                'shipping_name' => (string) ($customer->company_name ?: $customer->name),
                'shipping_contact' => (string) ($customer->company_name ? ('Attn: ' . ($customer->name ?? '')) : ($customer->name ?? '')),
                'shipping_phone' => (string) ($primaryPhone->phone ?? ''),
                'shipping_gst_no' => (string) ($customer->gst_no ?? ''),
                'shipping_address_lines' => $this->buildAddressLines($shippingAddress),
            ],
            'items' => $items,
            'taxes' => $this->buildTaxRows((string) ($order->tax_detail_json ?? ''), $taxTotal),
            'summary' => [
                'sub_total' => round($subTotal, 2),
                'discount_total' => round($discountTotal, 2),
                'taxable_total' => $taxableTotal,
                'tax_total' => round($taxTotal, 2),
                'transport_charge' => round($transportCharge, 2),
                'grand_total' => round($grandTotal, 2),
                'amount_in_words' => $this->convertAmountToWords($grandTotal),
            ],
            'bank' => [
                'account_name' => (string) ($bankDetail->account_holder_name ?? ''),
                'account_number' => (string) ($bankDetail->account_no ?? ''),
                'ifsc' => (string) ($bankDetail->ifsc_code ?? ''),
                'bank_name' => (string) ($bankDetail->bank_name ?? ''),
                'branch_name' => (string) ($bankDetail->branch_name ?? ''),
            ],
            'terms' => $this->termsAndConditionService->getInvoiceTerms('tenant'),
        ];
    }

    private function buildAddressLines($address): array
    {
        if (!$address) {
            return [];
        }

        $lineOne = trim(implode(', ', array_filter([
            (string) ($address->address_line_1 ?? ''),
            (string) ($address->address_line_2 ?? ''),
        ])));

        $lineTwo = trim(implode(', ', array_filter([
            optional($address->get_city)->name,
            optional($address->get_state)->name,
            (string) ($address->zipcode ?? ''),
        ])));

        return array_values(array_filter([$lineOne, $lineTwo]));
    }

    private function buildTaxRows(string $taxDetailJson, float $taxTotal): array
    {
        $decoded = json_decode($taxDetailJson, true);
        $labels = [];

        if (is_array($decoded)) {
            foreach ($decoded as $name => $value) {
                if ((int) $value === 1 || (float) $value > 0) {
                    $labels[] = (string) $name;
                }
            }
        }

        if (empty($labels)) {
            return [
                [
                    'label' => 'Tax Total',
                    'amount' => round($taxTotal, 2),
                ],
            ];
        }

        $perTaxAmount = count($labels) > 0 ? round($taxTotal / count($labels), 2) : round($taxTotal, 2);

        return array_map(function ($label) use ($perTaxAmount) {
            return [
                'label' => $label,
                'amount' => $perTaxAmount,
            ];
        }, $labels);
    }

    private function convertAmountToWords(float $amount): string
    {
        $whole = (int) floor($amount);
        $fraction = (int) round(($amount - $whole) * 100);

        if (!class_exists(\NumberFormatter::class)) {
            return number_format($amount, 2) . ' only';
        }

        $formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
        $words = ucfirst((string) $formatter->format($whole)) . ' rupees';

        if ($fraction > 0) {
            $words .= ' and ' . $formatter->format($fraction) . ' paise';
        }

        return $words . ' only';
    }
}
