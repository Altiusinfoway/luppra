<tr>
    <td class="section-cell">
        <table class="summary-table">
            <tr>
                <td colspan="2" class="section-heading">Tax Summary</td>
            </tr>
            @foreach(($invoice['taxes'] ?? []) as $tax)
                <tr>
                    <td>{{ $tax['label'] }}</td>
                    <td class="text-right">{{ number_format($tax['amount'], 2) }}</td>
                </tr>
            @endforeach
        </table>
    </td>
</tr>
