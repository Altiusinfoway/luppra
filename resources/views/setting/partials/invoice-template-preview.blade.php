@php
    $headerSection = $sectionMap->get('header');
    $companySection = $sectionMap->get('company_info');
    $invoiceMetaSection = $sectionMap->get('invoice_meta');
    $customerSection = $sectionMap->get('customer_info');
    $itemsSection = $sectionMap->get('items_table');
    $taxSummarySection = $sectionMap->get('tax_summary');
    $totalsSection = $sectionMap->get('totals');
    $amountWordsSection = $sectionMap->get('amount_in_words');
    $bankSection = $sectionMap->get('bank_details');
    $signatureSection = $sectionMap->get('signature');
    $termsSection = $sectionMap->get('terms_conditions');

    $companySettings = is_array($companySection?->settings_json) ? $companySection->settings_json : [];
    $invoiceMetaSettings = is_array($invoiceMetaSection?->settings_json) ? $invoiceMetaSection->settings_json : [];
    $customerSettings = is_array($customerSection?->settings_json) ? $customerSection->settings_json : [];
    $itemsSettings = is_array($itemsSection?->settings_json) ? $itemsSection->settings_json : [];
    $taxSummarySettings = is_array($taxSummarySection?->settings_json) ? $taxSummarySection->settings_json : [];
    $totalsSettings = is_array($totalsSection?->settings_json) ? $totalsSection->settings_json : [];
    $amountWordsSettings = is_array($amountWordsSection?->settings_json) ? $amountWordsSection->settings_json : [];
    $bankSettings = is_array($bankSection?->settings_json) ? $bankSection->settings_json : [];
    $signatureSettings = is_array($signatureSection?->settings_json) ? $signatureSection->settings_json : [];
    $termsSettings = is_array($termsSection?->settings_json) ? $termsSection->settings_json : [];

    $metaFields = $invoiceMetaSettings['fields'] ?? ['invoice_number', 'invoice_date'];
    $taxLayout = $taxSummarySettings['layout'] ?? 'detailed';
@endphp

<style>
    .template-preview-frame {
        background: linear-gradient(180deg, #f8fafc 0%, #eef3f8 100%);
        border: 1px solid #dce4ee;
        border-radius: 16px;
        padding: 18px;
    }

    .template-preview-note {
        margin-bottom: 14px;
        color: #64748b;
        font-size: 12px;
    }

    .template-preview-sheet {
        max-width: 980px;
        margin: 0 auto;
        background: #ffffff;
        border: 1px solid {{ $previewTheme['border'] }};
        border-radius: 14px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .template-preview-sheet table {
        width: 100%;
        border-collapse: collapse;
    }

    .template-preview-sheet td,
    .template-preview-sheet th {
        vertical-align: top;
    }

    .template-preview-accent {
        background: {{ $previewTheme['accent'] }};
        color: {{ $previewTheme['accent_text'] }};
    }

    .template-preview-title {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: 1px;
    }

    .template-preview-subtitle {
        font-size: 12px;
        color: #64748b;
    }

    .template-preview-box {
        border: 1px solid #d8e0ea;
        border-radius: 10px;
        padding: 12px;
        height: 100%;
    }

    .template-preview-section-title {
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .template-preview-items th,
    .template-preview-items td {
        border: 1px solid #d8e0ea;
        padding: 9px 8px;
        font-size: 12px;
    }

    .template-preview-items th {
        background: {{ $previewTheme['accent'] }};
        color: {{ $previewTheme['accent_text'] }};
        font-weight: 700;
        text-align: left;
    }

    .template-preview-right {
        text-align: right;
    }

    .template-preview-center {
        text-align: center;
    }

    .template-preview-muted {
        color: #64748b;
    }

    .template-preview-signature {
        min-height: 78px;
        display: flex;
        align-items: end;
        justify-content: end;
        font-weight: 600;
        color: #334155;
    }

    .template-preview-terms li {
        margin-bottom: 4px;
    }

    .preview-modern .template-preview-title {
        font-size: 26px;
    }

    .preview-compact .template-preview-box {
        padding: 10px;
    }

    .preview-compact .template-preview-items th,
    .preview-compact .template-preview-items td {
        padding: 7px 6px;
        font-size: 11px;
    }
</style>

<div class="card mb-3">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">Actual Preview Output</h5>
        <span class="badge bg-info-subtle text-info">Sample Invoice Preview</span>
    </div>
    <div class="card-body">
        <div class="template-preview-frame">
            <div class="template-preview-note">
                This preview uses sample invoice data and the selected landlord template settings, so you can see how the template would look when chosen.
            </div>

            <div class="template-preview-sheet {{ $previewTheme['class'] }}">
                <table>
                    @if($headerSection?->is_visible)
                        <tr>
                            <td colspan="2" class="template-preview-accent" style="padding: 20px 22px;">
                                <table>
                                    <tr>
                                        <td style="width: 65%;">
                                            <div class="template-preview-title">{{ $previewData['invoice']['title'] }}</div>
                                            <div class="template-preview-subtitle">
                                                {{ $template->name }} template preview based on the current seeded section setup
                                            </div>
                                        </td>
                                        <td class="template-preview-right" style="width: 35%;">
                                            <div style="font-size: 13px; font-weight: 600;">{{ $previewData['invoice']['number'] }}</div>
                                            <div style="font-size: 12px;">{{ $previewData['invoice']['date'] }}</div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="padding: 18px 18px 8px 18px; width: 60%;">
                            @if($companySection?->is_visible)
                                <div class="template-preview-box">
                                    <div class="template-preview-section-title">Company</div>
                                    <table>
                                        <tr>
                                            @if(($companySettings['show_logo'] ?? false) === true)
                                                <td style="width: 62px;">
                                                    <div class="template-preview-accent" style="width: 46px; height: 46px; border-radius: 10px; text-align: center; line-height: 46px; font-weight: 700;">
                                                        EN
                                                    </div>
                                                </td>
                                            @endif
                                            <td>
                                                <div style="font-size: 17px; font-weight: 700; color: #0f172a;">{{ $previewData['company']['name'] }}</div>
                                                <div class="template-preview-muted" style="font-size: 12px;">
                                                    {{ $previewData['company']['address_line_1'] }}, {{ $previewData['company']['address_line_2'] }},
                                                    {{ $previewData['company']['city'] }}, {{ $previewData['company']['state'] }} {{ $previewData['company']['zipcode'] }}
                                                </div>
                                                <div style="font-size: 12px; margin-top: 6px;">
                                                    Phone: {{ $previewData['company']['phone'] }}
                                                    @if(($companySettings['show_gst'] ?? false) === true)
                                                        | GST: {{ $previewData['company']['gst_no'] }}
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                        </td>
                        <td style="padding: 18px 18px 8px 0; width: 40%;">
                            @if($invoiceMetaSection?->is_visible)
                                <div class="template-preview-box">
                                    <div class="template-preview-section-title">Invoice Meta</div>
                                    <table style="font-size: 12px;">
                                        @foreach($metaFields as $field)
                                            <tr>
                                                <td style="padding: 4px 0; width: 42%; font-weight: 600;">
                                                    @if($field === 'invoice_number') Invoice No. @endif
                                                    @if($field === 'invoice_date') Invoice Date @endif
                                                    @if($field === 'transport') Transport @endif
                                                </td>
                                                <td style="padding: 4px 0;">
                                                    @if($field === 'invoice_number') {{ $previewData['invoice']['number'] }} @endif
                                                    @if($field === 'invoice_date') {{ $previewData['invoice']['date'] }} @endif
                                                    @if($field === 'transport') {{ $previewData['invoice']['transport'] }} @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td style="padding: 4px 0; font-weight: 600;">L.R. No.</td>
                                            <td style="padding: 4px 0;">{{ $previewData['invoice']['lr_no'] }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0; font-weight: 600;">Articles</td>
                                            <td style="padding: 4px 0;">{{ $previewData['invoice']['article_count'] }}</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                        </td>
                    </tr>

                    @if($customerSection?->is_visible)
                        <tr>
                            <td colspan="2" style="padding: 10px 18px;">
                                <table>
                                    <tr>
                                        @if(($customerSettings['show_billing'] ?? true) === true)
                                            <td style="width: 50%; padding-right: 8px;">
                                                <div class="template-preview-box">
                                                    <div class="template-preview-section-title">Billing To</div>
                                                    <div style="font-weight: 700; color: #0f172a;">{{ $previewData['customer']['billing_name'] }}</div>
                                                    <div class="template-preview-muted" style="font-size: 12px; margin-top: 4px;">{{ $previewData['customer']['billing_contact'] }}</div>
                                                    <div style="font-size: 12px; margin-top: 8px;">
                                                        {{ $previewData['customer']['billing_address_line_1'] }}, {{ $previewData['customer']['billing_address_line_2'] }}<br>
                                                        {{ $previewData['customer']['billing_city'] }}, {{ $previewData['customer']['billing_state'] }} {{ $previewData['customer']['billing_zipcode'] }}<br>
                                                        Phone: {{ $previewData['customer']['phone'] }}<br>
                                                        GST: {{ $previewData['customer']['gst_no'] }}
                                                    </div>
                                                </div>
                                            </td>
                                        @endif
                                        @if(($customerSettings['show_shipping'] ?? false) === true)
                                            <td style="width: 50%; padding-left: 8px;">
                                                <div class="template-preview-box">
                                                    <div class="template-preview-section-title">Shipping To</div>
                                                    <div style="font-weight: 700; color: #0f172a;">{{ $previewData['customer']['shipping_name'] }}</div>
                                                    <div class="template-preview-muted" style="font-size: 12px; margin-top: 4px;">{{ $previewData['customer']['shipping_contact'] }}</div>
                                                    <div style="font-size: 12px; margin-top: 8px;">
                                                        {{ $previewData['customer']['shipping_address_line_1'] }}, {{ $previewData['customer']['shipping_address_line_2'] }}<br>
                                                        {{ $previewData['customer']['shipping_city'] }}, {{ $previewData['customer']['shipping_state'] }} {{ $previewData['customer']['shipping_zipcode'] }}
                                                    </div>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif

                    @if($itemsSection?->is_visible)
                        <tr>
                            <td colspan="2" style="padding: 6px 18px 10px 18px;">
                                <table class="template-preview-items">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%;">#</th>
                                            <th>Item Description</th>
                                            @if(($itemsSettings['show_hsn'] ?? false) === true)
                                                <th style="width: 12%;">HSN</th>
                                            @endif
                                            <th style="width: 8%;">Qty</th>
                                            <th style="width: 10%;">Unit</th>
                                            <th style="width: 12%;">Rate</th>
                                            @if(($itemsSettings['show_tax_columns'] ?? false) === true)
                                                <th style="width: 10%;">Tax</th>
                                            @endif
                                            @if(($itemsSettings['show_discount_column'] ?? false) === true)
                                                <th style="width: 10%;">Discount</th>
                                            @endif
                                            <th style="width: 13%;">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($previewData['items'] as $index => $item)
                                            <tr>
                                                <td class="template-preview-center">{{ $index + 1 }}</td>
                                                <td>{{ $item['name'] }}</td>
                                                @if(($itemsSettings['show_hsn'] ?? false) === true)
                                                    <td>{{ $item['hsn'] }}</td>
                                                @endif
                                                <td class="template-preview-center">{{ $item['qty'] }}</td>
                                                <td>{{ $item['unit'] }}</td>
                                                <td class="template-preview-right">{{ number_format($item['rate'], 2) }}</td>
                                                @if(($itemsSettings['show_tax_columns'] ?? false) === true)
                                                    <td class="template-preview-center">{{ $item['tax'] }}</td>
                                                @endif
                                                @if(($itemsSettings['show_discount_column'] ?? false) === true)
                                                    <td class="template-preview-center">{{ $item['discount'] }}</td>
                                                @endif
                                                <td class="template-preview-right">{{ number_format($item['amount'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="padding: 0 18px 18px 18px; width: 58%;">
                            @if($amountWordsSection?->is_visible)
                                <div class="template-preview-box" style="margin-bottom: 12px;">
                                    <div class="template-preview-section-title">{{ $amountWordsSettings['label'] ?? 'Amount in Words' }}</div>
                                    <div style="font-size: 13px; font-weight: 600; color: #1e293b;">
                                        {{ $previewData['summary']['amount_in_words'] }}
                                    </div>
                                </div>
                            @endif

                            @if($bankSection?->is_visible)
                                <div class="template-preview-box" style="margin-bottom: 12px;">
                                    <div class="template-preview-section-title">Bank Details</div>
                                    <table style="font-size: 12px;">
                                        @if(($bankSettings['show_account_name'] ?? false) === true)
                                            <tr>
                                                <td style="padding: 4px 0; width: 38%; font-weight: 600;">Account Name</td>
                                                <td style="padding: 4px 0;">{{ $previewData['bank']['account_name'] }}</td>
                                            </tr>
                                        @endif
                                        @if(($bankSettings['show_account_number'] ?? false) === true)
                                            <tr>
                                                <td style="padding: 4px 0; font-weight: 600;">Account Number</td>
                                                <td style="padding: 4px 0;">{{ $previewData['bank']['account_number'] }}</td>
                                            </tr>
                                        @endif
                                        @if(($bankSettings['show_ifsc'] ?? false) === true)
                                            <tr>
                                                <td style="padding: 4px 0; font-weight: 600;">IFSC</td>
                                                <td style="padding: 4px 0;">{{ $previewData['bank']['ifsc'] }}</td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td style="padding: 4px 0; font-weight: 600;">Bank</td>
                                            <td style="padding: 4px 0;">{{ $previewData['bank']['bank_name'] }}</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif

                            @if($termsSection?->is_visible)
                                <div class="template-preview-box">
                                    <div class="template-preview-section-title">{{ $termsSettings['label'] ?? 'Terms & Conditions' }}</div>
                                    <ol class="template-preview-terms" style="padding-left: 18px; margin-bottom: 0; font-size: 12px;">
                                        @foreach($previewData['terms'] as $term)
                                            <li>{{ $term }}</li>
                                        @endforeach
                                    </ol>
                                </div>
                            @endif
                        </td>

                        <td style="padding: 0 18px 18px 0; width: 42%;">
                            @if($taxSummarySection?->is_visible)
                                <div class="template-preview-box" style="margin-bottom: 12px;">
                                    <div class="template-preview-section-title">Tax Summary</div>
                                    @if($taxLayout === 'stacked')
                                        <div style="font-size: 12px; line-height: 1.9;">
                                            <div class="d-flex justify-content-between"><span>CGST (9%)</span><strong>{{ number_format($previewData['summary']['cgst'], 2) }}</strong></div>
                                            <div class="d-flex justify-content-between"><span>SGST (9%)</span><strong>{{ number_format($previewData['summary']['sgst'], 2) }}</strong></div>
                                        </div>
                                    @elseif($taxLayout === 'compact')
                                        <div style="font-size: 12px;">
                                            GST Total: <strong>{{ number_format($previewData['summary']['cgst'] + $previewData['summary']['sgst'], 2) }}</strong>
                                        </div>
                                    @else
                                        <table style="font-size: 12px; width: 100%;">
                                            <tr>
                                                <td style="padding: 4px 0;">CGST (9%)</td>
                                                <td class="template-preview-right" style="padding: 4px 0;">{{ number_format($previewData['summary']['cgst'], 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 4px 0;">SGST (9%)</td>
                                                <td class="template-preview-right" style="padding: 4px 0;">{{ number_format($previewData['summary']['sgst'], 2) }}</td>
                                            </tr>
                                        </table>
                                    @endif
                                </div>
                            @endif

                            @if($totalsSection?->is_visible)
                                <div class="template-preview-box" style="margin-bottom: 12px;">
                                    <div class="template-preview-section-title">Totals</div>
                                    <table style="font-size: 12px; width: 100%;">
                                        <tr>
                                            <td style="padding: 5px 0;">Sub Total</td>
                                            <td class="template-preview-right" style="padding: 5px 0;">{{ number_format($previewData['summary']['sub_total'], 2) }}</td>
                                        </tr>
                                        @if(($totalsSettings['show_transport_charge'] ?? false) === true)
                                            <tr>
                                                <td style="padding: 5px 0;">Transport Charge</td>
                                                <td class="template-preview-right" style="padding: 5px 0;">{{ number_format($previewData['summary']['transport_charge'], 2) }}</td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td style="padding: 5px 0;">Tax Total</td>
                                            <td class="template-preview-right" style="padding: 5px 0;">{{ number_format($previewData['summary']['cgst'] + $previewData['summary']['sgst'], 2) }}</td>
                                        </tr>
                                        <tr class="template-preview-accent">
                                            <td style="padding: 8px; font-weight: 700;">Grand Total</td>
                                            <td class="template-preview-right" style="padding: 8px; font-weight: 700;">
                                                {{ number_format($previewData['summary']['grand_total'], 2) }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            @endif

                            @if($signatureSection?->is_visible)
                                <div class="template-preview-box">
                                    <div class="template-preview-section-title">Signature</div>
                                    <div class="template-preview-signature">
                                        {{ $signatureSettings['label'] ?? 'Authorised Signatory' }}
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
