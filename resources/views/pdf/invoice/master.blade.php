@php
    $itemsSection = $sectionMap->get('items_table');
    $itemsSettings = is_array($itemsSection?->settings_json) ? $itemsSection->settings_json : [];
    $itemsPerPage = 7;
    $itemsSectionIndex = $sections->search(fn ($section) => $section->section_key === 'items_table');
    $preItemSections = $itemsSectionIndex === false ? $sections : $sections->slice(0, $itemsSectionIndex);
    $postItemSections = $itemsSectionIndex === false ? collect() : $sections->slice($itemsSectionIndex + 1);
    $groupedLastPageSectionKeys = ['tax_summary', 'totals', 'amount_in_words', 'bank_details'];
    $groupedLastPageSections = $postItemSections
        ->filter(fn ($section) => in_array($section->section_key, $groupedLastPageSectionKeys, true))
        ->keyBy('section_key');
    $extraLastPageSections = $postItemSections
        ->reject(fn ($section) => in_array($section->section_key, array_merge($groupedLastPageSectionKeys, ['signature', 'terms_conditions']), true));

    $taxSection = $groupedLastPageSections->get('tax_summary');
    $totalsSection = $groupedLastPageSections->get('totals');
    $amountWordsSection = $groupedLastPageSections->get('amount_in_words');
    $bankSection = $groupedLastPageSections->get('bank_details');
    $signatureSection = $postItemSections->firstWhere('section_key', 'signature');
    $termsSection = $postItemSections->firstWhere('section_key', 'terms_conditions');

    $taxSettings = is_array($taxSection?->settings_json) ? $taxSection->settings_json : [];
    $totalsSettings = is_array($totalsSection?->settings_json) ? $totalsSection->settings_json : [];
    $amountWordsSettings = is_array($amountWordsSection?->settings_json) ? $amountWordsSection->settings_json : [];
    $bankSettings = is_array($bankSection?->settings_json) ? $bankSection->settings_json : [];
    $signatureSettings = is_array($signatureSection?->settings_json) ? $signatureSection->settings_json : [];
    $termsSettings = is_array($termsSection?->settings_json) ? $termsSection->settings_json : [];

    if ($itemsSection) {
        $itemPages = collect($invoice['items'] ?? [])->chunk($itemsPerPage)->values();
    } else {
        $itemPages = collect([collect($invoice['items'] ?? [])]);
    }

    if ($itemPages->isEmpty()) {
        $itemPages = collect([collect()]);
    }

    $totalPages = $itemPages->count();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $invoice['meta']['invoice_number'] ?? 'Invoice' }}</title>
    <style>
        @media print {
            .no-print { display: none !important; }
        }

        body.preview-mode {
            padding-top: 54px;
        }

        .preview-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 999;
            background: #ffffff;
            border-bottom: 1px solid #d1d5db;
            padding: 10px 14px;
            text-align: right;
        }

        .preview-toolbar a {
            display: inline-block;
            text-decoration: none;
            color: #ffffff;
            background: #198754;
            border: 1px solid #198754;
            padding: 6px 12px;
            font-size: 12px;
            margin-left: 8px;
        }
    </style>
    @include($styleView)
</head>
<body class="{{ !empty($preview_mode) ? 'preview-mode' : '' }}">
    @if(!empty($preview_mode))
        <div class="preview-toolbar no-print">
            <a href="javascript:window.print()">Print</a>
            @if(!empty($download_url))
                <a href="{{ $download_url }}">Download PDF</a>
            @endif
        </div>
    @endif

	    <div class="invoice-document">
		        @foreach($itemPages as $pageItems)
		            <div class="invoice-page {{ $loop->last ? 'invoice-page-last' : '' }}">
                        @php
                            $showFooterValues = $loop->last;
                        @endphp
		                <table class="invoice-shell">
	                    <tbody>
	                        @foreach($preItemSections as $section)
	                            @php
	                                $sectionView = 'pdf.invoice.sections.' . $section->section_key;
	                                $sectionSettings = is_array($section->settings_json) ? $section->settings_json : [];
	                            @endphp

                            @if(view()->exists($sectionView))
                                @include($sectionView, [
                                    'section' => $section,
                                    'settings' => $sectionSettings,
                                    'invoice' => $invoice,
                                    'template' => $template,
                                    'order' => $order,
                                    'pageItems' => $pageItems,
                                    'pageNumber' => $loop->parent->iteration,
                                    'totalPages' => $totalPages,
                                    'itemsPerPage' => $itemsPerPage,
                                ])
                            @else
                                <tr>
                                    <td class="section-cell">
                                        <table class="section-table">
                                            <tr>
                                                <td class="section-title">{{ $section->section_label }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted small-text">Custom section key: {{ $section->section_key }}</td>
                                            </tr>
                                            @foreach($sectionSettings as $key => $value)
                                                <tr>
                                                    <td class="small-text">
                                                        <strong>{{ str_replace('_', ' ', ucfirst($key)) }}:</strong>
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
                                            @endforeach
                                        </table>
	                                    </td>
	                                </tr>
	                            @endif
	                        @endforeach

                            @if($itemsSection)
                                @include('pdf.invoice.sections.items_table', [
                                    'section' => $itemsSection,
                                    'settings' => $itemsSettings,
                                    'invoice' => $invoice,
                                    'template' => $template,
                                    'order' => $order,
                                    'pageItems' => $pageItems,
                                    'pageNumber' => $loop->iteration,
                                    'totalPages' => $totalPages,
                                    'itemsPerPage' => $itemsPerPage,
                                ])
                            @endif

                            @if($taxSection || $bankSection || $amountWordsSection || $totalsSection)
                                <tr>
                                    <td class="section-cell">
                                        <table class="bottom-summary-wrap">
                                            <tr>
                                                <td class="bottom-summary-left">
                                                    @if($taxSection)
                                                        <table class="summary-table bottom-box-table">
                                                            <tr>
                                                                <td colspan="2" class="section-heading">{{ $taxSection->section_label }}</td>
                                                            </tr>
                                                            @foreach(($invoice['taxes'] ?? []) as $tax)
                                                                <tr>
                                                                    <td>{{ $tax['label'] }}</td>
                                                                    <td class="text-right">{{ $showFooterValues ? number_format($tax['amount'], 2) : '' }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </table>
                                                    @endif

                                                    @if($bankSection)
                                                        <table class="summary-table bottom-box-table">
                                                            <tr>
                                                                <td colspan="2" class="section-heading">{{ $bankSection->section_label }}</td>
                                                            </tr>
                                                            @if(!empty($bankSettings['show_account_name']))
                                                                <tr>
                                                                    <td style="width: 34%;">Account Name</td>
                                                                    <td>{{ $invoice['bank']['account_name'] ?? '' }}</td>
                                                                </tr>
                                                            @endif
                                                            @if(!empty($bankSettings['show_account_number']))
                                                                <tr>
                                                                    <td>Account No.</td>
                                                                    <td>{{ $invoice['bank']['account_number'] ?? '' }}</td>
                                                                </tr>
                                                            @endif
                                                            @if(!empty($bankSettings['show_ifsc']))
                                                                <tr>
                                                                    <td>IFSC</td>
                                                                    <td>{{ $invoice['bank']['ifsc'] ?? '' }}</td>
                                                                </tr>
                                                            @endif
                                                            <tr>
                                                                <td>Bank Name</td>
                                                                <td>{{ $invoice['bank']['bank_name'] ?? '' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Branch</td>
                                                                <td>{{ $invoice['bank']['branch_name'] ?? '' }}</td>
                                                            </tr>
                                                        </table>
                                                    @endif

                                                    @if($amountWordsSection)
                                                        <table class="section-table bottom-box-table">
                                                            <tr>
                                                                <td class="section-heading">{{ $amountWordsSettings['label'] ?? $amountWordsSection->section_label }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="amount-words-row">
                                                                    @if($showFooterValues)
                                                                        <strong>{{ $invoice['summary']['amount_in_words'] ?? '' }}</strong>
                                                                    @else
                                                                        &nbsp;
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    @endif
                                                </td>
                                                <td class="bottom-summary-right">
                                                    @if($totalsSection)
                                                        <table class="totals-table bottom-box-table">
                                                            <tr>
                                                                <td colspan="2" class="section-heading">{{ $totalsSection->section_label }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Sub Total</td>
                                                                <td class="text-right">{{ $showFooterValues ? number_format($invoice['summary']['sub_total'] ?? 0, 2) : '' }}</td>
                                                            </tr>
                                                            @if(($invoice['summary']['discount_total'] ?? 0) > 0)
                                                                <tr>
                                                                    <td>Discount</td>
                                                                    <td class="text-right">{{ $showFooterValues ? ('- ' . number_format($invoice['summary']['discount_total'], 2)) : '' }}</td>
                                                                </tr>
                                                            @endif
                                                            <tr>
                                                                <td>Tax Total</td>
                                                                <td class="text-right">{{ $showFooterValues ? number_format($invoice['summary']['tax_total'] ?? 0, 2) : '' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Transport Charge</td>
                                                                <td class="text-right">{{ $showFooterValues ? number_format($invoice['summary']['transport_charge'] ?? 0, 2) : '' }}</td>
                                                            </tr>
                                                            <tr class="{{ !empty($totalsSettings['highlight_grand_total']) ? 'totals-highlight' : '' }}">
                                                                <td><strong>Grand Total</strong></td>
                                                                <td class="text-right"><strong>{{ $showFooterValues ? number_format($invoice['summary']['grand_total'] ?? 0, 2) : '' }}</strong></td>
                                                            </tr>
                                                        </table>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            @endif

                            @if($loop->last)
                                @foreach($extraLastPageSections as $section)
                                    @php
                                        $sectionView = 'pdf.invoice.sections.' . $section->section_key;
                                        $sectionSettings = is_array($section->settings_json) ? $section->settings_json : [];
                                    @endphp

                                    @if(view()->exists($sectionView))
                                        @include($sectionView, [
                                            'section' => $section,
                                            'settings' => $sectionSettings,
                                            'invoice' => $invoice,
                                            'template' => $template,
                                            'order' => $order,
                                            'pageItems' => $pageItems,
                                            'pageNumber' => $totalPages,
                                            'totalPages' => $totalPages,
                                            'itemsPerPage' => $itemsPerPage,
                                        ])
                                    @endif
                                @endforeach
                            @endif

                            @if($termsSection || $signatureSection)
                                <tr>
                                    <td class="section-cell">
                                        <table class="terms-sign-wrap">
                                            <tr>
                                                <td class="terms-sign-left">
                                                    @if($termsSection)
                                                        <div class="footer-terms-title">{{ $termsSettings['label'] ?? $termsSection->section_label }}</div>
                                                        <ol class="terms-list footer-terms-list">
                                                            @foreach(($invoice['terms'] ?? []) as $term)
                                                                <li>{{ $term }}</li>
                                                            @endforeach
                                                        </ol>
                                                    @endif
                                                </td>
                                                <td class="terms-sign-right">
                                                    @if($signatureSection)
                                                        <div>For {{ $invoice['company']['name'] ?? '' }}</div>
                                                        <div class="signature-fixed-space"></div>
                                                        <div>{{ $signatureSettings['label'] ?? 'Authorised Signatory' }}</div>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="footer-bar-inline">
                                                    &nbsp;
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            @endif
	                    </tbody>
	                </table>
	            </div>
	        @endforeach
	    </div>
</body>
</html>
