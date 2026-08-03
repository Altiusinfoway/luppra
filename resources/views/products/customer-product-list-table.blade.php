
@php
   $default_img =  \App\Models\Utility::defaultImage();
@endphp
<div class="col-md-12">
    <div class="card" style="border:1px solid #e2e8f0;border-radius:22px;box-shadow:0 14px 28px rgba(15,23,42,.05);">
        <div class="card-body" id="product-rows">
            <div class="table-responsive" style="border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;background:#fff;">
            <table class="table table-bordered table-striped align-middle" style="width:100%;">
                <thead>
                    <tr>
                        <th data-ordering="false" style="width:300px;">Name </th>
                        <th>Image</th>
                        <th>Short Note</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Dealer Price</th>
                        <th class="dynamic_gst_name">GST</th>
                        <th class="hide_discount">Discount</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="product-list">
                     @if(!is_null($lead) && $lead->product && $lead->product->count() > 0)
                        @foreach($lead->product as $product)
                        @php
                            $cust_price_hist =  \App\Models\CustomerPriceHistory::where('customer_id',$lead->customer_id)->
                                                        where('product_id',$product->id)->first();
                            $dealerPrice = $cust_price_hist ? $cust_price_hist->price : $product->pivot->price;
                            $disVal = $cust_price_hist ? $cust_price_hist->discount : null;
                            $get_lead_qty = (!empty($product->pivot->qty) && $product->pivot->qty > 0) ? $product->pivot->qty :1;
                        @endphp

                        <tr class="product-row" data-default-gst="{{ $product?->getGstSlabMaster?->rate ?? 0 }}">
                        <td>
                            {{ $product->name }}
                            <input type="hidden" name="products[id][]" value="{{ $product->id }}">
                            <input type="hidden" name="products[listing_id][]" value="{{ $product->pivot->marketplace_listing_id ?? '' }}">
                            <input type="hidden" name="products[row_key][]" value="{{ $product->id }}:{{ $product->pivot->marketplace_listing_id ?? 'master' }}">
                            @if(!empty($product->pivot->marketplace_listing_id) && isset($product->pivot->marketplaceListing))
                                <div class="small text-muted">
                                    {{ ucfirst($product->pivot->marketplaceListing->platform ?? '') }}
                                    / {{ $product->pivot->marketplaceListing->account_name ?? 'Primary Account' }}
                                    SKU: {{ $product->pivot->marketplaceListing->platform_sku ?? '' }}
                                </div>
                            @endif
                        </td>
                        <td> <img src="@if(!empty($product->image)) {{  $product->image}} @else {{ $default_img }} @endif" height="70px" width="70px"> </td>
                         <td><textarea name="products[short_notes][]" class="form-control" cols="8" rows="3"></textarea></td>
                        <td>
                            <div class="input-step">
                                <button type="button" class="minus btn btn-danger">–</button>
                                <input type="number" class="product-quantity" name="products[qty][]" id="product-qty-1" value="{{ $get_lead_qty }}" >
                                <button type="button" class="plus btn btn-success">+</button>
                            </div>
                        </td>
                        <td>
                              {{ Form::select('products[units][]', \App\Models\Utility::getUnits($product->unit_type), $product->pivot->unit_id, [
                                'class' => 'form-select item-unit',
                                'required',
                            ]) }}
                            <input type="hidden"  name="products[mrp][]" class="form-control product-price bg-light border-0" id="productRate-1" value="{{ $product->pivot->price }}" step="0.01" placeholder="0.00"  readonly />
                        </td>
                        <td> <input type="number" name="products[price][]" class="form-control dealer-price " id="dealer-price-id" value="{{ $dealerPrice }}" step="0.01" placeholder="0.00" /></td>
                        <td>
                            <select name="products[gst][]" class="form-select gst-record">
                                <option value="">Select GST</option>
                            </select>
                            <input type="hidden" name="products[gst_value][]" class="gst-value gst_default_selected" value="{{ $product?->getGstSlabMaster?->rate ?? 0 }}">
                        </td>
                        <td class="hide_discount">

                            <input type="number" name="products[discount][]"  class="form-control discount " id="discount-id"  step="0.01" placeholder="0.00" value="{{ $disVal }}" />
                        </td>
                        <td class="text-end">
                           <span class="product_tol">  {{ $product->pivot->price * $product->pivot->qty  }}</span>
                             <input type="hidden" name="products[product_total][]" class="product-total" value="{{ $product->pivot->price * $product->pivot->qty }}">
                        </td>

                         <td class="text-center">
                            <button type="button" class="btn btn-sm btn-danger remove-product-row remove-row">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>

                <tfoot>
                    <tr><td colspan="7"></td></tr>
                    <tr class="subtotal-row hidden-row">
                        <td colspan="7"></td>
                        <td><strong>Sub Total </strong></td>
                        <td class="text-end subTotal">0.00</td>
                        <input type="hidden" name="sub_total" value="0.00">
                    </tr>
                    <tr id="tax-type-row" class="hide_discount">
                        <td colspan="7"></td>
                        <td><strong>Discount </strong></td>
                        <td class="text-end totalDiscount">0.00</td>
                    </tr>

                      <input type="hidden" name="tax_json_data" value="{}" class="tax_jsn">


                    <tr class="tax-rate-sum-row" style="display:none;">
                        <td colspan="7"></td>
                        <td><strong>Total Tax </strong></td>
                        <td class="text-end tax_rate_sum">0.00</td>
                       <input type="hidden" name="tax_rate_sum" value="0.00">

                    </tr>

                    <tr>
                        <td colspan="7"></td>
                        <td><strong>Tax Value</strong></td>
                        <td class="text-end totalTax">0.00</td>
                        <input type="hidden" name="tax" value="0.00">
                    </tr>
                    <tr>
                        <td colspan="7"></td>
                        <td class="blue-text"><strong>Total Amount </strong></td>
                        <td class="blue-text text-end totalAmount">0.00</td>
                         <input type="hidden" name="total_amt" value="0.00">
                    </tr>

                    {{-- <tr>
                        <td colspan="6"></td>
                        <td><strong>Tax </strong></td>
                        <td class="text-end totalTax">0.00</td>
                        <input type="hidden" name="tax" value="0">
                    </tr>
                    <tr>
                        <td colspan="6"></td>
                        <td class="blue-text"><strong>Total Amount </strong></td>
                        <td class="blue-text text-end totalAmount">0.00</td>
                        <input type="hidden" name="total_amt" value="0">
                    </tr> --}}
                </tfoot>

            </table>
            </div>
        </div>
    </div>
</div>
