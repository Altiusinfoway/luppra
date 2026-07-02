<tr>
    <td class="section-cell">
        <table class="party-table">
            <tr>
                @if(!empty($settings['show_billing']))
                    <td style="width: 50%; padding-right: 1px; vertical-align: top;">
                        <table class="section-table party-box">
                            <tr>
                                <td class="compact-party-cell">
                                    <div class="section-title compact-party-title">Billing To</div>
                                    <div><strong>{{ $invoice['customer']['billing_name'] ?? '' }}</strong></div>
                                    @if(!empty($invoice['customer']['billing_contact']))
                                        <div class="small-text compact-party-line">{{ $invoice['customer']['billing_contact'] }}</div>
                                    @endif
                                    @foreach(($invoice['customer']['billing_address_lines'] ?? []) as $line)
                                        <div class="small-text compact-party-line">{{ $line }}</div>
                                    @endforeach
                                    @if(!empty($invoice['customer']['billing_phone']))
                                        <div class="small-text compact-party-line">Phone: {{ $invoice['customer']['billing_phone'] }}</div>
                                    @endif
                                    @if(!empty($invoice['customer']['billing_gst_no']))
                                        <div class="small-text compact-party-line">GSTIN: {{ $invoice['customer']['billing_gst_no'] }}</div>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                @endif
                @if(!empty($settings['show_shipping']))
                    <td style="width: 50%; padding-left: 1px; vertical-align: top;">
                        <table class="section-table party-box">
                            <tr>
                                <td class="compact-party-cell">
                                    <div class="section-title compact-party-title">Shipping To</div>
                                    <div><strong>{{ $invoice['customer']['shipping_name'] ?? '' }}</strong></div>
                                    @if(!empty($invoice['customer']['shipping_contact']))
                                        <div class="small-text compact-party-line">{{ $invoice['customer']['shipping_contact'] }}</div>
                                    @endif
                                    @foreach(($invoice['customer']['shipping_address_lines'] ?? []) as $line)
                                        <div class="small-text compact-party-line">{{ $line }}</div>
                                    @endforeach
                                    @if(!empty($invoice['customer']['shipping_phone']))
                                        <div class="small-text compact-party-line">Phone: {{ $invoice['customer']['shipping_phone'] }}</div>
                                    @endif
                                    @if(!empty($invoice['customer']['shipping_gst_no']))
                                        <div class="small-text compact-party-line">GSTIN: {{ $invoice['customer']['shipping_gst_no'] }}</div>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                @endif
            </tr>
        </table>
    </td>
</tr>
