<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ \App\Models\Utility::getSetting('website_name') ?? '' }}</title>
    <style>
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: normal;
            src: url("{{ asset('fonts/THSarabunNew.ttf') }}") format('truetype');
        }

        /* Page / Print */
        @page {
            size: A4;
            margin: 6mm;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            color: #000;
            -webkit-print-color-adjust: exact;
        }

        .cm-bg {
            background: #eaeaea;
        }

	        .sheet {
	            width: 190mm;
	            height: 274mm;
	            min-height: 274mm;
	            max-height: 274mm;
	            /* A4 minus margins */
		            margin: 5mm auto 0 auto;
	            padding: 0 0 10mm 0;
	            border: 0.8pt solid #000;
	            box-sizing: border-box;
	            font-size: 9px;
	            line-height: 1.1;
	            overflow: hidden;
	            position: relative;
	        }

		        .sheet.quote-page-break {
		            page-break-after: always;
		        }

        /* Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .header-table td {
            vertical-align: top;
            padding: 4px;
        }

        .company-title {
            font-size: 18px;
            font-weight: 700;
            text-align: center;
            border: 0.8pt solid #000;
        }

        .company-addr {
            font-size: 9px;
            margin-top: 2px;
        }

        .cm-border-bottom {
            border-bottom: 0.8pt solid #000;
        }

        .title-box {
            border-top: 0.8pt solid #000;
            border-bottom: 0.8pt solid #000;

            width: 100%;
            margin-top: 6px;
        }

        .title-box td {
            padding: 3px;
            font-weight: 700;
        }

        /* Party / Details */
        .party {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }

        .party td {
            vertical-align: top;
            padding: 5px;
            border-bottom: none;
        }

        .party-left {
            width: 31%;
        }

        .party-left {
            border-right: 1pt solid #000;
            padding: 3px;
            box-sizing: border-box;
        }

        .party-right-wrap {
            width: 38%;
        }

        .party-right {
            padding: 0;
            box-sizing: border-box;
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 4px;
            font-size: 12px;
        }

        /* Items table - tuned widths for DOMPDF */
        .items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
            font-size: 9px;
            border-bottom: 0.8pt solid #000;
        }

        .items thead th {
            border: 0.8pt solid #000;
            padding: 3px;
            background: #f5f5f5;
            font-weight: 700;
        }

        .items tbody td {
            border-left: 0.8pt solid #000;
            border-right: 0.8pt solid #000;
            border-bottom: none;
            padding: 0;
            vertical-align: middle;
        }

        .items tbody tr.product-row td,
        .items tbody tr.blank-product-row td {
            height: 13px;
            line-height: 13px;
        }

        .items tbody tr.product-row td {
            vertical-align: top;
        }

        .items tbody tr.blank-product-row td {

            font-size: 1px;
        }

        .items tbody tr:first-child td {
            border-top: 0.8pt solid #000;
        }

        .items td.center {
            text-align: center;
        }

        .items td.right {
            text-align: right;
        }

        /* Column widths */
        .col-sr {
            width: 6mm;
        }

        .col-name {
            width: 50mm;
        }

        .col-hsn {
            width: 18mm;
        }

        .col-qty {
            width: 16mm;
        }

        .col-unit {
            width: 12mm;
        }

        .col-rate {
            width: 15mm;
        }

        .col-tax {
            width: 14mm;
        }

        .col-amt {
            width: 18mm;
        }

        /* Bottom totals */
        .bottom-wrap {
            width: 100%;
            margin: 0;
            padding: 0%;
        }

        .bottom-left {
            width: 64%;
        }

        .bottom-right {
            width: 36%;
            vertical-align: top;
            padding-left: 0;
        }

        .box {
            border: 1pt solid #000;
            padding: 3px;
            box-sizing: border-box;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 3px;
        }

        .bold {
            font-weight: 700;
        }

        .small {
            font-size: 10.2px;
        }

        /* Terms & Signature */
        .terms {
            margin-top: 0;
            margin-bottom: 6mm;
            border-top: 0.8pt solid #000;
            border-bottom: 0.8pt solid #000;
            padding: 3px;
            font-size: 9px;
            box-sizing: border-box;
        }

        .terms ol {
            margin-left: 14px;
            margin-top: 6px;
        }

        .signature {
            width: 100%;
            margin-top: 8px;
        }

        .signature td {
            vertical-align: bottom;
            padding: 3px;
        }

        .footer {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 4mm;
            text-align: center;
            margin-top: 0;
            font-size: 8px;
            color: #333;
            line-height: 9px;
            /* border-top: 1px solid #ddd; */
            padding-top: 0px;
        }

        /* Helpers */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Image-table inside product cell */
        table.image-table {
            border-collapse: collapse;
            width: 100%;
        }

        .image-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .image-table img {
            display: block;
        }


    </style>
</head>

<body>
    @php
        $default_img = \App\Models\Utility::defaultImage();
        $company_address = \App\Models\Utility::getSetting('company_address_id');
        $address = null;
        if ($company_address) {
            $address = \App\Models\Address::find($company_address);
        }

        $validUntil = date('Y-m-d', strtotime($quote_id['date'] . ' + 7 days'));
        $logoPath = \App\Models\Utility::websiteLogo(true);
        $company_gst = \App\Models\Utility::getSetting('gst_no');

        $customer = \App\Models\Entity::where('id', $quote_id->customer_id)->first();
        $showDiscount = (int) ($check_discount_allow ?? 0) === 1;
        $quotation_terms = $quotation_terms ?? [];
        $productsPerPage = 12;
        $blankRowHeight = '24px';
        $allQuoteProducts = collect($quote_products)->values();
        $quoteProductPages = $allQuoteProducts->chunk($productsPerPage);
        if ($quoteProductPages->isEmpty()) {
            $quoteProductPages = collect([collect()]);
        }

        $sb_total = 0;
        $dis_val = 0;
        $g_total = 0;
        foreach ($allQuoteProducts as $product) {
            $product_total = (float) $product->qty * (float) $product->price;
            $g_total += $product_total;
            $sb_total += $product_total;
            $dis_val += ($product_total * (float) ($product->discount ?? 0)) / 100;
        }
        $f_total = $g_total - ($showDiscount ? $dis_val : 0) + (float) $quote_id->gst;
    @endphp

    @foreach ($quoteProductPages as $pageIndex => $pageProducts)
    @php
        $isLastQuotePage = $loop->last;
    @endphp
    <div class="sheet {{ !$isLastQuotePage ? 'quote-page-break' : '' }}">

        <!-- HEADER -->
        <table class="header-table">
            <tr>
                <td style="width:80%; text-align: center">
                    <div class="company-title cm-bg">{{ \App\Models\Utility::getSetting('website_name') ?? '' }}</div>
                    <div class="company-addr">
                        {{ optional($address)->address_line_1 }}, {{ optional($address)->address_line_2 }}
                        {{ optional(optional($address)->get_city)->name }},
                        {{ optional(optional($address)->get_state)->name }} {{ optional($address)->zipcode }}
                    </div>
                    <div style="margin-top:4px;" class="small">
                        Mo.: {{ \App\Models\Utility::getSetting('phone') ?? '' }}
                    </div>
                </td>

                <td style="width:20%; text-align:right; vertical-align:middle;">
                    @if (!empty($logoPath))
                        <img src="{{ $logoPath }}" style="max-width:140px; max-height:70px;">
                    @endif
                </td>
            </tr>
        </table>

        <!-- TITLE BOX - keep wording QUOTATION -->
        <table class="title-box">
            <tr>
                <td style="width:20%;"></td>
                <td style="width:60%; text-align:center;">QUOTATION</td>
                <td style="width:20%; text-align:right;">Original</td>
            </tr>
        </table>

        <!-- PARTY DETAILS -->
        <table class="party">
            <tr>
                <td class="party-left">
                    <div class="section-title">Billing :</div>
                    <div>
                        <strong> {{ $customer->company_name ?? $customer->name }}</strong>
                    </div>


                    @if ($customer->company_name)
                        <div>Attn: {{ optional(optional($quote_id)->customer)->name ?? '' }}</div>
                    @endif

                    @php

                        $primary = $quote_id->customerPhone()->where('is_primary', 1)->first();
                        if ($customer && !empty($customer->billing_address_id)) {
                            $cust_adr = \App\Models\Address::where('id', $customer->billing_address_id)->first();
                            if ($cust_adr) {
                                $cust_city_name = \App\Models\City::where('id', $cust_adr->city)->first();
                            }
                        }
                    @endphp

                    <div style="margin-top:6px;">
                        @if ($customer)
                            @if (!empty($cust_adr->address_line_1))
                                {{ $cust_adr->address_line_1 }}
                            @endif

                            @if (!empty($cust_adr->address_line_2))
                                , {{ $cust_adr->address_line_2 }}
                            @endif
                            <br>

                            @if (optional($cust_adr->get_city)->name)
                                {{ optional($cust_adr->get_city)->name }}
                            @endif

                            @if (optional($cust_adr->get_state)->name)
                                , {{ optional($cust_adr->get_state)->name }}
                            @endif

                            @if (!empty($cust_adr->zipcode))
                                {{ $cust_adr->zipcode }}
                            @endif
                        @endif
                    </div>

                    <div style="margin-top:6px;">
                        Phone: {{ optional($primary)->phone }}<br>
                        GST No: {{ optional($customer)->gst_no ?? '' }}
                    </div>
                </td>
                <td class="party-left">
                    <div class="section-title">Shippng :</div>
                    <div>
                        @if ($customer)
                            <strong>
                                {{ $customer->company_name ?? $customer->name }}
                            </strong>
                        @endif
                    </div>

                    @if ($customer && $customer->company_name)
                        <div>Attn: {{ $customer->name ?? '' }}</div>
                    @endif

                    @php

                        if ($customer && !empty($customer->shipping_address_id)) {
                            $cus_address_ship = \App\Models\Address::where(
                                'id',
                                $customer->shipping_address_id,
                            )->first();
                        }
                    @endphp
                    <div style="margin-top:6px;">
                        @if ($cus_address_ship)
                            @if (!empty($cus_address_ship->address_line_1))
                                {{ $cus_address_ship->address_line_1 }}
                            @endif

                            @if (!empty($cus_address_ship->address_line_2))
                                , {{ $cus_address_ship->address_line_2 }}
                            @endif
                            <br>

                            @if (optional($cus_address_ship->get_city)->name)
                                {{ optional($cus_address_ship->get_city)->name }}
                            @endif

                            @if (optional($cus_address_ship->get_state)->name)
                                , {{ optional($cus_address_ship->get_state)->name }}
                            @endif

                            @if (!empty($cus_address_ship->zipcode))
                                {{ $cus_address_ship->zipcode }}
                            @endif
                        @endif
                    </div>

                    <div style="margin-top:6px;">
                        Phone: {{ optional($primary)->phone }}<br>
                        GST No: @if ($customer)
                            {{ $customer->gst_no }}
                        @endif
                    </div>
                </td>

                <td class="party-right-wrap" style="padding: 1px">
                    <table style="width:100%; border-bottom:1px solid #000" class="party-right cm-bg">
                        <tr>
                            <td><strong>Quotation #:</strong></td>
                            <td>{{ $quote_id['code'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Date:</strong></td>
                            <td>{{ \App\Models\Utility::getDateFormated($quote_id['date']) }}</td>
                        </tr>

                    </table>
                    <table style="width:100%;" class="party-right">
                        <tr>
                            <td><strong>Transport:</strong></td>
                            <td>{{ $quote_id->get_transport->name ?? '' }}
                                @php
                                    if ($quote_id->get_transport) {
                                        $contacts = json_decode($quote_id->get_transport->contact_json, true);
                                    }

                                @endphp
                                @if (!empty($contacts) && is_array($contacts))
                                    <br>{{ $contacts[0] ?? '' }}
                                @endif

                            </td>
                        </tr>

                        <tr>
                            <td><strong>L.R. No :</strong></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td><strong>Payment Terms :</strong></td>
                            <td>
                                {{ $quote_id['is_advance_payment'] ? 'Ad' : ' ' . $quote_id['payment_after_days'] . ' Days' }}
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
        </table>

        <!-- ITEMS TABLE -->
        <table class="items" role="table">
            <thead>
                <tr>
                    <th class="col-sr text-center">SrNo</th>
                    <th class="col-name"> Product Name</th>
                    <th class="col-hsn text-center">HSN/SAC</th>
                    <th class="col-qty text-center">Qty</th>
                    <th class="col-unit text-center">Unit</th>
                    <th class="col-rate text-center">Rate</th>
                    @if ($showDiscount)
                        <th class="col-tax text-center">Discount</th>
                    @endif
                    <th class="col-rate text-center">
                        @php
                            $tax_all = json_decode($quote_id->tax_detail_json, true);


                            if (!empty($tax_all) && is_array($tax_all)) {
                                $activeTaxes = [];

                                foreach ($tax_all as $key => $value) {
                                    if ((int)$value === 1) {
                                        $activeTaxes[] = $key;
                                    }
                                }

                                echo implode(' + ', $activeTaxes);
                            }
                            else
                            {
                                echo 'Tax';
                            }
                        @endphp

                    </th>

                    <th class="col-amt text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
		                @php
		                    $product_count = count($pageProducts);
		                    $reming_items = max(0, $productsPerPage - $product_count);
		                    $itemColumnCount = $showDiscount ? 9 : 8;
		                @endphp

	                @foreach ($pageProducts as $key => $product)
	                    @php
	                        $product_detail = \App\Models\Products::where('id', $product['product_id'])->first();
                            $listing_detail = $product->marketplaceListing;
	                        $product_total = $product ? ((float) $product->qty * (float) $product->price) : 0;
	                        $unit_nm = \App\Models\Units::where('id', $product->unit_id)->first();
	                    @endphp

		                    <tr class="product-row">
	                        <td class="center" style="border-left:none ">{{ ($pageIndex * $productsPerPage) + $loop->iteration }}</td>
                        <td>
                            <table class="image-table">
                                <tr>
                                    <td style="padding:2px 5px; vertical-align:middle;  border: none;">
                                        <div class="bold">{{ $listing_detail?->platform_sku ?? $product_detail['sku_code'] }} -
                                            {{ $listing_detail?->listing_title ?? $product_detail['name'] }}</div>
                                        @if($listing_detail)
                                            <div class="small">{{ ucfirst($listing_detail->platform ?? '') }} listing</div>
                                        @endif
                                        <div class="small">({{ $product->short_notes ?? '' }})</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td class="center">{{ $product_detail['hsn_code'] ?? ($product_detail['hsn'] ?? '') }}</td>
                        <td class="center">{{ $product['qty'] }}</td>
                        <td class="center">{{ $unit_nm->name ?? '' }}</td>
                        <td class="center">{{ number_format($product['price'], 2) }}</td>
                        @if ($showDiscount)
                            <td class="center">{{ number_format($product->discount ?? 0, 2) }} %</td>
                        @endif
                        <td class="center">{{ $product->tax ?? 0 }} %</td>
                        <td class="text-right" style="border-right:none; ">
                            {{ number_format($product_total, 2) }}</td>
                    </tr>
	                @endforeach
		                @if ($reming_items > 0)
		                    @for ($i = 0; $i < $reming_items; $i++)
                        <tr class="blank-product-row">
                            @for ($blankCell = 1; $blankCell <= $itemColumnCount; $blankCell++)
                                <td
                                    class="{{ $blankCell === 1 ? 'center' : ($blankCell === $itemColumnCount ? 'text-right' : '') }}"
                                    style="{{ $blankCell === 1 ? 'border-left:none; height: ' . $blankRowHeight . ';' : '' }} {{ $blankCell === $itemColumnCount ? 'border-right:none; padding: 1px;' : '' }}"
                                >&nbsp;</td>
                            @endfor
                        </tr>
                    @endfor
                @endif
            </tbody>
        </table>

		        @php
		            $f_total = $g_total - ($showDiscount ? $dis_val : 0) + (float) $quote_id->gst;
                    $amountInWords = '';
                    $taxLabel = 'Tax';
                    $taxAmountLabel = '';

                    if ($isLastQuotePage) {
                        $amountInWords = number_format((float) $f_total, 2) . ' only';
                        if (class_exists('\NumberFormatter')) {
                            $formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
                            $amountInWords = ucfirst($formatter->format(round((float) $f_total, 2))) . ' Rupees';
                        }

                        $taxAmountLabel = '+ ' . number_format($quote_id->gst, 2);
                        if (!empty($quote_id?->tax_detail_json)) {
                            $taxDetails = json_decode($quote_id->tax_detail_json, true);
                            $activeTaxes = [];

                            if (json_last_error() === JSON_ERROR_NONE && is_array($taxDetails)) {
                                foreach ($taxDetails as $key => $value) {
                                    if ((int) $value === 1) {
                                        $activeTaxes[] = $key;
                                    }
                                }
                            }

                            if (!empty($activeTaxes)) {
                                $gst_div = $quote_id->gst / count($activeTaxes);
                                $taxParts = [];
                                foreach ($activeTaxes as $tax) {
                                    $taxParts[] = $tax . '=' . number_format($gst_div, 2);
                                }
                                $taxLabel = 'Tax (' . implode(', ', $taxParts) . ')';
                            }
                        }
                    }
		        @endphp

        <!-- Bottom Note + Totals -->
        <table class="bottom-wrap">
            <tr>
                <td class="bottom-left" style="border-right:1px solid #000 ">
                    <div class="" style="min-height:22mm;">
                        <div>
                            <div style="min-height: 4mm;">
                                {{-- <b>Note :</b> {{ $quote_id['notes'] ?? '' }} --}}
                            </div>
                        </div>
                        <table class="title-box">
                            <tr>
                                <td style="width:20%;">Prev.Bal. 0.00</td>
	                                <td style="width:60%; text-align:center;">This Bill {{ $isLastQuotePage ? number_format($f_total, 2) : '' }}
	                                </td>
	                                <td style="width:20%; text-align:right;">Total {{ $isLastQuotePage ? number_format($f_total, 2) : '' }}</td>
                            </tr>
                        </table>
                        <table style="width:100%;">
                            <tr>
                                <td style="width:80%;">
                                    <div class="small"><b>Bank Name :</b> {{ $bank_detail->bank_name ?? '' }}
                                    </div>
                                    <div class="small"><b>Bank A/c. No. :</b> {{ $bank_detail->account_no ?? '' }}
                                    </div>
                                    <div class="small"><b>IFSC Code :</b> {{ $bank_detail->ifsc_code ?? '' }}
                                    </div>
                                    <div class="small"><b>Branch Name :</b> {{ $bank_detail->branch_name ?? '' }}
                                    </div>
                                </td>
                                <td style="width:20%; text-align:center;">
                                    @if (false && file_exists(public_path('uploads/quote_qr/QR_001.png')))
                                        <img src="{{ public_path('uploads/quote_qr/QR_001.png') }}" alt="QR Code"
                                            style="width:50px; height:50px;">
                                    @endif
                                </td>
                            </tr>
                        </table>

                        <div style="margin-top:6px;">

	                            <div style="margin-top:4px;" class="bold"><b>Bill Amount
	                                    :</b>{{ $amountInWords }}</div>
                        </div>
                    </div>
                </td>

                <td class="bottom-right">
                    <table class="totals-table cm-bg" style="border-bottom:1px solid #000 ">
                        <tr>
                            <td>Subtotal</td>
	                            <td class="text-right">{{ $isLastQuotePage ? number_format($sb_total, 2) : '' }}
	                            </td>
                        </tr>
                    </table>
                    <table class="totals-table" style="border-bottom:1px solid #000 ">
	                        @if ($showDiscount)
	                            <tr>
	                                <td>Discount</td>
	                                <td class="text-right">{{ $isLastQuotePage ? '- ' . number_format($dis_val, 2) : '' }}</td>
                            </tr>
                        @endif
                        {{-- <tr>
                            <td>Taxable Amount</td>
                            <td class="text-right">{{ number_format($sb_total - $dis_val, 2) }}</td>
                        </tr> --}}
	                        <tr>
		                            <td>{{ $taxLabel }}</td>
		                            <td class="text-right">{{ $taxAmountLabel }}</td>
	                        </tr>
                        {{-- <tr>
                            <td>Round Off</td>
                            <td class="text-right">{{ number_format($quote_id->round_off ?? 0, 2) }}</td>
                        </tr> --}}
                        <tr style="background:#f5f5f5; font-weight:700; border-top: 0.8pt solid #000 ">
                            <td class="bold">TOTAL</td>
	                            <td class="text-right bold">{{ $isLastQuotePage ? number_format($f_total, 2) : '' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>



        <table class=" terms" style="margin-top:0;width:100%;">
            <tr>
                <td style="width:70%;">

	                    <div>
	                        <div class="bold">Terms &amp; Condition :</div>
	                        @if (!empty($quotation_terms))
	                            <ol>
	                                @foreach ($quotation_terms as $term)
	                                    <li >{{ $term }}</li>
	                                @endforeach
	                            </ol>
	                        @endif

	                        <div style="margin-top:6px;"><b>GSTIN No. :</b> {{ $company_gst ?? '' }}</div>
	                    </div>
                </td>
                <td>
                    <div style="margin-top: 35px">
                        <div>For, {{ \App\Models\Utility::getSetting('website_name') ?? '' }}</div>
                        <div>(Authorised Signatory)</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer">
            <div class="thank-you">Thank you for considering
                {{ \App\Models\Utility::getSetting('website_name') ?? '' }}!
            </div>
            <div>Phone: {{ \App\Models\Utility::getSetting('phone') ?? '' }} | Email:
                {{ \App\Models\Utility::getSetting('email') ?? '' }}

            </div>

        </div>
    </div>
    @endforeach
</body>

</html>
