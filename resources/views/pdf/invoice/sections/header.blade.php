@php
    $title = $settings['title'] ?? ($invoice['meta']['title'] ?? 'TAX INVOICE');
@endphp
<tr>
    <td class="section-cell">
        <table class="section-table header-box">
            <tr>
                <td>
                    <div class="invoice-title">{{ $title }}</div>
                </td>
            </tr>
        </table>
    </td>
</tr>
