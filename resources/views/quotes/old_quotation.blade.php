<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quotation</title>
    <style>
         @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: normal;
            src: url("{{ asset('fonts/THSarabunNew.ttf') }}") format('truetype');
        }

        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 13px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 10px;
            background: #fff;
        }
        .container {
            width: 100%;
            border: 1px solid #ddd;
            padding: 10px;
        }

        /* Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        .header-table td {
            vertical-align: top;
            padding: 5px;
        }
        .company-name {
            font-size: 15px;
            font-weight: bold;
            color: #2c5282;
        }
        .quotation-title {
            font-size: 22px;
            font-weight: bold;
            color: #2c5282;
            text-align: right;
        }

        /* Details section */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .details-table td {
            width: 50%;
            vertical-align: top;
            padding: 10px;
            border: 1px solid #ddd;
            background: #f9f9f9;
        }
        .section-title {
            font-weight: bold;
            font-size: 15px;
            color: #2c5282;
            margin-bottom: 6px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 4px;
        }

        /* Table */
        table.products {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .products th {
            background: #2c5282;
            color: #fff;
            padding: 5px;
            border: 0.5px solid #1e40af;
            text-align: left;
            font-size: 10px;
        }
        .products td {
            border: 0.5px solid #ddd;
            padding: 5px;
            font-size: 10px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        /* Totals + QR */
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .totals-table td {
            vertical-align: top;
            padding: 10px;
        }
        .total-label {
            text-align: right;
            font-weight: bold;
            padding-right: 10px;
        }
        .grand-total {
            border-top: 2px solid #2c5282;
            padding-top: 6px;
            font-size: 15px;
            color: #2c5282;
        }
        .qr-box {
            border: 1px solid #ddd;
            background: #f9f9f9;
            text-align: center;
            padding: 10px;
        }
        .qr-box img {
            width: 120px;
            height: 120px;
        }
        .payment-info {
            font-size: 12px;
            margin-top: 5px;
        }

        /* Notices */
        .validity {
            margin-top: 15px;
            padding: 10px;
            background: #fff6e6;
            border: 1px solid #ffb347;
            text-align: center;
            font-weight: bold;
            color: #e67700;
        }
        .notes {
            margin-top: 15px;
            padding: 10px;
            border: 1px solid #2c5282;
            background: #f1f7ff;
        }
        .notes-title {
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 8px;
        }

        /* Signatures */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        .signature-table td {
            width: 50%;
            padding: 10px;
            border-top: 1px solid #ddd;
            vertical-align: top;
        }
        .signature-label {
            font-weight: bold;
            color: #2c5282;
        }

        /* Footer */
        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 12px;
            color: #718096;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .thank-you {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #2c5282;
        }

        table.image-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .image-table th {
            background: #2c5282;
            color: #fff;
            padding: 5px;
            border: none !important;
            text-align: left;
            font-size: 10px;
        }

        .image-table td {
            border: none !important;
            padding: 5px;
            font-size: 10px;
        }
    </style>
</head>
<body>
<div class="container">

    @php
        $default_img =  \App\Models\Utility::defaultImage();
        $company_address = \App\Models\Utility::getSetting('company_address_id');
        $address = null;
        if($company_address){
            $address = \App\Models\Address::find($company_address);
        }

        $validUntil = date('Y-m-d', strtotime($quote_id['date'] . ' + 7 days'));

        $logoPath = \App\Models\Utility::websiteLogo(true);

        $customer = \App\Models\Entity::where('id',$quote_id->customer_id)->first();
        if($customer && $customer->billing_address_id)
        {
            $cust_adr = \App\Models\Address::where('id',$customer->billing_address_id)->first();
            if($cust_adr)
            {
                $cust_city_name = \App\Models\City::where('id',$cust_adr->city)->first();
            }
        }

    @endphp
    <!-- Header -->
    <table class="header-table">
        <tr>
            <td>
                <div>
                    @if(!empty($logoPath) && file_exists($logoPath))
                        <img src="{{ $logoPath }}" width="150">
                    @endif
                </div>
                {{-- <div class="company-name">
                    {{ (\App\Models\Utility::setting('websiteName')) ?? "" }}
                </div> --}}
                <div>{{ optional($address)->address_line_1 }}</div>
                <div>{{ optional($address)->address_line_2 }}</div>
                <div>{{ optional(optional($address)->get_city)->name }}, {{ optional(optional($address)->get_state)->name }} {{ optional($address)->zipcode }}</div>
                <div>Phone: {{ (\App\Models\Utility::getSetting('phone')) ?? "" }} | Email: {{ (\App\Models\Utility::getSetting('email')) ?? "" }}</div>
            </td>
            <td style="text-align:right;">
                <div class="quotation-title">QUOTATION</div>
                <div><strong>Quotation #:</strong> {{ $quote_id['code'] }}</div>
                <div><strong>Date:</strong> {{ \App\Models\Utility::getDateFormated($quote_id['date']) }}</div>
                <div><strong>Valid Until:</strong> {{ \App\Models\Utility::getDateFormated($validUntil) }}</div>
            </td>
        </tr>
    </table>

    <!-- Details -->
    <table class="details-table">
        <tr>
            <td>
                <div class="section-title">Prepared For:</div>
                <div>
                    <strong>
                        {{ $customer->name ?? ''}}
                    </strong>
                </div>


                <div>{{ optional($cust_adr)->address_line_1 }}, {{ optional($cust_adr)->address_line_2 }}</div>
                <div>{{ optional(optional($cust_adr)->get_city)->name }}, {{ optional(optional($cust_adr)->get_state)->name }} {{ optional($cust_adr)->zipcode }}</div>

                @php

                    $primary = $quote_id->customerPhone()->where('is_primary',1)->first();

                @endphp

                <div>Phone: {{ optional($primary)->phone }}</div>
                <div>Email: {{ optional($customer)->email }}</div>
            </td>
            <td>
                <div class="section-title">Quotation Details:</div>
                {{-- <div><strong>Prepared By:</strong> {{ optional($quote_id)->createdBy->name }}</div> --}}
                <div>Sales Representative</div>
                <div>Phone: {{ optional($quote_id)->createdBy->phone }}</div>
                <div>Email: {{ optional($quote_id)->createdBy->email }}</div>
                <div><strong>Payment Terms:</strong>  {{ $quote_id['is_advance_payment'] ? 'Advance' : 'Net ' . $quote_id['payment_after_days'] . ' Days' }}</div>
                {{-- <div><strong>Delivery:</strong> Included (within NYC)</div> --}}
            </td>
        </tr>
    </table>

    <!-- Products -->
    <table class="products">
        <thead>
        <tr>
            <th>Description</th>
            <th class="text-center">SKU</th>
            {{-- <th class="text-center">HSN</th> --}}
            <th class="text-center">Qty</th>
            <th class="text-right">Price</th>
            @if($check_discount_allow == 1)
            <th class="text-right">Disc %</th>
            @endif
            {{-- <th>
                 @if (!empty($quote_id?->tax_detail_json))
                        @php
                            $taxDetails = json_decode($quote_id->tax_detail_json, true);
                        @endphp

                        @if (json_last_error() === JSON_ERROR_NONE && is_array($taxDetails))
                            @foreach ($taxDetails as $key => $value)

                                    {{ $key }}@if (!$loop->last),@endif

                            @endforeach

                        @endif
                    @endif
            </th> --}}
            <th class="text-right">Amount</th>
        </tr>
        </thead>
        <tbody>

            @php
                $sb_total = 0;
                $dis_val = 0;
            @endphp

            @foreach ($quote_products as $key => $product)
                @php
                    $product_detail = \App\Models\Products::where('id',$product['product_id'])->first();

                    if($product)
                    {
                        $sb_total += $product->total;
                        $product_total = $product->qty * $product->price;
                        $dis_val += ($product_total * $product->discount) / 100;
                    }
                @endphp

            <tr>
                <td>
                    @php
                        $imagePath = str_replace(url('/'), base_path(), $product_detail['image'] ?? $default_img);
                    @endphp
                    <table class="image-table">
                        <tr>
                            <td>
                                <img src="{{ $imagePath }}" width="50px">
                            </td>
                             <td>
                                {{ $product_detail['name'] }} <br>
                                <small>{{ $product->short_notes }}</small>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="text-center">{{ $product_detail['sku_code'] }}</td>
                {{-- <td class="text-center">{{ $product_detail['hsn_code'] }}</td> --}}
                <td class="text-center">{{ $product['qty'] }}</td>
                <td class="text-right">{{ number_format($product['price'],2) }}</td>
                @if($check_discount_allow == 1)
                <td class="text-right">{{ number_format($product['discount'],2) }}</td>
                @endif
                {{-- <td class="text-right">
                    {{ $product['tax'] }}
                </td> --}}
                <td class="text-right">{{ number_format($product['total'],2) }}</td>
            </tr>

           @endforeach
        </tbody>
    </table>

    <!-- Totals + QR -->
    <table class="totals-table">
        <tr>
            <td width="40%">
                {{-- <div class="qr-box">
                    <img src="{{ $qrCode }}" alt="QR Code">

                    <img src="{{ public_path('uploads/quote_qr/QR_001.png') }}" alt="QR Code">
                    <div class="payment-info">
                        <p><strong>Scan QR code for quick payment</strong></p>
                        <p>Bank Transfer, Credit Card, PayPal</p>
                    </div>
                </div> --}}
            </td>
            <td width="30%">
                @if($check_discount_allow == 1)
                 <div class="total-label">Discount :</div>
                 @endif
                <div class="total-label">Subtotal:</div>

                <div class="total-label">Tax (
                     @if (!empty($quote_id?->tax_detail_json))
                        @php
                            $taxDetails = json_decode($quote_id->tax_detail_json, true);
                        @endphp

                        @if (json_last_error() === JSON_ERROR_NONE && is_array($taxDetails))
                            @foreach ($taxDetails as $key => $value)

                                  {{ $key }}={{ $value }}% @if(!$loop->last),@endif

                            @endforeach

                        @endif
                    @endif
                    )
                    {{-- ({{ $quote_id->gst }}) %: --}}
                </div>
                <div class="total-label grand-total">TOTAL:</div>
            </td>
            <td width="30%">
                @if($check_discount_allow == 1)
                 <div class="text-right">- {{ $dis_val }}</div>
                @endif
                <div class="text-right">{{ $sb_total  }}</div>

                <div class="text-right"> + {{ $quote_id->gst }} </div>
                <div class="text-right grand-total">{{ $quote_id->grand_total }}</div>
            </td>

        </tr>
    </table>

    <!-- Notices -->
    <div class="validity">This quotation is valid for 7 days from the date of issue</div>

    <div style="page-break-after: always;"></div>

    <div class="notes">
        <div class="notes-title">Terms & Notes:</div>
        {{-- <p>1. Prices include standard packaging and delivery within India</p>
        <p>2. Delivery outside India may incur additional charges</p>
        <p>3. All products come with a 3-months manufacturer's warranty</p>
        <p>4. Professional installation available at additional cost</p>
        <p>5. Payment via QR code: Scan with your banking app to pay instantly</p> --}}
    </div>

    <!-- Signatures -->
    {{-- <table class="signature-table">
        <tr>
            <td>
                <div class="signature-label">Authorized Signature ({{ (\App\Models\Utility::getSetting('website_name')) ?? "" }}):</div>
                <div>Name: {{ optional($quote_id)->createdBy->name }}</div>
                <div>Date: {{ \App\Models\Utility::getDateFormated(date('Y-m-d')) }}</div>
            </td>
            <td>
                <div class="signature-label">Client Acceptance:</div>
                <div>Name: ___________________________</div>
                <div>Date: ___________________________</div>
            </td>
        </tr>
    </table> --}}

    <!-- Footer -->
    <div class="footer">
        <div class="thank-you">Thank you for considering {{ (\App\Models\Utility::getSetting('websiteName')) ?? "" }}!</div>
        {{-- <div>We are committed to providing you with the highest quality kitchenware products</div> --}}
        <div>Phone: {{ (\App\Models\Utility::getSetting('phone')) ?? "" }} | Email: {{ (\App\Models\Utility::getSetting('email')) ?? "" }} | Web: {{ (\App\Models\Utility::getSetting('website_url')) ?? "" }}</div>
    </div>

</div>
</body>
</html>
