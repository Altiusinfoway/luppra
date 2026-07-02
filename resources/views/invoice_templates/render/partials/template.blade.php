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

    $headerSettings = is_array($headerSection?->settings_json) ? $headerSection->settings_json : [];
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
    $templateClass = match ($previewTheme['class'] ?? 'preview-classic') {
        'preview-modern' => 'it-modern',
        'preview-compact' => 'it-compact',
        default => 'it-classic',
    };
    $headerTitle = $headerSettings['title'] ?? ($previewData['invoice']['title'] ?? 'TAX INVOICE');
    $taxTotal = (float) ($previewData['summary']['cgst'] ?? 0) + (float) ($previewData['summary']['sgst'] ?? 0);
    $knownSectionKeys = [
        'header',
        'company_info',
        'invoice_meta',
        'customer_info',
        'items_table',
        'tax_summary',
        'totals',
        'amount_in_words',
        'bank_details',
        'signature',
        'terms_conditions',
    ];
    $extraSections = $template->sections
        ->filter(function ($section) use ($knownSectionKeys) {
            return $section->is_visible && !in_array($section->section_key, $knownSectionKeys, true);
        })
        ->sortBy('sort_order')
        ->values();
@endphp

<div class="it-document {{ $templateClass }}">
    <table class="it-sheet">
        @if($headerSection?->is_visible)
            <tr>
                <td colspan="2" style="padding: 8px;">
                    <table class="it-inner-table">
                        <tr>
                            <td style="width: 64%;">
                                <table class="it-inner-table">
                                    <tr>
                                        @if(($companySettings['show_logo'] ?? false) === true)
                                            <td style="width: 54px; padding-right: 8px;">
                                                <div class="it-logo-box">EN</div>
                                            </td>
                                        @endif
                                        <td>
                                            <div class="it-company-title">{{ $previewData['company']['name'] }}</div>
                                            <div class="it-small it-muted">
                                                {{ $previewData['company']['address_line_1'] }}, {{ $previewData['company']['address_line_2'] }},
                                                {{ $previewData['company']['city'] }}, {{ $previewData['company']['state'] }} {{ $previewData['company']['zipcode'] }}
                                            </div>
                                            <div class="it-small">
                                                Phone: {{ $previewData['company']['phone'] }}
                                                @if(($companySettings['show_gst'] ?? false) === true)
                                                    | GST: {{ $previewData['company']['gst_no'] }}
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td style="width: 36%;">
                                <div class="it-header-box">
                                    <div class="it-invoice-title">{{ $headerTitle }}</div>
                                    <table class="it-inner-table" style="margin-top: 6px;">
                                        <tr>
                                            <td style="width: 44%; font-weight: 700;">Template</td>
                                            <td>{{ $template->name }}</td>
                                        </tr>
                                        <tr>
                                            <td style="font-weight: 700;">Code</td>
                                            <td>{{ strtoupper($template->code) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="font-weight: 700;">Paper</td>
                                            <td>{{ $template->paper_size }} / {{ ucfirst($template->orientation) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        @endif

        <tr>
            <td colspan="2" style="padding: 0 8px 8px 8px;">
                <table class="it-inner-table">
                    <tr>
                        @if($customerSection?->is_visible && ($customerSettings['show_billing'] ?? true) === true)
                            <td style="width: 34%; padding-right: 6px;">
                                <div class="it-section-box">
                                    <div class="it-section-title">Billing To</div>
                                    <div><strong>{{ $previewData['customer']['billing_name'] }}</strong></div>
                                    <div class="it-small it-muted">{{ $previewData['customer']['billing_contact'] }}</div>
                                    <div class="it-small" style="padding-top: 5px;">
                                        {{ $previewData['customer']['billing_address_line_1'] }}, {{ $previewData['customer']['billing_address_line_2'] }}<br>
                                        {{ $previewData['customer']['billing_city'] }}, {{ $previewData['customer']['billing_state'] }} {{ $previewData['customer']['billing_zipcode'] }}<br>
                                        Phone: {{ $previewData['customer']['phone'] }}<br>
                                        GST: {{ $previewData['customer']['gst_no'] }}
                                    </div>
                                </div>
                            </td>
                        @endif

                        @if($customerSection?->is_visible && ($customerSettings['show_shipping'] ?? false) === true)
                            <td style="width: 33%; padding-right: 6px;">
                                <div class="it-section-box">
                                    <div class="it-section-title">Shipping To</div>
                                    <div><strong>{{ $previewData['customer']['shipping_name'] }}</strong></div>
                                    <div class="it-small it-muted">{{ $previewData['customer']['shipping_contact'] }}</div>
                                    <div class="it-small" style="padding-top: 5px;">
                                        {{ $previewData['customer']['shipping_address_line_1'] }}, {{ $previewData['customer']['shipping_address_line_2'] }}<br>
                                        {{ $previewData['customer']['shipping_city'] }}, {{ $previewData['customer']['shipping_state'] }} {{ $previewData['customer']['shipping_zipcode'] }}
                                    </div>
                                </div>
                            </td>
                        @endif

                        @if($invoiceMetaSection?->is_visible)
                            <td style="width: 33%;">
                                <div class="it-section-box">
                                    <div class="it-section-title">Invoice Details</div>
                                    <table class="it-inner-table">
                                        @foreach($metaFields as $field)
                                            <tr>
                                                <td style="width: 44%; font-weight: 700;">
                                                    @if($field === 'invoice_number') Invoice No. @endif
                                                    @if($field === 'invoice_date') Date @endif
                                                    @if($field === 'transport') Transport @endif
                                                </td>
                                                <td>
                                                    @if($field === 'invoice_number') {{ $previewData['invoice']['number'] }} @endif
                                                    @if($field === 'invoice_date') {{ $previewData['invoice']['date'] }} @endif
                                                    @if($field === 'transport') {{ $previewData['invoice']['transport'] }} @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td style="font-weight: 700;">L.R. No.</td>
                                            <td>{{ $previewData['invoice']['lr_no'] }}</td>
                                        </tr>
                                        <tr>
                                            <td style="font-weight: 700;">Articles</td>
                                            <td>{{ $previewData['invoice']['article_count'] }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        @endif
                    </tr>
                </table>
            </td>
        </tr>

        @if($itemsSection?->is_visible)
            <tr>
                <td colspan="2" style="padding: 0 8px 8px 8px;">
                    <table class="it-items-table">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: {{ ($itemsSettings['show_hsn'] ?? false) === true ? '27%' : '33%' }};">Item Description</th>
                                @if(($itemsSettings['show_hsn'] ?? false) === true)
                                    <th style="width: 11%;">HSN</th>
                                @endif
                                <th style="width: 7%;">Qty</th>
                                <th style="width: 9%;">Unit</th>
                                <th style="width: 11%;">Rate</th>
                                @if(($itemsSettings['show_tax_columns'] ?? false) === true)
                                    <th style="width: 9%;">Tax</th>
                                @endif
                                @if(($itemsSettings['show_discount_column'] ?? false) === true)
                                    <th style="width: 10%;">Discount</th>
                                @endif
                                <th style="width: 11%;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($previewData['items'] as $index => $item)
                                <tr>
                                    <td class="it-center">{{ $index + 1 }}</td>
                                    <td>{{ $item['name'] }}</td>
                                    @if(($itemsSettings['show_hsn'] ?? false) === true)
                                        <td>{{ $item['hsn'] }}</td>
                                    @endif
                                    <td class="it-center">{{ $item['qty'] }}</td>
                                    <td>{{ $item['unit'] }}</td>
                                    <td class="it-right">{{ number_format($item['rate'], 2) }}</td>
                                    @if(($itemsSettings['show_tax_columns'] ?? false) === true)
                                        <td class="it-center">{{ $item['tax'] }}</td>
                                    @endif
                                    @if(($itemsSettings['show_discount_column'] ?? false) === true)
                                        <td class="it-center">{{ $item['discount'] }}</td>
                                    @endif
                                    <td class="it-right">{{ number_format($item['amount'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
            </tr>
        @endif

        <tr>
            <td style="width: 62%; padding: 0 8px 8px 8px;">
                @if($amountWordsSection?->is_visible)
                    <div class="it-section-box" style="margin-bottom: 8px;">
                        <div class="it-section-title">{{ $amountWordsSettings['label'] ?? 'Amount in Words' }}</div>
                        <div class="it-small"><strong>{{ $previewData['summary']['amount_in_words'] }}</strong></div>
                    </div>
                @endif

                @if($bankSection?->is_visible)
                    <div class="it-section-box" style="margin-bottom: 8px;">
                        <div class="it-section-title">Bank Details</div>
                        <table class="it-inner-table">
                            @if(($bankSettings['show_account_name'] ?? false) === true)
                                <tr>
                                    <td style="width: 34%; font-weight: 700;">Account Name</td>
                                    <td>{{ $previewData['bank']['account_name'] }}</td>
                                </tr>
                            @endif
                            @if(($bankSettings['show_account_number'] ?? false) === true)
                                <tr>
                                    <td style="font-weight: 700;">Account Number</td>
                                    <td>{{ $previewData['bank']['account_number'] }}</td>
                                </tr>
                            @endif
                            @if(($bankSettings['show_ifsc'] ?? false) === true)
                                <tr>
                                    <td style="font-weight: 700;">IFSC</td>
                                    <td>{{ $previewData['bank']['ifsc'] }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td style="font-weight: 700;">Bank</td>
                                <td>{{ $previewData['bank']['bank_name'] }}</td>
                            </tr>
                        </table>
                    </div>
                @endif

                @if($termsSection?->is_visible)
                    <div class="it-section-box">
                        <div class="it-section-title">{{ $termsSettings['label'] ?? 'Terms & Conditions' }}</div>
                        <ol class="it-list it-small">
                            @foreach($previewData['terms'] as $term)
                                <li>{{ $term }}</li>
                            @endforeach
                        </ol>

                        @if(($termsSettings['show_notes_block'] ?? false) === true)
                            <div class="it-small" style="padding-top: 6px;">
                                <strong>Note:</strong> Template-controlled notes block remains landlord managed and invoice data remains company specific.
                            </div>
                        @endif
                    </div>
                @endif
            </td>

            <td style="width: 38%; padding: 0 8px 8px 0;">
                @if($taxSummarySection?->is_visible)
                    <table class="it-tax-table" style="margin-bottom: 8px;">
                        <tr>
                            <td colspan="2" class="it-section-title" style="background: {{ $previewTheme['accent'] ?? '#eef1f5' }}; color: {{ $previewTheme['accent_text'] ?? '#1f2937' }}; margin: 0;">Tax Summary</td>
                        </tr>
                        @if($taxLayout === 'stacked')
                            <tr>
                                <td>CGST (9%)</td>
                                <td class="it-right">{{ number_format($previewData['summary']['cgst'], 2) }}</td>
                            </tr>
                            <tr>
                                <td>SGST (9%)</td>
                                <td class="it-right">{{ number_format($previewData['summary']['sgst'], 2) }}</td>
                            </tr>
                        @elseif($taxLayout === 'compact')
                            <tr>
                                <td>GST Total</td>
                                <td class="it-right">{{ number_format($taxTotal, 2) }}</td>
                            </tr>
                        @else
                            <tr>
                                <td>CGST (9%)</td>
                                <td class="it-right">{{ number_format($previewData['summary']['cgst'], 2) }}</td>
                            </tr>
                            <tr>
                                <td>SGST (9%)</td>
                                <td class="it-right">{{ number_format($previewData['summary']['sgst'], 2) }}</td>
                            </tr>
                            <tr>
                                <td>Total Tax</td>
                                <td class="it-right">{{ number_format($taxTotal, 2) }}</td>
                            </tr>
                        @endif
                    </table>
                @endif

                @if($totalsSection?->is_visible)
                    <table class="it-totals-table" style="margin-bottom: 8px;">
                        <tr>
                            <td>Sub Total</td>
                            <td class="it-right">{{ number_format($previewData['summary']['sub_total'], 2) }}</td>
                        </tr>
                        @if(($totalsSettings['show_transport_charge'] ?? false) === true)
                            <tr>
                                <td>Transport Charge</td>
                                <td class="it-right">{{ number_format($previewData['summary']['transport_charge'], 2) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td>Tax Total</td>
                            <td class="it-right">{{ number_format($taxTotal, 2) }}</td>
                        </tr>
                        <tr class="{{ ($totalsSettings['highlight_grand_total'] ?? false) === true ? 'it-grand-total' : '' }}">
                            <td>Grand Total</td>
                            <td class="it-right">{{ number_format($previewData['summary']['grand_total'], 2) }}</td>
                        </tr>
                    </table>
                @endif

                @if($signatureSection?->is_visible)
                    <table class="it-footer-table">
                        <tr>
                            <td>
                                <div class="it-section-title">Signature</div>
                                <div class="it-signature-box">
                                    <div class="it-signature-text">{{ $signatureSettings['label'] ?? 'Authorised Signatory' }}</div>
                                </div>
                            </td>
                        </tr>
                    </table>
                @endif
            </td>
        </tr>

        @if($extraSections->isNotEmpty())
            <tr>
                <td colspan="2" style="padding: 0 8px 8px 8px;">
                    <table class="it-inner-table">
                        <tr>
                            @foreach($extraSections as $extraSection)
                                @php
                                    $extraSettings = is_array($extraSection->settings_json) ? $extraSection->settings_json : [];
                                @endphp
                                <td style="width: {{ floor(100 / max($extraSections->count(), 1)) }}%; padding-right: 6px;">
                                    <div class="it-section-box">
                                        <div class="it-section-title">{{ $extraSection->section_label }}</div>
                                        <div class="it-small it-muted" style="padding-bottom: 6px;">
                                            Custom section key: {{ $extraSection->section_key }}
                                        </div>

                                        @if(!empty($extraSettings['content']))
                                            <div class="it-small" style="padding-bottom: 6px;">{{ $extraSettings['content'] }}</div>
                                        @endif

                                        @if(!empty($extraSettings['lines']) && is_array($extraSettings['lines']))
                                            <ul class="it-list it-small">
                                                @foreach($extraSettings['lines'] as $line)
                                                    <li>{{ $line }}</li>
                                                @endforeach
                                            </ul>
                                        @endif

                                        <table class="it-inner-table">
                                            @foreach($extraSettings as $key => $value)
                                                @if(!in_array($key, ['content', 'lines'], true))
                                                    <tr>
                                                        <td style="width: 36%; font-weight: 700;">{{ str_replace('_', ' ', ucfirst($key)) }}</td>
                                                        <td>
                                                            @if(is_array($value))
                                                                {{ json_encode($value, JSON_UNESCAPED_UNICODE) }}
                                                            @elseif(is_bool($value))
                                                                {{ $value ? 'true' : 'false' }}
                                                            @elseif($value === null || $value === '')
                                                                -
                                                            @else
                                                                {{ $value }}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </table>
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    </table>
                </td>
            </tr>
        @endif
    </table>
</div>
