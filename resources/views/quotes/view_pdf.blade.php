<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quotation</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="UTF-8">
    <style type="text/css">
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: normal;
            src: url("{{ asset('fonts/THSarabunNew.ttf') }}") format('truetype');
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
        }


        * {
            box-sizing: border-box;

        }

        pre,
        p {
            padding: 0;
            margin: 0;

        }

        table {
            width: 100%;
            border-collapse: collapse;
            padding: 1px;

        }

        td,
        th {
            text-align: left;

        }

        .visibleMobile {
            display: none;

        }

        .hiddenMobile {
            display: none;

        }
    </style>

</head>

<body>

<table style="width: 100%; table-layout: fixed">
    @php
        $website_logo = \App\Models\Utility::websiteLogo(true);
        $website_name = \App\Models\Utility::getWebsiteName();
        $cust_primary_phone = \App\Models\CustomerPhone::where('is_primary',1)->first();
        $company_billing_adr = \App\Models\Utility::getCompanyBillingAddress();
        if($company_billing_adr)
        {
            $city_name = \App\Models\City::where('id',$company_billing_adr->city)->first();
        }
        $customer = \App\Models\Entity::where('id',$quote_id->customer_id)->first();
        if($customer && $customer->billing_address_id)
        {
            $cust_adr = \App\Models\Address::where('id',$customer->billing_address_id)->first();
            if($cust_adr)
            {
                $cust_city_name = \App\Models\City::where('id',$cust_adr->city)->first();
            }
        }
        $company_nm = \App\Models\Utility::WebsiteName();
        $company_email = \App\Models\Utility::getSetting('email');
        $company_url = \App\Models\Utility::getSetting('website_url');
    @endphp
        <tr>
            <td colspan="4" style="border-right: 1px solid #e4e4e4; width: 300px; color: #323232; line-height: 1.5; vertical-align: top;">
                <p style="font-size: 15px; color: #5b5b5b; font-weight: bold; line-height: 1; vertical-align: top; ">
                   Quotation Detail
                </p>
                <br>
                <p style="font-size: 12px; color: #5b5b5b; line-height: 24px; vertical-align: top;">
                    Quotation no : {{ $quote_id['code'] }} <br>
                    Date: {{ $quote_id['date'] }}
                </p>

               <p style="font-size: 12px; color: #050505; line-height: 24px; vertical-align: top;">
                Name : {{ $lead_id['name'] ?? ''}} <br>
                Phone : {{ $cust_primary_phone->phone ?? '' }}

            </p>

            </td>
            <td colspan="4" style="width: 300px; text-align: right; vertical-align: top;">
                <div style="text-align: right;">
                    <img src="{{ $website_logo ?? '' }}" alt="logo" height="30" style="margin-bottom: 5px;" />
                    <p style="font-size: 12px; font-weight: bold; color: #5b5b5b; margin: 0;">{{ $website_name ?? '' }}</p>
                    <p style="font-size: 12px; color: #5b5b5b; margin: 0;">
                        @if(!empty($company_billing_adr))
                            {{ $company_billing_adr->address_line_1 ?? '' }} <br>
                            {{ $company_billing_adr->address_line_2 ?? '' }} <br>
                            {{ $city_name->name ?? '' }}<br>
                             {{ $company_billing_adr->zipcode ?? '' }} <br>
                        @endif

                    </p>
                </div>
            </td>
        </tr>
        <tr class="visibleMobile">
            <td height="10"></td>
        </tr>
        <tr>
            <td colspan="10" style="border-bottom:1px solid #e4e4e4"></td>
        </tr>
    </table>
    {{-- header end --}}

    {{-- billing and shipping start --}}
    <table class="table" style="width: 100%;">
        <tbody style="display: table-header-group">
            <tr class="visibleMobile">
                <td height="20"></td>
            </tr>
            <tr style=" margin: 0;">
                <td colspan="4" style="width: 300px;">
                    <p
                        style="font-size: 12px; font-weight: bold; color: #5b5b5b; line-height: 1; vertical-align: top; ">
                        {{-- {{ localize('SHIPPING INFORMATION') }} --}}

                    </p>

                    @php
                        // $shippingAddress = $order->orderGroup->shippingAddress;
                    @endphp
                    <p style="font-size: 12px; color: #5b5b5b; line-height: 24px; vertical-align: top;">

                    <p class="mb-0">
                        {{-- {{ localize('Delivery Type') }}: --}}
                       <p style="font-size: 12px; color: #5b5b5b; line-height: 24px; vertical-align: top;">
                        Billing Information

                        </p>
                        <span style="font-size: 12px; color: black; line-height: 24px; vertical-align: top;">
                          @if($cust_adr)
                            {{ $cust_adr->address_line_1 ?? '' }}<br>
                            {{ $cust_adr->address_line_2 ?? '' }}<br>
                            {{ $cust_city_name->name ?? '' }} <br>
                            {{ $cust_adr->zipcode ?? '' }}<br>
                          @endif

                        </span>
                    </p>

                    <p class="mb-0" style="font-size: 12px; color: #5b5b5b; line-height: 24px; vertical-align: top;">
                    </p>



                </td>

                    <td colspan="4" style="width: 300px;">
                        <p
                            style="font-size: 11px; font-weight: bold; color: #5b5b5b; line-height: 1; vertical-align: top; ">
                        </p>
                        @php
                            // $billingAddress = $order->orderGroup->billingAddress;
                        @endphp
                        <p style="font-size: 12px; color: #5b5b5b; line-height: 24px; vertical-align: top;">

                        </p>
                    </td>


            </tr>

        </tbody>
    </table>
    {{-- billing and shipping end --}}

    {{-- item details start --}}
    <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable" bgcolor="#ffffff" style="margin-top: 20px;">
        <tbody>
            <tr>
                <td>
                    <table width="600" border="0" cellpadding="0" cellspacing="0" align="center"
                        class="fullTable" bgcolor="#ffffff">
                        <tbody>
                            <tr class="visibleMobile">
                                <td height="40"></td>
                            </tr>
                            <tr>
                                <td>
                                    <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center"
                                        class="fullPadding">
                                        <tbody>
                                            <tr>
                                                <th style="font-size: 12px; color: #000000; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 10px 7px 0;"  width="52%" align="left">
                                                    Product Name
                                                </th>
                                                <th style="font-size: 12px; color: #000000; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 0 7px; text-align: center; "align="center">
                                                    Qty
                                                </th>

                                                <th style="font-size: 12px; color: #000000; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 0 7px;" align="left">
                                                    MRP
                                                </th>
                                                <th  style="font-size: 12px; color: #000000; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 0 7px;" align="left">
                                                    Dealer Price
                                                </th>
                                                <th  style="font-size: 12px; color: #000000; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 0 7px;" align="left">
                                                    Discount
                                                </th>
                                                <th style="font-size: 12px; color: #000000; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 0 7px; text-align: right; " align="right">
                                                    Total
                                                </th>
                                            </tr>
                                            <tr>
                                                <td height="1" style="background: #e4e4e4;" colspan="6"></td>
                                            </tr>
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
                                                    <td style="font-size: 12px; color: #5b5b5b;  line-height: 18px;  vertical-align: top; padding:10px 0;"
                                                        class="article">
                                                        <div>{{ $product_detail['name'] }}</div>
                                                    </td>
                                                    <td style="font-size: 12px; color: #646a6e;  line-height:18px;  vertical-align: top; padding:10px 0; text-align: center;" align="center">
                                                        {{ $product['qty'] }}
                                                    </td>

                                                    <td style="font-size: 12px; color: #646a6e;  line-height: 18px;  vertical-align: top; padding:10px 0;">
                                                        {{ number_format($product['mrp'],2) }}
                                                    </td>
                                                    <td style="font-size: 12px; color: #646a6e;  line-height: 18px;  vertical-align: top; padding:10px 0;">
                                                        {{ number_format($product['price'],2) }}
                                                    </td>
                                                    <td style="font-size: 12px; color: #646a6e;  line-height: 18px;  vertical-align: top; padding:10px 0;">
                                                        {{ number_format($product['discount'],2) }}
                                                    </td>
                                                    <td style="font-size: 12px; color: #000000; font-weight: normal; line-height: 1; vertical-align: top; padding:10px 0; text-align: right; " align="right">
                                                        {{ number_format($product['total'],2) }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td height="1" style="background: #e4e4e4;" colspan="6"></td>
                                                </tr>
                                            @endforeach


                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td height="20"></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    {{-- item details end --}}

    {{-- item total start --}}
    <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable" bgcolor="#ffffff" >

        <tbody>
            <tr>
                <td>
                    <table width="600" border="0" cellpadding="0" cellspacing="0" align="center"
                        class="fullTable" bgcolor="#ffffff">
                        <tbody>
                            <tr>
                                <td>
                                    <!-- Table Total -->
                                    <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" class="fullPadding">
                                        <tbody>
                                            <tr>
                                                <td  style="font-size: 12px; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; ">
                                                    Sub Total :
                                                </td>
                                                <td style="font-size: 12px; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; white-space:nowrap;" width="80">

                                                 {{ number_format($sb_total,2) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <td style="font-size: 12px; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; ">
                                                    Discount :
                                                </td>
                                                <td style="font-size: 12px; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; white-space:nowrap;"
                                                    width="80">
                                                     {{ number_format($dis_val,2) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <td style="font-size: 12px; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; ">
                                                    Tax :
                                                </td>
                                                <td style="font-size: 12px; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; ">
                                                    {{ number_format($quote_id['gst'],2) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <td style="font-size: 12px; color: #000; line-height: 22px; vertical-align: top; text-align:right; ">
                                                    Total Amount :
                                                </td>
                                                <td style="font-size: 12px; color: #000; line-height: 22px; vertical-align: top; text-align:right; ">
                                                    {{ number_format($quote_id['grand_total'],2) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <!-- /Table Total -->
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    {{-- item total end --}}

    {{-- footer start --}}
    <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable" bgcolor="#ffffff">

        <tr>
            <td>
                <table width="600" border="0" cellpadding="0" cellspacing="0" align="center"
                    class="fullTable" bgcolor="#ffffff" style="border-radius: 0 0 10px 10px;">

                    <tr class="hiddenMobile">
                        <td height="30"></td>
                    </tr>
                    <tr class="visibleMobile">
                        <td height="20"></td>
                    </tr>

                    <tr>
                        <td>
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center"
                                class="fullPadding">
                                <tbody>
                                    <tr>
                                        <td style="font-size: 12px; color: #5b5b5b; line-height: 18px; vertical-align: top; text-align: left;">
                                            <p
                                                style="font-size: 12px; color: #5b5b5b; line-height: 18px; vertical-align: top; text-align: left;">
                                                {{-- {{ localize('Hello') }} --}}
                                                {{-- <strong>{{ optional($order->user)->name }},</strong> --}}
                                                <br>
                                                {{-- {{ getSetting('invoice_thanksgiving') }} --}}

                                                {{ $lead_id['name'] }} <br>
                                                Thank You
                                            </p>
                                            <br><br>
                                            <p
                                                style="font-size: 12px; color: #5b5b5b; line-height: 18px; vertical-align: top; text-align: left;">
                                                {{-- {{ localize('Best Regards') }},
                                                <br>{{ getSetting('system_title') }} <br>
                                                {{ localize('Email') }}: {{ getSetting('topbar_email') }}<br>
                                                {{ localize('Website') }}: {{ env('APP_URL') }} --}}

                                                Best Regards<br>
                                                {{ $company_nm ?? '' }}<br>
                                                email : {{ $company_email ?? ''}}<br>
                                                website: {{ $company_url ?? '' }}<br>
                                            </p>

                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    {{-- footer end --}}

</body>

</html>

