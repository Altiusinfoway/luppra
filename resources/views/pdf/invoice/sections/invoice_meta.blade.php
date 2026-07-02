@php
    $fields = $settings['fields'] ?? ['invoice_number', 'invoice_date'];
@endphp
<tr>
    <td class="section-cell">
        <table class="summary-table">
            <tr>
                <td colspan="2" class="section-heading">Invoice Details</td>
            </tr>
            @foreach($fields as $field)
                <tr>
                    <td style="width: 32%;">
                        @if($field === 'invoice_number') Invoice No. @endif
                        @if($field === 'invoice_date') Invoice Date @endif
                        @if($field === 'transport') Transport @endif
                    </td>
                    <td>
                        @if($field === 'invoice_number') {{ $invoice['meta']['invoice_number'] ?? '' }} @endif
                        @if($field === 'invoice_date') {{ $invoice['meta']['invoice_date'] ?? '' }} @endif
                        @if($field === 'transport') {{ $invoice['meta']['transport_name'] ?? '' }} @endif
                    </td>
                </tr>
            @endforeach
            <tr>
                <td>L.R. No.</td>
                <td>{{ $invoice['meta']['lr_number'] ?? '' }}</td>
            </tr>
            <tr>
                <td>No. Of Article</td>
                <td>{{ $invoice['meta']['article_count'] ?? '' }}</td>
            </tr>
            <tr>
                <td>Payment Terms</td>
                <td>{{ $invoice['meta']['payment_terms'] ?? '' }}</td>
            </tr>
        </table>
    </td>
</tr>
