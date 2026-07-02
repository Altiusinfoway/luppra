@extends('layouts.app')

@section('content')
<style>
    .flatpickr-months .flatpickr-month
    {
        background: white;
    }
    .hidden-row {
    display: none !important;
}
</style>
@php
    $default_img = \App\Models\Utility::defaultImage();
    $check_discount_allow = \App\Models\Utility::isDiscountAllowed();
@endphp

<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Quotes  </h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('quotes.index') }}">Quotes</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5 class="card-title mb-0">Quotes Edit</h5>
                    </div>
                    <div class="card-body">
                        {{ Form::open(['route' => ['quotes.update', $quote_id], 'method' => 'post', 'enctype' => 'multipart/form-data', 'id' => 'quotesEditForm', 'autocomplete' => 'off']) }}

                        {{-- lead-id store customer-id original --}}
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Customer <x-required/></label>{{-- $quote_id->lead_id --}}
                                {{ Form::select('lead_id', $leads,$quote_id->customer_id , [
                                    'class' => 'form-select mb-3 choices-select form-control',
                                    'id' => 'lead_id',
                                    'required',
                                    'data-choices',
                                    'data-choices-removeItem',
                                    'disabled'
                                ]) }}
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Type <x-required/></label>
                                {{ Form::select('customer_type', $client_type, old('customer_type', $quote_id->customer_type ?? null), [
                                    'class' => 'form-select mb-3 choices-select form-control',
                                    'id' => 'customer_type',
                                    'required',
                                    'data-choices',
                                    'data-choices-removeItem',
                                    'disabled'
                                ]) }}
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-6">
                                {{ Form::label('date', __('Quote Date'), ['class' => 'form-label']) }} <x-required/>
                                {{ Form::date('date', $quote_id->date, [
                                    'class' => 'form-control datepicker-range',
                                    'id' => 'datepicker-range',
                                    'placeholder' => __('Enter date.'),
                                    'data-provider' => 'flatpickr'
                                ]) }}
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Transport </label>
                                {{ Form::select('transport_id', ['' => 'Select Transport'] + $transport_list, $quote_id->transport_id, [
                                    'class' => 'form-select mb-3 choices-select form-control',
                                    'id' => 'transport_id',
                                    'data-choices',
                                    'data-choices-removeItem'
                                ]) }}
                            </div>
                        </div>

                        <div class="row mt-2">

                             <div class="col-md-3">
                                        <label for="company_name" class="form-label">Company Name</label>
                                        {{ Form::text('company_name', null, [
                                            'class' => 'form-control',
                                            'id' => 'company_name_id',
                                        ]) }}
                                        <span class="text-danger error-msg" id="error-company_name_id"></span>
                                    </div>

                            <div class="col-md-3">
                                <label class="form-label">GST No</label>
                                {{ Form::text('gst_no', old('gst_no'), ['class' => 'form-control', 'id' => 'gst_no_id']) }}
                                  @error('gst_no')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                <span class="text-danger error-msg" id="error-gst_no_id"></span>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Aadhar Number</label>
                                {{ Form::text('adhar_no', old('adhar_no'), ['class' => 'form-control', 'id' => 'adhar_no_id']) }}
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Udhyam Number</label>
                                {{ Form::text('udhaym_no', old('udhaym_no'), ['class' => 'form-control', 'id' => 'udhaym_no_id']) }}
                            </div>
                        </div>

                          <div class="address-section">
                            @php
                                $company = \App\Models\Entity::find($quote_id->customer_id);
                            @endphp

                            @include('address.address_selection',['company' => $company,'billing_address_id'=>$company->billing_address_id ?? null,'shipping_address_id'=>$company->billing_address_id ?? null])
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-6">
                                <label class="form-label">Products</label>
                                  <a href="javascript:void(0);" class="float-end" data-size="md" data-url="{{ route('products.quick_create') }}" data-ajax-popup="true" data-bs-original-title="{{__('Add Product')}}"><i class="ri-add-line align-bottom me-1"></i> Add New Product</a>

                                <select class="form-select" id="raw_id" data-choices data-choices-removeItem>
                                    <option value="">Select Product</option>
                                    @foreach ($product_list as $product)
                                        <option value="{{ $product->id }}"
                                            data-name="{{ $product->name }}"
                                            data-image="{{ $product->image ?? $default_img }}"
                                            data-price="{{ $product->price }}"
                                            data-units='@json(\App\Models\Utility::getUnits($product->unit_type))'
                                            data-default-unit="{{ $product->unit_type }}"
                                            data-default-gst="{{ $product?->getGstSlabMaster?->rate ?? 0 }}"
                                            >
                                            {{ $product->sku_code }} - {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <span class="text-danger error-msg d-block mt-2" id="error-products"></span>
                            <div class="main-product-list">
                                @include('products.edit-customer-product-list-table', ['qt_id' => $quote_id])
                            </div>
                        </div>

                        <div class="row">
                            @if ($quote_id['is_advance_payment'] == 1)
                                <div class="col-md-6 offset-md-6" id="advance_payment">
                                    <label>Advance Payment <x-required/></label>
                                    <input type="number" name="advance_payment" class="form-control" value="{{ $quote_id->advance_payment }}">
                                </div>
                            @else
                                <div class="col-md-6 offset-md-6" id="payment_after_days">
                                    <label>Payment After Days <x-required/></label>
                                    <input type="number" name="payment_after_days" class="form-control" value="{{ $quote_id->payment_after_days }}">
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 hstack gap-2 justify-content-end">
                            <a href="{{ route('quotes.index') }}"  class="btn btn-light" >Close</a>
                            <button type="submit" class="btn btn-success" name="save" value="only_save" id="saveBtn">Save</button>
                            {{-- <button type="submit" class="btn btn-danger" name="save" value="save_send"><i class="ri-send-plane-fill align-bottom me-1"></i>Save & Send</button> --}}
                        </div>

                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Activity History</h5>
                    </div>
                    <div class="card-body">
                        @include('activity._timeline', [
                            'activities' => $activityTimeline,
                            'emptyMessage' => 'No activity found for this quotation.',
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('page-script')
<script>
let GLOBAL_GST_LIST = {};
</script>

<script>
/* ================================================================
   PRINCE CRM - EDIT QUOTE SCRIPT
   ---------------------------------------------------------------
   Handles:
   → Product load & dynamic tax calculation
   → Row total calculation
   → Add/remove product
   → Validation
================================================================== */

// ========== UTILITIES ==========
let btn = document.getElementById('saveBtn');
async function isCustomerAddressAvailable(leadId) {
     //lead-id store cust-id original
    const url = "{{ route('quotes.check_cust_adr', ':lead_id') }}".replace(':lead_id', leadId);
    try {
        const response = await fetch(url);
        return await response.json();
    } catch (error) {
        console.error("Error checking customer address:", error);
        return false;
    }
}

function initExistingProductRows() {
    document.querySelectorAll('.product-row').forEach(row => {
        const gstSelect = row.querySelector('.gst-record');
        if (!gstSelect) return;

        fillGstDropdown(gstSelect);
        applyDefaultGstForRow(row);
    });

    updateTotals();
}

// ========== PRODUCT DISPLAY ==========
async function display_product(e)
{
    const leadId = e.value;
     //lead-id store cust-id original
    const raw_url = "{{ route('leads.edit-customer-get-products', ['#', $quote_id->id]) }}"; //edit-get-products
    const finalUrl = raw_url.replace('#', leadId);

    document.querySelector('.main-product-list').innerHTML = '';

    getAjax(finalUrl, async function(response)
    {
        document.querySelector('.main-product-list').innerHTML = response;

        //----------------- default gst selected -----------------
        // document.querySelectorAll('.product-row').forEach(row => {
        //     const gstSelect = row.querySelector('.gst-record');
        //     if (!gstSelect) return;

        //     // Fill dropdown AFTER GST list is ready
        //     fillGstDropdown(gstSelect);

        //     const defaultGst = parseFloat(row.dataset.defaultGst) || 0;

        //     if (defaultGst) {
        //         gstSelect.value = defaultGst;

        //         const gstValInput = row.querySelector('.gst-value');
        //         if (gstValInput) gstValInput.value = defaultGst;

        //         gstSelect.dispatchEvent(new Event('change', { bubbles: true }));
        //     }
        // });

        document.querySelectorAll('.product-row').forEach(row => {
            const gstSelect = row.querySelector('.gst-record');
            fillGstDropdown(gstSelect);

            const defaultGst = parseFloat(row.dataset.defaultGst) || 0;
            if (defaultGst) {
                gstSelect.value = defaultGst;
                gstSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        //-------------------------------

        updateTotals();

        const addressCheck = await isCustomerAddressAvailable(leadId);
        const taxData = addressCheck.tax_data || {};

        const taxTypeRowEl = document.getElementById("tax-type-row");
        const taxJsonInputEl = document.querySelector('input[name="tax_json_data"]');
        const taxRateSumDisplayEl = document.querySelector('.tax_rate_sum');
        const taxRateSumInputEl = document.querySelector('input[name="tax_rate_sum"]');

        document.querySelectorAll('[data-tax-row="1"]').forEach(el => el.remove());

        //gst json assign
        const taxJsonInput = document.querySelector('input[name="tax_json_data"]');

        if (taxJsonInput && taxData && typeof taxData === 'object') {
            taxJsonInput.value = JSON.stringify(taxData);
        } else if (taxJsonInput) {
            taxJsonInput.value = '{}';
        }
         if (taxTypeRowEl && taxData) {

            // Remove previously added tax rows
            while (
                taxTypeRowEl.nextElementSibling &&
                taxTypeRowEl.nextElementSibling.dataset.taxRow === "1"
            ) {
                taxTypeRowEl.nextElementSibling.remove();
            }

            // Get only keys where value > 0
            Object.entries(taxData).forEach(([key, value]) => {
                const taxValue = parseFloat(value) || 0;

                if (taxValue) {
                    const tr = document.createElement("tr");
                    tr.dataset.taxRow = "1";

                    tr.innerHTML = `
                        <td colspan="6"></td>
                        <td><strong>${key}</strong></td>
                        <td class="text-end"></td>
                    `;

                    taxTypeRowEl.parentNode.insertBefore(
                        tr,
                        taxTypeRowEl.nextSibling
                    );
                }
            });
        }

        // ================= GST Name dynamic set =================
        GLOBAL_GST_LIST = addressCheck.gst_list || {};

       document.querySelectorAll('.product-row').forEach(row => {
            const gstSelect = row.querySelector('.gst-record');
            if (!gstSelect) return;

            // build dropdown now (GST list is ready)
            fillGstDropdown(gstSelect);

            // apply default GST from row (edit data)
            const defaultGst = parseFloat(row.dataset.defaultGst) || 0;
            if (defaultGst) {
                gstSelect.value = defaultGst;

                const gstValInput = row.querySelector('.gst-value');
                if (gstValInput) gstValInput.value = defaultGst;

                gstSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });



        // temp-cmt
        // let taxJsonResult = {}, taxRateTotal = 0;
        // for (const [key, value] of Object.entries(taxData))
        // {
        //     if (key !== 'tax_type' && value) {
        //         const tr = document.createElement('tr');
        //         tr.dataset.taxRow = "1";
        //         tr.innerHTML = `<td colspan="6"></td><td><strong>${key}</strong></td><td class="text-end">${value}%</td>`;
        //         taxTypeRowEl.parentNode.insertBefore(tr, taxTypeRowEl.nextSibling);
        //         taxJsonResult[key] = value;
        //         taxRateTotal += parseFloat(value) || 0;
        //     }
        // }

        // taxJsonInputEl.value = JSON.stringify(taxJsonResult);
        // taxRateSumDisplayEl.textContent = taxRateTotal + '%';
        // taxRateSumInputEl.value = taxRateTotal.toFixed(2);




        updateTotals();

        applyDiscountVisibility();

    });


}

 // ===== GST SECTION =====
    // ===== FETCH GST / ADHAR / UDHYAM =====
async function fetchCustomerDetails(leadId) {
    if (!leadId) return;

     //lead-id store cust-id original
    const gstUrl = "{{ route('leads.get-gst', ['#']) }}".replace('#', leadId);
    try {
        const response = await fetch(gstUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const contentType = response.headers.get('content-type') || '';

        if (!response.ok) {
            throw new Error(`GST request failed with status ${response.status}`);
        }

        if (!contentType.includes('application/json')) {
            throw new Error(`GST request returned ${contentType || 'non-JSON'} instead of JSON`);
        }

        const data = await response.json();

          //customer-comp
            const customerInput = document.getElementById('company_name_id');
            const comp_nm = data.company_name || '';
            customerInput.value = comp_nm;
            comp_nm !== '' ? customerInput.setAttribute('disabled', true) : customerInput.removeAttribute('disabled');

        // GST
        const gstInput = document.getElementById('gst_no_id');
        const gst = data.gst_no || '';
        gstInput.value = gst;
        gst !== '' ? gstInput.setAttribute('disabled', true) : gstInput.removeAttribute('disabled');

        // Aadhaar
        const adharInput = document.getElementById('adhar_no_id');
        const adhar = data.adhar_nub || '';
        adharInput.value = adhar;
        adhar !== '' ? adharInput.setAttribute('disabled', true) : adharInput.removeAttribute('disabled');

        // Udhyam
        const udhaymInput = document.getElementById('udhaym_no_id');
        const udhyam = data.udhyam_nub || '';
        udhaymInput.value = udhyam;
        udhyam !== '' ? udhaymInput.setAttribute('disabled', true) : udhaymInput.removeAttribute('disabled');
    } catch (err) {
        console.error("Failed to fetch customer GST/Adhar/Udhyam:", err);
        ['gst_no_id', 'adhar_no_id', 'udhaym_no_id'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.value = '';
                el.removeAttribute('disabled');
            }
        });
    }
}

async function loadAddresses(company_id) {

    console.log("======== B ============");
    const companyId = company_id.value;

    const address_url = "{{ route('addresses.fetch', [':company_id',':quote_billing_id',':quote_shipping_id' ]) }}";
    document.querySelector('.address-section').innerHTML = '';

    return new Promise((resolve, reject) => {

      var url=address_url.replace(':company_id', companyId).replace(':quote_billing_id',{{ $company->billing_address_id }}).replace(':quote_shipping_id', {{ $company->shipping_address_id }})

        getAjax(url, async function(res) {

            console.log("======== C ============");
            document.querySelector('.address-section').innerHTML = res;

            initAddressCardEvents();

            console.log("======== D ============");

            resolve(true);

        });

    });

}
//gst dropdown build from check address key
function fillGstDropdown(selectEl)
{
    const previousValue = parseFloat(selectEl.value || 0);
    const row = selectEl.closest('tr');
    const rowDefaultGst = parseFloat(
        row?.dataset?.defaultGst || row?.querySelector('.gst-value')?.value || 0
    );

    selectEl.innerHTML = '<option value="">Select GST</option>';

    const appendGstOption = (rate, label = null) => {
        const parsedRate = parseFloat(rate);
        if (!(parsedRate >= 0)) return;

        const exists = Array.from(selectEl.options).some(
            option => parseFloat(option.value) === parsedRate
        );
        if (exists) return;

        const opt = document.createElement('option');
        opt.value = parsedRate;
        opt.textContent = label || `${parsedRate}%`;
        selectEl.appendChild(opt);
    };
    const appendFallbackRates = () => {
        [5, 12, 18, 28].forEach(rate => appendGstOption(rate, `${rate}%`));
    };

    if (!GLOBAL_GST_LIST || Object.keys(GLOBAL_GST_LIST).length === 0) {
        appendFallbackRates();
        if (rowDefaultGst > 0) {
            appendGstOption(rowDefaultGst, `${rowDefaultGst}%`);
        }
    } else {
        const gstType = Object.keys(GLOBAL_GST_LIST)[0];
        const gstRates = GLOBAL_GST_LIST[gstType];
        if (gstRates && typeof gstRates === 'object' && Object.keys(gstRates).length > 0) {
            Object.entries(gstRates).forEach(([rate, split]) => {
                appendGstOption(rate, `${rate}% (${split})`);
            });
        } else {
            appendFallbackRates();
        }

        if (rowDefaultGst > 0) {
            appendGstOption(rowDefaultGst, `${rowDefaultGst}%`);
        }
    }

    if (previousValue > 0) {
        const hasPrevious = Array.from(selectEl.options).some(option => parseFloat(option.value) === previousValue);
        if (hasPrevious) {
            selectEl.value = String(previousValue);
        }
    }

}

function applyDefaultGstForRow(row) {
    const gstSelect = row.querySelector('.gst-record');
    if (!gstSelect) return;

    const gstValInput = row.querySelector('.gst-value');
    const defaultGst = parseFloat(row.dataset.defaultGst || gstValInput?.value || 0);

    if (defaultGst > 0) {
        const hasDefault = Array.from(gstSelect.options).some(option => parseFloat(option.value) === defaultGst);
        if (hasDefault) {
            gstSelect.value = String(defaultGst);
        }
    }

    if (gstValInput) {
        gstValInput.value = parseFloat(gstSelect.value) || 0;
    }

    gstSelect.dispatchEvent(new Event('change', { bubbles: true }));
}
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('gst-record')) {

        const row = e.target.closest('tr');
        if (!row) return;

        const gstValInput = row.querySelector('.gst-value');
        if (!gstValInput) return;

        gstValInput.value = parseFloat(e.target.value) || 0;

        updateTotals();
    }
});

// ========== LOAD PRODUCTS ==========
async function loadProducts(l_id) {
    const leadId = l_id.value;


    await fetchCustomerDetails({{ $quote_id->customer_id }});

    await loadAddresses(l_id);

    const available = await isCustomerAddressAvailable(leadId);

    if (!available.success) {
        btn.disabled = true;
        show_toastr('error', 'Customer billing address not found.');
        return;
    } else {
        btn.disabled = false;
        await display_product(l_id);
    }

    //old code
     // if (!available) {
    //     show_toastr('error', 'Customer billing address not found.');
    //     return;
    // }
    // await display_product(l_id);

}

// ========== ROW CALCULATION ==========
function calculateRowTotal(row)
{
     console.log('cal row func',row);
    const qty = parseFloat(row.querySelector(".product-quantity")?.value) || 0;
    const dealer = parseFloat(row.querySelector(".dealer-price")?.value) || 0;
    const discount = parseFloat(row.querySelector(".discount")?.value) || 0;


    const discountAmountPerUnit = (dealer * discount) / 100;
    const priceAfterDiscount = dealer - discountAmountPerUnit;
    const subTotal = qty * priceAfterDiscount;

    const gstPercent = parseFloat(row.querySelector('.gst-record')?.value) || 0;

    const gstAmount = (subTotal * gstPercent) / 100;

    const lineTotal = subTotal + gstAmount;

    const textEndElement = row.querySelector(".product_tol");
    if (textEndElement) {
        textEndElement.textContent = lineTotal.toFixed(2);

        const productTotalInput = row.querySelector('.product-total');
        if (productTotalInput) {
            productTotalInput.value = lineTotal.toFixed(2);
        }
    }

    return {
        lineTotal,
        discountAmount: qty * discountAmountPerUnit
    };

    /* old code
    const qty = parseFloat(row.querySelector(".product-quantity")?.value) || 0;
    const price = parseFloat(row.querySelector(".dealer-price")?.value) || 0;
    const discount = parseFloat(row.querySelector(".discount")?.value) || 0;

    const discountAmt = (price * discount) / 100;
    const lineTotal = qty * (price - discountAmt);

    row.querySelector(".text-end").textContent = lineTotal.toFixed(2);
    row.querySelector(".product-total").value = lineTotal.toFixed(2);

    return { lineTotal, discountAmt: qty * discountAmt };
    */
}

function updateTotals() {

        let subtotal = 0;
    let grandTotal = 0;
    let totalDiscount = 0;
    let totalGST = 0;

    // GST split totals
    let totalCGST = 0;
    let totalSGST = 0;
    let totalIGST = 0;

    const gstType = Object.keys(GLOBAL_GST_LIST || {})[0] || '';

    // ================= ROW CALCULATION =================
    document.querySelectorAll("tbody tr.product-row").forEach(row => {

        const qty = parseFloat(row.querySelector(".product-quantity")?.value) || 0;
        const dealer = parseFloat(row.querySelector(".dealer-price")?.value) || 0;
        const discount = parseFloat(row.querySelector(".discount")?.value) || 0;

        // discount first
        const discountAmountPerUnit = (dealer * discount) / 100;
        const priceAfterDiscount = dealer - discountAmountPerUnit;

        const rowSubtotal = qty * priceAfterDiscount;

        const gstPercent = parseFloat(row.querySelector('.gst-record')?.value) || 0;
        const rowGST = (rowSubtotal * gstPercent) / 100;

        // GST split
        if (gstType === 'CGST+SGST') {
            const half = rowGST / 2;
            totalCGST += half;
            totalSGST += half;
        } else if (gstType === 'IGST') {
            totalIGST += rowGST;
        }
         else if (gstType === 'GST') {
            totalIGST += rowGST;
        }

        totalGST += rowGST;

        const lineTotal = rowSubtotal + rowGST;

        // update row UI
        const rowTotalText = row.querySelector(".product_tol");
        if (rowTotalText) {
            rowTotalText.textContent = lineTotal.toFixed(2);
        }

        const productTotalInput = row.querySelector(".product-total");
        if (productTotalInput) {
            productTotalInput.value = lineTotal.toFixed(2);
        }

        subtotal += rowSubtotal;
        grandTotal += lineTotal;
        totalDiscount += qty * discountAmountPerUnit;
    });

    // ================= FOOTER UI =================
    const subTotalEl = document.querySelector(".subTotal");
    if (subTotalEl) subTotalEl.textContent = subtotal.toFixed(2);

    const totalDiscountEl = document.querySelector(".totalDiscount");
    if (totalDiscountEl) totalDiscountEl.textContent = totalDiscount.toFixed(2);

    const totalTaxEl = document.querySelector(".totalTax");
    if (totalTaxEl) totalTaxEl.textContent = totalGST.toFixed(2);

    const totalAmountEl = document.querySelector(".totalAmount");
    if (totalAmountEl) totalAmountEl.textContent = grandTotal.toFixed(2);

    // ================= HIDDEN INPUTS =================
    const subTotalInput = document.querySelector('input[name="sub_total"]');
    if (subTotalInput) subTotalInput.value = subtotal.toFixed(2);

    const taxInput = document.querySelector('input[name="tax"]');
    if (taxInput) taxInput.value = totalGST.toFixed(2);

    const totalAmtInput = document.querySelector('input[name="total_amt"]');
    if (totalAmtInput) totalAmtInput.value = grandTotal.toFixed(2);

    // ================= SUBTOTAL ROW VISIBILITY =================
    const subtotalRow = document.querySelector('.subtotal-row');
    if (subtotalRow) {
        subtotalRow.style.display = subtotal > 0 ? '' : 'none';
    }

    // ================= OLD TAX RATE SUM (KEEP AS IS) =================
    const taxRateSumEl = document.querySelector(".tax_rate_sum");
    if (taxRateSumEl) taxRateSumEl.textContent = '0.00';

    const taxRateSumInput = document.querySelector('input[name="tax_rate_sum"]');
    if (taxRateSumInput) taxRateSumInput.value = '0.00';

    // ================= GST HEADER NAME =================
    const gstHeader = document.querySelector('.dynamic_gst_name');
    if (gstHeader) {
        gstHeader.textContent = gstType ? gstType.replace('+', ' + ') : 'GST';
    }

    // ================= GST VALUE ROW PRINT =================
    const taxTypeRowEl = document.getElementById("tax-type-row");
    if (taxTypeRowEl && totalTaxEl) {

        // remove old rows
        while (
            taxTypeRowEl.nextElementSibling &&
            taxTypeRowEl.nextElementSibling.dataset.taxRow === "1"
        ) {
            taxTypeRowEl.nextElementSibling.remove();
        }

        const totalTax = parseFloat(totalTaxEl.textContent) || 0;
        // if (totalTax)
        // {

            const gstKey = Object.keys(GLOBAL_GST_LIST || {})[0];

            let taxEntries = [];

            if (gstKey === 'IGST') {
                taxEntries.push(['IGST', totalTax]);
            } else if (gstKey === 'GST') {
                taxEntries.push(['GST', totalTax]);
            } else if (gstKey === 'CGST+SGST') {
                const half = totalTax / 2;
                taxEntries.push(['CGST', half]);
                taxEntries.push(['SGST', half]);
            }

            taxEntries.forEach(([name, value]) => {
                const tr = document.createElement("tr");
                tr.dataset.taxRow = "1";
                tr.innerHTML = `
                    <td colspan="7"></td>
                    <td><strong>${name}</strong></td>
                    <td class="text-end">${value.toFixed(2)}</td>
                `;
                taxTypeRowEl.parentNode.insertBefore(tr, taxTypeRowEl.nextSibling);
            });
        // }
    }

    // ================= DEBUG (KEEP) =================
    console.log({
        gstType,
        CGST: totalCGST.toFixed(2),
        SGST: totalSGST.toFixed(2),
        IGST: totalIGST.toFixed(2),
        TotalGST: totalGST.toFixed(2)
    });

    applyDiscountVisibility();
}

// ========== EVENT HANDLERS ==========
document.addEventListener("input", e => {
    if (["product-quantity", "dealer-price", "discount"].some(c => e.target.classList.contains(c))) {
        updateTotals();
    }
});

document.addEventListener("click", e => {
    if (e.target.classList.contains("plus") || e.target.classList.contains("minus")) {
        const row = e.target.closest("tr");
        const qtyInput = row.querySelector(".product-quantity");
        let qty = parseInt(qtyInput.value) || 1;
        qty = e.target.classList.contains("plus") ? qty + 1 : Math.max(1, qty - 1);
        qtyInput.value = qty;
        updateTotals();
    }
});


document.addEventListener("input", function (e) {
    if (e.target.classList.contains("product-quantity")) {
        let value = e.target.value;
        if (value === "") return;

        let qty = parseInt(value);

        if (isNaN(qty) || qty < 1) {
            qty = 1;
        }

        e.target.value = qty;
    }
});
document.addEventListener("blur", function (e) {
    if (e.target.classList.contains("product-quantity")) {

        let value = e.target.value;

        if (!value || parseInt(value) < 1) {
            e.target.value = 1;
        }
        if (typeof updateTotals === "function") {
            updateTotals();
        }
    }
}, true);


$(document).on('click', '.remove-row', function() {
    $(this).closest('.product-row').remove();
    updateTotals();
});

// ========== ADD NEW PRODUCT ==========
document.getElementById('raw_id').addEventListener('change', function () {
    const selectedOption = this.options[this.selectedIndex];
    if (!selectedOption || !selectedOption.value) return;

    const productId = selectedOption.value;
    const leadDropdown = document.getElementById('lead_id');
    const leadId = leadDropdown ? leadDropdown.value.trim() : '';

    //  Step 1: Make sure a customer (lead) is selected
    if (!leadId) {
        show_toastr('error', 'Please select a customer before adding a product.');
        this.value = '';
        return;
    }

    //  Step 2: Prevent duplicate products
    const existingIds = Array.from(document.querySelectorAll('input[name="products[id][]"]')).map(i => i.value);
    if (existingIds.includes(productId)) {
        show_toastr('error', 'This product is already added.');
        this.value = '';
        return;
    }

    //  Step 3: Prepare basic info from dropdown
    const productName = selectedOption.dataset.name;
    const fallbackPrice = parseFloat(selectedOption.dataset.price || 0).toFixed(2);
    const productImg = selectedOption.dataset.image || "{{ \App\Models\Utility::defaultImage() }}";
    const unitList = JSON.parse(selectedOption.dataset.units || '{}');
    const defaultUnit = selectedOption.dataset.defaultUnit;

    const defaultGst = parseFloat(selectedOption.getAttribute('data-default-gst') || selectedOption.dataset.defaultGst || 0);

    //  Step 4: Build URL for AJAX
    //lead-id store cust-id original
    const urlTemplate = "{{ route('quotes.get_customer_price_history', ['lead_id' => ':lead_id', 'product_id' => ':product_id']) }}";
    const fetchUrl = urlTemplate
        .replace(':lead_id', leadId)// customer_id
        .replace(':product_id', productId);

    // 🟤 Step 5: Fetch price & discount history
    fetch(fetchUrl)
        .then(res => res.json())
        .then(data => {
            let productPrice = fallbackPrice;
            let productDiscount = '0.00';

            if (data.success) {
                productPrice = parseFloat(data.price || fallbackPrice).toFixed(2);
                productDiscount = parseFloat(data.discount || 0).toFixed(2);
            }

            // Build the unit dropdown
            let unitDropdown = '<select name="products[units][]" class="form-select item-unit" required>';
            Object.entries(unitList).forEach(([key, label]) => {
                const selected = key == defaultUnit ? 'selected' : '';
                unitDropdown += `<option value="${key}" ${selected}>${label}</option>`;
            });
            unitDropdown += '</select>';

            //  Step 6: Create the new product row
            const newRow = `
                <tr class="product-row" data-default-gst="${defaultGst}">
                    <input type="hidden" name="products[id][]" value="${productId}">
                    <td>${productName}</td>
                    <td><img src="${productImg}" height="70px" width="70px"></td>
                    <td><textarea name="products[short_notes][]" class="form-control" rows="3"></textarea></td>
                    <td>
                        <div class="input-step">
                            <button type="button" class="minus btn btn-danger">–</button>
                            <input type="number" class="product-quantity" name="products[qty][]" value="1" >
                            <button type="button" class="plus btn btn-success">+</button>
                        </div>
                    </td>
                    <td>${unitDropdown}</td>
                    <td><input type="number" name="products[price][]" class="form-control dealer-price" value="${productPrice}" step="0.01"></td>
                    <td>
                        <select name="products[gst][]" class="form-select gst-record"></select>
                        <input type="hidden" name="products[gst_value][]" class="gst-value" value="0">
                    </td>
                    <td class="hide_discount"><input type="number" name="products[discount][]" class="form-control  discount" value="${productDiscount}" step="0.01"></td>
                    <td class="">
                     <span class="product_tol"> ${productPrice}</span>
                       <input type="hidden" name="products[product_total][]" class="product-total" value="${productPrice}">
                    </td>

                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger remove-row">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </td>
                </tr>
            `;

            // Step 7: Insert and update totals
            document.querySelector('.product-list').insertAdjacentHTML('beforeend', newRow);

            //------new
            const lastRow = document.querySelector('.product-list tr:last-child');

            if (lastRow) {
                const gstSelect = lastRow.querySelector('.gst-record');
                if (gstSelect) fillGstDropdown(gstSelect);
                applyDefaultGstForRow(lastRow);
            }
            //----------------
            updateTotals();
        })
        .catch(err => {
            console.error("Error fetching customer price history:", err);
            show_toastr('error', 'Error fetching customer price history.');
        })
        .finally(() => {
            this.value = ''; // reset dropdown
        });
});


// ========== FORM VALIDATION ==========
document.getElementById("quotesEditForm").addEventListener("submit", function(e) {
    e.preventDefault();

    let valid = true;
    const required = [ 'datepicker-range'];//transport_id
    required.forEach(id => {
        const el = document.getElementById(id);
        if (!el || !el.value.trim()) {
            valid = false;
            show_toastr('error', `${id.replace('_', ' ')} is required.`);
        }
    });

    const customerType = document.getElementById('customer_type')?.value;
    if (customerType === 'regular' && !document.querySelector('input[name="payment_after_days"]')?.value) {
        show_toastr('error', "Payment After Days is required."); valid = false;
    }
    if (customerType === 'advance' && !document.querySelector('input[name="advance_payment"]')?.value) {
        show_toastr('error', "Advance Payment is required."); valid = false;
    }

    if (document.querySelectorAll('.product-row').length === 0) {
        show_toastr('error', 'Please add at least one product.');
        valid = false;
    }

    if (valid) this.submit();
});

// ========== INIT ==========
document.addEventListener("DOMContentLoaded", function() {
    const leadSelect = document.getElementById('lead_id');
    if (leadSelect && leadSelect.value) loadProducts(leadSelect);

     updateTotals();
     initExistingProductRows();

});
</script>
@endsection
