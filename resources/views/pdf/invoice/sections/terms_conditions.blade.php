<tr>
    <td class="section-cell">
        <table class="section-table">
            <tr>
                <td class="section-heading">{{ $settings['label'] ?? 'Terms & Conditions' }}</td>
            </tr>
            <tr>
                <td>
                    <ol class="terms-list">
                        @foreach(($invoice['terms'] ?? []) as $term)
                            <li>{{ $term }}</li>
                        @endforeach
                    </ol>
                </td>
            </tr>
            @if(!empty($invoice['company']['gst_no']))
                <tr>
                    <td class="small-text">Company GSTIN: {{ $invoice['company']['gst_no'] }}</td>
                </tr>
            @endif
        </table>
    </td>
</tr>
