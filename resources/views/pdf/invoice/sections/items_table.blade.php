@php
    $showHsn = !empty($settings['show_hsn']);
    $showTax = !empty($settings['show_tax_columns']);
    $showDiscount = !empty($settings['show_discount_column']);
    $currentPageItems = isset($pageItems) ? collect($pageItems) : collect($invoice['items'] ?? []);
    $columnCount = 6 + ($showHsn ? 1 : 0) + ($showDiscount ? 1 : 0) + ($showTax ? 1 : 0);
    $blankRowCount = max(0, ((int) ($itemsPerPage ?? $currentPageItems->count())) - $currentPageItems->count());
@endphp
<tr>
    <td class="section-cell">
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">Sr</th>
                    <th>Item Description</th>
                    @if($showHsn)
                        <th style="width: 11%;">HSN/SAC</th>
                    @endif
                    <th style="width: 7%;">Qty</th>
                    <th style="width: 8%;">Unit</th>
                    <th style="width: 11%;">Rate</th>
                    @if($showDiscount)
                        <th style="width: 10%;">Discount</th>
                    @endif
                    @if($showTax)
                        <th style="width: 9%;">Tax</th>
                    @endif
                    <th style="width: 12%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($currentPageItems as $item)
                    <tr>
                        <td class="text-center">{{ $item['sr_no'] }}</td>
                        <td>
                            <strong>{{ $item['description'] }}</strong>
                            @if(!empty($item['notes']))
                                <div class="small-text text-muted">{{ $item['notes'] }}</div>
                            @endif
                        </td>
                        @if($showHsn)
                            <td>{{ $item['hsn'] }}</td>
                        @endif
                        <td class="text-center">{{ rtrim(rtrim(number_format($item['qty'], 2, '.', ''), '0'), '.') }}</td>
                        <td class="text-center">{{ $item['unit'] }}</td>
                        <td class="text-right">{{ number_format($item['rate'], 2) }}</td>
                        @if($showDiscount)
                            <td class="text-center">{{ number_format($item['discount_percent'], 2) }}%</td>
                        @endif
                        @if($showTax)
                            <td class="text-center">{{ number_format($item['tax_percent'], 2) }}%</td>
                        @endif
                        <td class="text-right">{{ number_format($item['amount'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $columnCount }}" class="text-center">
                            No invoice items available.
                        </td>
                    </tr>
                @endforelse

                @if($currentPageItems->count() > 0)
                    @for($index = 0; $index < $blankRowCount; $index++)
                        <tr class="blank-item-row">
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            @if($showHsn)
                                <td>&nbsp;</td>
                            @endif
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            @if($showDiscount)
                                <td>&nbsp;</td>
                            @endif
                            @if($showTax)
                                <td>&nbsp;</td>
                            @endif
                            <td>&nbsp;</td>
                        </tr>
                    @endfor
                @endif
            </tbody>
        </table>
    </td>
</tr>
