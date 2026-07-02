<tr>
    <td class="section-cell">
        <table class="section-table party-box">
            <tr>
                @if(!empty($settings['show_logo']) && !empty($invoice['company']['logo_path']) && file_exists($invoice['company']['logo_path']))
                    <td class="logo-cell">
                        <img src="{{ $invoice['company']['logo_path'] }}" class="logo-image">
                    </td>
                @endif
                <td class="company-detail-cell">
                    <div class="company-name">{{ $invoice['company']['name'] ?? '' }}</div>
                    @foreach(($invoice['company']['address_lines'] ?? []) as $line)
                        <div class="small-text">{{ $line }}</div>
                    @endforeach
                    @if(!empty($invoice['company']['phone']))
                        <div class="small-text">Phone: {{ $invoice['company']['phone'] }}</div>
                    @endif
                    @if(!empty($invoice['company']['email']))
                        <div class="small-text">Email: {{ $invoice['company']['email'] }}</div>
                    @endif
                    @if(!empty($settings['show_gst']) && !empty($invoice['company']['gst_no']))
                        <div class="small-text">GSTIN: {{ $invoice['company']['gst_no'] }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </td>
</tr>
