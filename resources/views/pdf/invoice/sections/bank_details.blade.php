<tr>
    <td class="section-cell">
        <table class="summary-table">
            <tr>
                <td colspan="2" class="section-heading">Bank Details</td>
            </tr>
            @if(!empty($invoice['bank']['account_name']))
                <tr>
                    <td style="width: 32%;">Account Name</td>
                    <td>{{ $invoice['bank']['account_name'] }}</td>
                </tr>
            @endif
            @if(!empty($invoice['bank']['account_number']))
                <tr>
                    <td>Account No.</td>
                    <td>{{ $invoice['bank']['account_number'] }}</td>
                </tr>
            @endif
            @if(!empty($settings['show_ifsc']) && !empty($invoice['bank']['ifsc']))
                <tr>
                    <td>IFSC</td>
                    <td>{{ $invoice['bank']['ifsc'] }}</td>
                </tr>
            @endif
            @if(!empty($invoice['bank']['bank_name']))
                <tr>
                    <td>Bank Name</td>
                    <td>{{ $invoice['bank']['bank_name'] }}</td>
                </tr>
            @endif
            @if(!empty($invoice['bank']['branch_name']))
                <tr>
                    <td>Branch</td>
                    <td>{{ $invoice['bank']['branch_name'] }}</td>
                </tr>
            @endif
        </table>
    </td>
</tr>
