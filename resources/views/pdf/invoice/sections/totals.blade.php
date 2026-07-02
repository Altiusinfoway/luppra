<tr>
    <td class="section-cell">
        <table class="totals-table">
            <tr>
                <td colspan="2" class="section-heading">Totals</td>
            </tr>
            <tr>
                <td>Sub Total</td>
                <td class="text-right">{{ number_format($invoice['summary']['sub_total'] ?? 0, 2) }}</td>
            </tr>
            @if(($invoice['summary']['discount_total'] ?? 0) > 0)
                <tr>
                    <td>Discount</td>
                    <td class="text-right">- {{ number_format($invoice['summary']['discount_total'], 2) }}</td>
                </tr>
            @endif
            <tr>
                <td>Taxable Amount</td>
                <td class="text-right">{{ number_format($invoice['summary']['taxable_total'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Tax Total</td>
                <td class="text-right">{{ number_format($invoice['summary']['tax_total'] ?? 0, 2) }}</td>
            </tr>
            @if(!empty($settings['show_transport_charge']))
                <tr>
                    <td>Transport Charge</td>
                    <td class="text-right">{{ number_format($invoice['summary']['transport_charge'] ?? 0, 2) }}</td>
                </tr>
            @endif
            <tr class="{{ !empty($settings['highlight_grand_total']) ? 'totals-highlight' : '' }}">
                <td><strong>Grand Total</strong></td>
                <td class="text-right"><strong>{{ number_format($invoice['summary']['grand_total'] ?? 0, 2) }}</strong></td>
            </tr>
        </table>
    </td>
</tr>
