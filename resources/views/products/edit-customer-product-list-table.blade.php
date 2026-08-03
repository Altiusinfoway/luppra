@php
    $default_img = \App\Models\Utility::defaultImage();
@endphp
<div class="col-md-12">
    <div class="card">
        <div class="card-body" id="product-rows">
            <table class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th data-ordering="false" style="width:300px;">Name </th>
                        <th>Image </th>
                        <th>Short Name</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        {{-- <th>MRP</th> --}}
                        <th>Dealer Price</th>
                         <th class="dynamic_gst_name">GST</th>
                        <th class="hide_discount">Discount (%)</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="product-list">
                    @php
                        $sb_total = 0;
                        $dis_val = 0;
                    @endphp

                    @if ($qt_id && $qt_id->quoteProducts && $qt_id->quoteProducts->count())
                        @foreach ($qt_id->quoteProducts as $qt_product)
                            @php
                                // $quote_product = \App\Models\QuoteProducts::where('quote_id', $qt_id['id'])
                                //     ->where('product_id', $product['id'])
                                //     ->first();
                                // if ($quote_product) {
                                //     $sb_total += $quote_product->total;
                                //     $product_total = $quote_product->qty * $quote_product->price;
                                //     $dis_val += ($product_total * $quote_product->discount) / 100;
                                // }

                                $sb_total += $qt_product->total;
                                $product_total = $qt_product->qty * $qt_product->price;
                                if ($qt_product->discount != 0) {
                                    $dis_val += ($product_total * $qt_product->discount) / 100;
                                }

                            @endphp

                            <tr class="product-row" data-default-gst="{{ $qt_product->tax ?? 0 }}">
                                <input type="hidden" name="products[id][]" value="{{ $qt_product->product_id }}">
                                <input type="hidden" name="products[listing_id][]" value="{{ $qt_product->marketplace_listing_id ?? '' }}">
                                <input type="hidden" name="products[row_key][]" value="{{ $qt_product->product_id }}:{{ $qt_product->marketplace_listing_id ?? 'master' }}">
                                <input type="hidden" name="products[quote_ids][]" value="{{ $qt_product->id }}">

                                <td>
                                    {{ $qt_product->getProduct->name ?? '' }}
                                    @if(!empty($qt_product->marketplace_listing_id) && $qt_product->marketplaceListing)
                                        <div class="small text-muted">
                                            {{ ucfirst($qt_product->marketplaceListing->platform ?? '') }}
                                            / {{ $qt_product->marketplaceListing->account_name ?? 'Primary Account' }}
                                            SKU: {{ $qt_product->marketplaceListing->platform_sku ?? '' }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <img src="@if (!empty($qt_product->getProduct->image)) {{ $qt_product->getProduct->image }} @else {{ $default_img }} @endif"
                                        height="70px" width="70px">
                                </td>
                                <td>
                                    <textarea name="products[short_notes][]" class="form-control" cols="8" rows="3">{{ $qt_product->short_notes ?? '' }}</textarea>
                                </td>
                                <td>
                                    <div class="input-step">
                                        <button type="button" class="minus btn btn-danger">–</button>
                                        <input type="number" class="product-quantity" name="products[qty][]"
                                            id="product-qty-1" value="{{ $qt_product['qty'] }}" >
                                        <button type="button" class="plus btn btn-success">+</button>
                                    </div>
                                </td>
                                <td>
                                    {{ Form::select(
                                        'products[units][]',
                                        \App\Models\Utility::getUnits($qt_product->getProduct->unit_type),
                                        $qt_product->unit_id,
                                        [
                                            'class' => 'form-select item-unit',
                                            'required',
                                        ],
                                    ) }}
                                </td>

                                <input type="hidden" name="products[mrp][]"
                                    class="form-control product-price bg-light border-0" id="productRate-1"
                                    value="{{ $qt_product->mrp }}" step="0.01" placeholder="0.00" readonly />

                                <td>
                                    <input type="number" name="products[price][]"
                                        class="form-control dealer-price " id="dealer-price-id"
                                        value="{{ $qt_product->price }}" step="0.01" placeholder="0.00" />
                                </td>
                                <td>
                                    <select name="products[gst][]" class="form-select gst-record">
                                        <option value="">Select GST</option>
                                    </select>
                                    <input type="hidden" name="products[gst_value][]" class="gst-value gst_default_selected" value="{{ $qt_product->tax ?? 0 }}">
                                </td>

                                <td class="hide_discount">
                                    <input type="number" name="products[discount][]"
                                        class="form-control discount " id="discount-id" step="0.01"
                                        placeholder="0.00" value="{{ $qt_product->discount }}" />
                                </td>
                                {{-- <td class="text-end">
                                    {{ number_format($qt_product->total, 2) }}
                                </td>
                                 <input type="hidden" name="products[product_total][]" class="product-total"
                                    value=" {{ number_format($qt_product->total, 2) }}">
                                --}}
                                 <td>
                                    <span class="product_tol">  {{ number_format($qt_product->total, 2) }}</span>
                                        <input type="hidden" name="products[product_total][]" class="product-total" value="{{ $qt_product->total }}">
                                </td>

                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger remove-product-row remove-row">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </td>

                        @endforeach
                    @endif
                      </tr>

                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7"></td>
                    </tr>

                    <tr class="subtotal-row hidden-row" >
                        @php
                            //   $sb_total=0;
                            //   $sb_total +=$quote_product->total;

                            //   $dis_val=0;
                            //   $f_total=0;
                            //   $f_total = $quote_product['qty'] *  $quote_product['price'];
                            //   $dis_val += $f_total * $quote_product['discount']/100;
                        @endphp

                        <td colspan="7"></td>
                        <td><strong>Sub Total </strong></td>
                        <td class="text-end subTotal">
                            {{ number_format($sb_total, 2) }}
                             <input type="hidden" name="sub_total" value="{{ number_format($sb_total, 2) }}">
                        </td>


                    </tr>
                    <tr id="tax-type-row" class="hide_discount">
                        <td colspan="7"></td>
                        <td><strong>Discount </strong></td>
                        <td class="text-end totalDiscount">{{ $dis_val }}</td>
                    </tr>

                    {{-- @if (!empty($qt_id?->tax_detail_json))
                        @php
                            $taxDetails = json_decode($qt_id->tax_detail_json, true);
                        @endphp

                        @if (json_last_error() === JSON_ERROR_NONE && is_array($taxDetails))
                            @foreach ($taxDetails as $key => $value)
                                <tr>
                                    <td colspan="6"></td>
                                    <td><strong>{{ $key }}</strong></td>
                                    <td class="text-end">{{ $value }}%</td>
                                </tr>
                            @endforeach

                        @endif
                    @endif--}}

                    <input type="hidden" name="tax_json_data" value="{{ $qt_id->tax_detail_json }}" class="tax_jsn">

                    <tr class="tax-rate-sum-row" style="display:none;">
                        <td colspan="7"></td>
                        <td><strong>Total Tax </strong></td>
                        <td class="text-end tax_rate_sum">{{ $qt_id->total_tax_sum ?? 0 }}</td>
                        <input type="hidden" name="tax_rate_sum" value="{{ $qt_id->total_tax_sum ?? 0 }}">

                    </tr>
                    <tr>
                        <td colspan="7"></td>
                        <td><strong>Tax Value </strong></td>

                        <td class="text-end totalTax">{{ $qt_id->gst }}</td>
                        <input type="hidden" name="tax" value="{{ $qt_id->gst }}">
                    </tr>
                    <tr>
                        <td colspan="7"></td>
                        <td class="blue-text"><strong>Total Amount </strong></td>
                        <td class="blue-text text-end totalAmount">{{ number_format($qt_id['grand_total'], 2) }}</td>
                        <input type="hidden" name="total_amt" value="{{ $qt_id['grand_total'] }}">
                    </tr>

                </tfoot>
            </table>
        </div>
    </div>
</div>


<script>
    // applyDiscountVisibility();
</script>
