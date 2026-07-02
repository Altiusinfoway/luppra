@php
    $invoice_terms = $invoice_terms ?? [];
    $showDiscount = (int) ($check_discount_allow ?? 0) === 1;
    $productsPerPage = 8;
    $blankRowHeight = '35px';
    $allOrderProducts = collect($order_products)->values();
    $orderProductPages = $allOrderProducts->chunk($productsPerPage);

    if ($orderProductPages->isEmpty()) {
        $orderProductPages = collect([collect()]);
    }

    $company_address = \App\Models\Utility::getSetting('company_address_id');
    $address = $company_address ? \App\Models\Address::find($company_address) : null;
    $logoPath = \App\Models\Utility::websiteLogo($for_pdf ?? false);
    $company_gst = \App\Models\Utility::getSetting('gst_no');
    $customer = optional($order)->getCustomer;
    $billingAddress = optional($customer)->getBillingAddress;
    $shippingAddress = optional($customer)->getShippingAddress ?: $billingAddress;
    $primary = $order->customerPhone()->where('is_primary', 1)->first();
    $transportContacts = optional($order)->getTransport
        ? json_decode(optional($order->getTransport)->contact_json, true)
        : [];
    $activeTaxes = [];
    $tax_all = json_decode($order->tax_detail_json, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($tax_all)) {
        foreach ($tax_all as $key => $value) {
            if ((int) $value === 1) {
                $activeTaxes[] = $key;
            }
        }
    }

    $taxHeading = !empty($activeTaxes) ? implode(' + ', $activeTaxes) : 'Tax';

    $sb_total = 0;
    $dis_val = 0;
    $g_total = 0;
    foreach ($allOrderProducts as $product) {
        $product_total = (float) $product->qty * (float) $product->price;
        $g_total += $product_total;
        $sb_total += $product_total;
        $dis_val += ($product_total * (float) ($product->discount ?? 0)) / 100;
    }

    $transportCharge = (float) ($order->transport_charge ?? 0);
    $taxTotal = (float) ($order->gst ?? 0);
    $f_total = $g_total - ($showDiscount ? $dis_val : 0) + $taxTotal + $transportCharge;
@endphp

@foreach ($orderProductPages as $pageIndex => $pageProducts)
    @php
        $isLastInvoicePage = $loop->last;
        $product_count = count($pageProducts);
        $reming_items = max(0, $productsPerPage - $product_count);
        $itemColumnCount = $showDiscount ? 9 : 8;
        $amountInWords = '';
        $taxLabel = 'Tax';
        $taxAmountLabel = '';
        $transportAmountLabel = '';

        if ($isLastInvoicePage) {
            $amountInWords = number_format((float) $f_total, 2) . ' only';
            if (class_exists('\NumberFormatter')) {
                $formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
                $amountInWords = ucfirst($formatter->format(round((float) $f_total, 2))) . ' Rupees';
            }

            $taxAmountLabel = '+ ' . number_format($taxTotal, 2);
            $transportAmountLabel = '+ ' . number_format($transportCharge, 2);

            if (!empty($activeTaxes)) {
                $gst_div = $taxTotal / count($activeTaxes);
                $taxParts = [];
                foreach ($activeTaxes as $tax) {
                    $taxParts[] = $tax . '=' . number_format($gst_div, 2);
                }
                $taxLabel = 'Tax (' . implode(', ', $taxParts) . ')';
            }
        }
    @endphp

    <div class="sheet {{ !$isLastInvoicePage ? 'invoice-page-break' : '' }}">
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

        <table class="title-box">
            <tr>
                <td style="width:20%;"></td>
                <td style="width:60%; text-align:center;">TAX INVOICE</td>
                <td style="width:20%; text-align:right;">{{ ucfirst($print_option ?? 'Original') }}</td>
            </tr>
        </table>

        <table class="party">
            <tr>
                <td class="party-left">
                    <div class="section-title">Billing :</div>
                    <div>
                        <strong>{{ optional($customer)->company_name ?? optional($customer)->name }}</strong>
                    </div>

                    @if (!empty(optional($customer)->company_name))
                        <div>Attn: {{ optional($customer)->name ?? '' }}</div>
                    @endif

                    <div style="margin-top:6px;">
                        @if ($billingAddress)
                            @if (!empty($billingAddress->address_line_1))
                                {{ $billingAddress->address_line_1 }}
                            @endif
                            @if (!empty($billingAddress->address_line_2))
                                , {{ $billingAddress->address_line_2 }}
                            @endif
                            <br>
                            @if (optional($billingAddress->get_city)->name)
                                {{ optional($billingAddress->get_city)->name }}
                            @endif
                            @if (optional($billingAddress->get_state)->name)
                                , {{ optional($billingAddress->get_state)->name }}
                            @endif
                            @if (!empty($billingAddress->zipcode))
                                {{ $billingAddress->zipcode }}
                            @endif
                        @endif
                    </div>

                    <div style="margin-top:6px;">
                        Phone: {{ optional($primary)->phone }}<br>
                        GST No: {{ optional($customer)->gst_no ?? '' }}
                    </div>
                </td>

                <td class="party-left">
                    <div class="section-title">Shipping :</div>
                    <div>
                        <strong>{{ optional($customer)->company_name ?? optional($customer)->name }}</strong>
                    </div>

                    @if (!empty(optional($customer)->company_name))
                        <div>Attn: {{ optional($customer)->name ?? '' }}</div>
                    @endif

                    <div style="margin-top:6px;">
                        @if ($shippingAddress)
                            @if (!empty($shippingAddress->address_line_1))
                                {{ $shippingAddress->address_line_1 }}
                            @endif
                            @if (!empty($shippingAddress->address_line_2))
                                , {{ $shippingAddress->address_line_2 }}
                            @endif
                            <br>
                            @if (optional($shippingAddress->get_city)->name)
                                {{ optional($shippingAddress->get_city)->name }}
                            @endif
                            @if (optional($shippingAddress->get_state)->name)
                                , {{ optional($shippingAddress->get_state)->name }}
                            @endif
                            @if (!empty($shippingAddress->zipcode))
                                {{ $shippingAddress->zipcode }}
                            @endif
                        @endif
                    </div>

                    <div style="margin-top:6px;">
                        Phone: {{ optional($primary)->phone }}<br>
                        GST No: {{ optional($customer)->gst_no ?? '' }}
                    </div>
                </td>

                <td class="party-right-wrap" style="padding: 1px">
                    <table style="width:100%; border-bottom:1px solid #000" class="party-right cm-bg">
                        <tr>
                            <td><strong>Invoice #:</strong></td>
                            <td>{{ $order->bill_number ?? str_replace('ORDER', 'INV', $order->order_number) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Date:</strong></td>
                            <td>{{ \App\Models\Utility::getDateFormated($order['date']) }}</td>
                        </tr>
                    </table>
                    <table style="width:100%;" class="party-right">
                        <tr>
                            <td><strong>Transport:</strong></td>
                            <td>
                                {{ optional($order->getTransport)->name ?? '' }}
                                @if (!empty($transportContacts) && is_array($transportContacts))
                                    <br>{{ $transportContacts[0] ?? '' }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>L.R. No :</strong></td>
                            <td>{{ $order->lr_number ?? '' }}</td>
                        </tr>
                        <tr>
                            <td><strong>No Of Article :</strong></td>
                            <td>{{ $order->no_article ?? '' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Payment Terms :</strong></td>
                            <td>{{ $order['is_advance_payment'] ? 'Ad' : ' ' . $order['payment_after_days'] . ' Days' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="items" role="table">
            <thead>
                <tr>
                    <th class="col-sr text-center">SrNo</th>
                    <th class="col-name">Product Name</th>
                    <th class="col-hsn text-center">HSN/SAC</th>
                    <th class="col-qty text-center">Qty</th>
                    <th class="col-unit text-center">Unit</th>
                    <th class="col-rate text-center">Rate</th>
                    @if ($showDiscount)
                        <th class="col-tax text-center">Discount</th>
                    @endif
                    <th class="col-rate text-center">{{ $taxHeading }}</th>
                    <th class="col-amt text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pageProducts as $product)
                    @php
                        $product_detail = \App\Models\Products::where('id', $product['product_id'])->first();
                        $product_total = $product ? ((float) $product->qty * (float) $product->price) : 0;
                        $unit_nm = \App\Models\Units::where('id', $product->unit_id)->first();
                    @endphp

                    <tr class="product-row">
                        <td class="center" style="border-left:none;">{{ ($pageIndex * $productsPerPage) + $loop->iteration }}</td>
                        <td>
                            <table class="image-table">
                                <tr>
                                    <td style="padding:2px 5px; border:none;">
                                        <div class="bold">{{ $product_detail['sku_code'] ?? '' }} - {{ $product_detail['name'] ?? '' }}</div>
                                        <div class="small">{{ $product->short_notes ?? '' }}</div>
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
                        <td class="text-right" style="border-right:none; padding:1px;">{{ number_format($product_total, 2) }}</td>
                    </tr>
                @endforeach

                @if ($reming_items > 0)
                    @for ($i = 0; $i < $reming_items; $i++)
                        <tr class="blank-product-row">
                            @for ($blankCell = 1; $blankCell <= $itemColumnCount; $blankCell++)
                                <td
                                    class="{{ $blankCell === 1 ? 'center' : ($blankCell === $itemColumnCount ? 'text-right' : '') }}"
                                    style="{{ $blankCell === 1 ? 'border-left:none; height:' . $blankRowHeight . ';' : '' }} {{ $blankCell === $itemColumnCount ? 'border-right:none; padding:1px;' : '' }}"
                                >&nbsp;</td>
                            @endfor
                        </tr>
                    @endfor
                @endif
            </tbody>
        </table>

        <table class="bottom-wrap">
            <tr>
                <td class="bottom-left" style="border-right:1px solid #000;">
                    <div style="min-height:22mm;">
                        <div style="min-height:4mm;"></div>
                        <table class="title-box">
                            <tr>
                                <td style="width:20%;">Prev.Bal. 0.00</td>
                                <td style="width:60%; text-align:center;">This Bill {{ $isLastInvoicePage ? number_format($f_total, 2) : '' }}</td>
                                <td style="width:20%; text-align:right;">Total {{ $isLastInvoicePage ? number_format($f_total, 2) : '' }}</td>
                            </tr>
                        </table>
                        <table style="width:100%;">
                            <tr>
                                <td style="width:80%;">
                                    <div class="small"><b>Bank Name :</b> {{ $bank_detail->bank_name ?? '' }}</div>
                                    <div class="small"><b>Bank A/c. No. :</b> {{ $bank_detail->account_no ?? '' }}</div>
                                    <div class="small"><b>IFSC Code :</b> {{ $bank_detail->ifsc_code ?? '' }}</div>
                                    <div class="small"><b>Branch Name :</b> {{ $bank_detail->branch_name ?? '' }}</div>
                                </td>
                            </tr>
                        </table>

                        <div style="margin-top:6px;">
                            <div style="margin-top:4px;" class="bold"><b>Bill Amount :</b> {{ $amountInWords }}</div>
                        </div>
                    </div>
                </td>

                <td class="bottom-right">
                    <table class="totals-table cm-bg" style="border-bottom:1px solid #000;">
                        <tr>
                            <td>Subtotal</td>
                            <td class="text-right">{{ $isLastInvoicePage ? number_format($sb_total, 2) : '' }}</td>
                        </tr>
                    </table>
                    <table class="totals-table" style="border-bottom:1px solid #000;">
                        @if ($showDiscount)
                            <tr>
                                <td>Discount</td>
                                <td class="text-right">{{ $isLastInvoicePage ? '- ' . number_format($dis_val, 2) : '' }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td>{{ $taxLabel }}</td>
                            <td class="text-right">{{ $taxAmountLabel }}</td>
                        </tr>
                        <tr>
                            <td>Trans. Chg</td>
                            <td class="text-right">{{ $transportAmountLabel }}</td>
                        </tr>
                        <tr style="background:#f5f5f5; font-weight:700; border-top:0.8pt solid #000;">
                            <td class="bold">TOTAL</td>
                            <td class="text-right bold">{{ $isLastInvoicePage ? number_format($f_total, 2) : '' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="terms" style="margin-top:0; width:100%;">
            <tr>
                <td style="width:70%;">
                    <div>
                        <div class="bold">Terms &amp; Condition :</div>
                        @if (!empty($invoice_terms))
                            <ol>
                                @foreach ($invoice_terms as $term)
                                    <li>{{ $term }}</li>
                                @endforeach
                            </ol>
                        @endif

                        <div style="margin-top:6px;"><b>GSTIN No. :</b> {{ $company_gst ?? '' }}</div>
                    </div>
                </td>
                <td style="width:30%; text-align:right;">
                    <div style="margin-top:60px;">
                        <div>For, {{ \App\Models\Utility::getSetting('website_name') ?? '' }}</div>
                        <div>(Authorised Signatory)</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer">
            <div class="thank-you">Thank you for considering {{ \App\Models\Utility::getSetting('website_name') ?? '' }}!</div>
            <div>
                Phone: {{ \App\Models\Utility::getSetting('phone') ?? '' }} | Email:
                {{ \App\Models\Utility::getSetting('email') ?? '' }}
            </div>
        </div>
    </div>
@endforeach
