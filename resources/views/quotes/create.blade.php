@extends('layouts.app')

@section('page-css')
<style>
    html, body {
        overflow-x: hidden;
    }

    .form-suite {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
    }

    .form-suite .hero-shell {
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 28px;
        background:
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
            radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
            linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
        margin-bottom: 1rem;
    }

    .form-suite .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.76);
        border: 1px solid #dbeafe;
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .form-suite .hero-title {
        font-size: clamp(2rem, 3vw, 2.7rem);
        line-height: 1.05;
        letter-spacing: -0.04em;
        font-weight: 800;
        margin: 1rem 0 .45rem;
        color: #0f172a;
    }

    .form-suite .hero-subtitle {
        color: #64748b;
    }

    .flatpickr-months .flatpickr-month
    {
        background: white;
    }

 .hidden-row {
        display: none !important;
    }

    .quote-create-card {
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
    }

    .quote-topbar {
        border: 1px solid rgba(255, 255, 255, 0.78);
        background:
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
            radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
            linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        color: #0f172a;
        border-radius: 22px;
        padding: 16px 18px;
        margin-bottom: 14px;
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
    }

    .quote-step-badge {
        background: #eff6ff;
        border: 1px solid #dbeafe;
        color: #1d4ed8;
        border-radius: 999px;
        padding: 6px 11px;
        font-size: 12px;
        font-weight: 700;
    }

    .quote-form-section {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 16px;
        margin-bottom: 14px;
        background: #f8fafc;
    }

    .quote-form-section .section-title {
        font-size: 0.96rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 12px;
        letter-spacing: -0.02em;
    }

    .quote-actions {
        position: sticky;
        bottom: 0;
        background: rgba(255, 255, 255, 0.96);
        border-top: 1px solid #e2e8f0;
        padding-top: 12px;
        z-index: 5;
    }

    .quote-form-wrap {
        max-width: 100%;
    }

    .main-product-list {
        max-width: 100%;
        width: 100%;
    }

    .page-content,
    .container-fluid,
    .quote-create-card,
    .quote-form-section {
        overflow-x: clip;
    }

    .address-section > .row,
    .address-section .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    .address-section .address-cards-container {
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)) !important;
    }

    .form-suite .status-banner {
        border: 1px solid #dce4ee;
        border-radius: 18px;
        padding: 1rem 1.15rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }

    .form-suite .status-banner.status-danger {
        background: linear-gradient(135deg, #fef2f2 0%, #fff1f2 100%);
        border-color: #fecdd3;
        color: #be123c;
    }
</style>
@endsection

@section('content')
    <!-- Product Modal  Start -->
@php
    $default_img  =\App\Models\Utility::defaultImage();
    $check_discount_allow = \App\Models\Utility::isDiscountAllowed();
@endphp

    <div class="page-content form-suite">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="hero-shell">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-8">
                                    <span class="hero-eyebrow">Create Workflow</span>
                                    <h1 class="hero-title">Create Quote</h1>
                                    <p class="hero-subtitle mb-0">Capture customer details, build the product mix, and confirm pricing in the same cleaner workspace as the rest of the refreshed app.</p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('quotes.index') }}">Quotes</a></li>
                                            <li class="breadcrumb-item active">Create</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-12">
                    <div class="quote-topbar d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="mb-1">Create Quotation</h5>
                            <small class="text-muted">Fill customer details, add products, verify totals, then save.</small>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="quote-step-badge">1. Customer</span>
                            <span class="quote-step-badge">2. Products</span>
                            <span class="quote-step-badge">3. Payment</span>
                        </div>
                    </div>
                </div>
                <!-- Varying Modal Content -->
                <div class="col-lg-12">
                    <div class="card quote-create-card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h5 class="card-title  mb-0">Quote Builder</h5>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="offcanvas" href="#offcanvasExample">
                                    Add Customer
                                </button>
                            </div>

                        </div>
                        <div class="card-body quote-form-wrap">
                            {{ Form::open(['route' => 'quotes.store', 'method' => 'post', 'enctype' => 'multipart/form-data', 'id' => 'quotesForm', 'autocomplete' => 'off']) }}
                            @if ($errors->any())
                                <div class="status-banner status-danger mb-3">
                                    {{ $errors->first() }}
                                </div>
                            @endif
                            <div class="row">
                                <div class="col-12">
                                    <div class="quote-form-section">
                                        <div class="section-title">Customer & Quote Details</div>
                                        <div class="row g-3">

                                <div class="col-md-6">

                                    {{-- customer-id store in lead_id --}}
                                    <label for="lead" class="form-label">Customer <span
                                            class="text-danger">*</span></label>
                                    {{ Form::select('lead_id', ['' => 'Select Customer'] + $leads->toArray(), $new_customer_id ?? old('lead_id'), [
                                        'class' => 'form-select form-control ' . (!$lead_id ? 'choices-select' : 'choices-select-1'),
                                        'id' => 'lead_id',
                                        'data-choices',
                                        'data-choices-removeItem',
                                        'onChange="loadProducts(this)"',
                                        'required' => true,
                                    ]) }}

                                    <span class="text-danger error-msg" id="error-lead_id"></span>

                                    {{-- this lead id from lead-view-screen thrg pass --}}
                                   <input type="hidden" name="new_lead_id" value="{{ $new_lead_id ?? null }}" id="new_lead_id">
                                </div>

                                <div class="col-md-6">

                                    <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                    {{ Form::select('customer_type', ['' => 'Select Type'] + $client_type,old('customer_type'), [
                                        'class' => 'form-select  choices-select form-control',
                                        'id' => 'customer_type',
                                        'data-choices',
                                        'data-choices-removeItem',
                                        'required' => true,
                                    ]) }}

                                    <span class="text-danger error-msg" id="error-customer_type"></span>

                                </div>

                                <div class="col-md-6">
                                    {{ Form::label('date', __('Quote Date'), ['class' => 'form-label']) }} <span
                                        class="text-danger">*</span>
                                    {{ Form::date('date', old('date',null), [
                                        'class' => 'form-control datepicker-range',
                                        'id' => 'datepicker-range',
                                        'placeholder' => __('Enter date.'),
                                        'data-provider' => 'flatpickr',
                                        'data-range' => 'true',
                                        'required' => true,
                                    ]) }}

                                    <span class="text-danger error-msg" id="error-datepicker-range"></span>
                                </div>

                                <div class="col-md-6">

                                    <label for="type" class="form-label">Transport </label>
                                             <a href="javascript:void(0);" class="float-end" data-size="md" data-url="{{ route('transports.quick_create') }}" data-ajax-popup="true" data-bs-original-title="{{__('Add Transport')}}"><i class="ri-add-line align-bottom me-1"></i> Add New Transport</a>
                                    {{ Form::select('transport_id', ['' => 'Select Transport'] + $transport_list,  old('transport_id'), [
                                        'class' => 'form-select choices-select form-control',
                                        'id' => 'transport_id',
                                        'data-choices',
                                        'data-choices-removeItem',
                                    ]) }}

                                    <span class="text-danger error-msg" id="error-transport_id"></span>

                                </div>


                                <div class="col-12">
                                <div class="row g-3">

                                     <div class="col-md-3">
                                        <label for="company_name" class="form-label">Company Name</label>
                                        {{ Form::text('company_name',  old('company_name'), [
                                            'class' => 'form-control',
                                            'id' => 'company_name_id',
                                            'maxlength' => 120,
                                        ]) }}
                                        <span class="text-danger error-msg" id="error-company_name_id"></span>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="gst_no" class="form-label">GST No</label>
                                        {{ Form::text('gst_no', old('gst_no'), [
                                            'class' => 'form-control',
                                            'id' => 'gst_no_id',
                                            'maxlength' => 15,
                                        ]) }}
                                         @error('gst_no')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        <span class="text-danger error-msg" id="error-gst_no_id"></span>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="adhar_no" class="form-label">Adhar Number</label>
                                        {{ Form::text('adhar_no',  old('adhar_no'), [
                                            'class' => 'form-control',
                                            'id' => 'adhar_no_id',
                                            'maxlength' => 12,
                                        ]) }}
                                        <span class="text-danger error-msg" id="error-adhar_no"></span>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="udhaym_no" class="form-label">Udhyam Number</label>
                                        {{ Form::text('udhaym_no', old('udhaym_no'), [
                                            'class' => 'form-control',
                                            'id' => 'udhaym_no_id',
                                            'maxlength' => 20,
                                        ]) }}
                                        <span class="text-danger error-msg" id="error-udhaym_no"></span>
                                    </div>

                                </div>
                                </div>

                                  <div class="col-12 address-section">

                                    @php
                                        $company = \App\Models\Entity::find($new_customer_id);
                                    @endphp

                                    @include('address.address_selection',['company' => $company,'billing_address_id'=>null,'shipping_address_id'=>null])
                                    </div>
                                    </div>
                                </div>


                                <div class="col-12 mt-2">
                                    <div class="quote-form-section">
                                        <div class="section-title">Products & Pricing</div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label">Products</label>
                                                <a href="javascript:void(0);" class="float-end" data-size="md" data-url="{{ route('products.quick_create') }}" data-ajax-popup="true" data-bs-original-title="{{__('Add Product')}}"><i class="ri-add-line align-bottom me-1"></i> Add New Product</a>
                                                <div class="">
                                                    <select class="form-select" id="raw_id" data-choices data-choices-removeItem>
                                                        <option value="">Select Product</option>
                                                        @foreach ($product_list as $product)
                                                            <option value="{{ $product->id }}" data-product-id="{{ $product->id }}" data-marketplace-listing-id="" data-name="{{ $product->name }}"
                                                                data-image="{{ $product->image ?? $default_img }}"
                                                                data-price="{{ $product->price }}"
                                                                data-units='@json(\App\Models\Utility::getUnits($product->unit_type))'
                                                                data-default-unit="{{ $product->unit_type }}"
                                                                data-default-gst="{{ $product?->getGstSlabMaster?->rate ?? 0 }}"
                                                                >
                                                                Master: {{ $product->sku_code }} - {{ $product->name }}
                                                            </option>
                                                            @foreach (($product->relationLoaded('marketplaceListings') ? $product->marketplaceListings : collect()) as $listing)
                                                                <option value="{{ $product->id }}::{{ $listing->id }}" data-product-id="{{ $product->id }}" data-marketplace-listing-id="{{ $listing->id }}" data-name="{{ $listing->listing_title }}" data-image="{{ $product->image ?? $default_img }}" data-price="{{ $listing->selling_price ?: $product->price }}" data-units='@json(\App\Models\Utility::getUnits($product->unit_type))' data-default-unit="{{ $product->unit }}" data-default-gst="{{ $product?->getGstSlabMaster?->rate ?? 0 }}" data-platform="{{ $listing->platform }}" data-platform-sku="{{ $listing->platform_sku }}">
                                                                    {{ ucfirst($listing->platform) }}: {{ $listing->platform_sku }} - {{ $listing->listing_title }}
                                                                </option>
                                                            @endforeach
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <span class="text-danger error-msg d-block mt-2" id="error-products"></span>
                                            </div>
                                            <div class="col-12 main-product-list">
                                                @include('products.customer-product-list-table')
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 offset-md-6" id="advance_payment" style="display: none;">
                                    <label>Advance Payment</label> <x-required></x-required>
                                    <input type="number" step="0.01" min="0" name="advance_payment" class="form-control" value="{{ old('advance_payment') }}">
                                </div>

                                <div class="col-md-6 offset-md-6" id="payment_after_days" >{{-- style="display: none;" --}}
                                    <label>Payment After Days</label> <x-required></x-required>
                                    <input type="number" min="0" max="365" name="payment_after_days" class="form-control" value="{{ old('payment_after_days') }}">
                                </div>


                                <div class="col-12 mt-4 quote-actions">
                                    <div class="hstack gap-2 justify-content-end">
                                        <a href="{{ route('quotes.index') }}"  class="btn btn-light" >Close</a>
                                        <button type="submit" class="btn btn-success" name="save"
                                            value="only_save" id="saveBtn">Save</button>
                                        {{-- <button type="submit" class="btn btn-danger" name="save" value="save_send"
                                            data-bs-dismiss="modal"><i class="ri-send-plane-fill align-bottom me-1"></i>
                                            Save & Send</button> --}}
                                    </div>
                                </div>

                            </div>
                            {{ Form::close() }}

                            <div class="">
                                @include('customer.customer_model')
                            </div>



                        </div>
                    </div>
                </div>
            </div>



<script>
let GLOBAL_GST_LIST = {};
let _address = { billing_address:'', shipping_address:''};
const new_lead_id = document.getElementById('new_lead_id');

function syncTaxJsonInput(taxData = {}) {
    const taxJsonInput = document.querySelector('input[name="tax_json_data"]');
    if (!taxJsonInput) return;

    const normalizedTaxData =
        taxData && typeof taxData === 'object' && !Array.isArray(taxData)
            ? taxData
            : {};

    const taxJsonValue = JSON.stringify(normalizedTaxData);
    taxJsonInput.value = taxJsonValue;
    taxJsonInput.setAttribute('value', taxJsonValue);
}

function buildTaxJsonFromGstList() {
    const gstTypeKey = Object.keys(GLOBAL_GST_LIST || {})[0] || '';

    if (!gstTypeKey) {
        return {};
    }

    return {
        CGST: gstTypeKey === 'CGST+SGST' ? 1 : 0,
        SGST: gstTypeKey === 'CGST+SGST' ? 1 : 0,
        IGST: gstTypeKey === 'IGST' ? 1 : 0,
        GST: gstTypeKey === 'GST' ? 1 : 0,
    };
}
</script>
<script>

/* ============================================================
   CUSTOMER & PRODUCT HANDLING
============================================================ */
 let btn = document.getElementById('saveBtn');
async function isCustomerAddressAvailable(leadId) {

    console.log('============ check cust adr ============ 2');
    //lead-id store cust-id original
    const url = "{{ route('quotes.check_cust_adr', ':lead_id') }}".replace(':lead_id', leadId);
    try {
        const response = await fetch(url);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error("Error checking customer address:", error);
        return false;
    }
}

async function has_leads(e, l) {
    console.log("Calling has_leads::");

    const companyId = e.value;
    const leadId = l.value;

    return new Promise((resolve, reject) => {

        if(leadId == '' || leadId == undefined){

            /* Find Recent Leads */
            const lead_url = "{{ route('leads.fetch-recent', [':company_id']) }}";

            getAjax(lead_url.replace(':company_id', companyId), async function(res) {

                $(new_lead_id).val(res.id);
                console.log("Start Calling display_product::A");
                await display_product(e, l); //customer-id,lead-id
                resolve(true);
            });

        } else {
            console.log("Start Calling display_product::B");

            display_product(e, l).then(() => {
                resolve(true);
            });

        }

    });

}

async function display_product(e,l) //customer-id,lead-id
{
    console.log('============ lead product list ============ 3');

    const customerId = e.value; //customer_id
    const leadId = l.value;  //lead-id
    const hasLeadId = typeof leadId === 'string' ? leadId.trim().length > 0 : !!leadId;

    console.log('customer-id ',customerId);
      console.log('leadId ',leadId);

    console.log("calling::_check_address===========");

    await _check_address(_address);

    console.log("_billing_address=",_address.billing_address);
    // if(_address.billing_address == ''){

    //     show_toastr('error', 'Please select billing address.');
    //     return;

    // }

     if(!hasLeadId){

         console.log('leadId not if consition ');
        document.querySelector('.product-list').innerHTML = '';
        // applySavedGstSelections();
        updateTotals();
        // lockGstDropdowns();
    }

    if(hasLeadId)
    {
        document.querySelector('.main-product-list').innerHTML = '';


        //lead-id store cust-id original
        const raw_url = "{{ route('leads.get_customer_lead_product', ['#']) }}";
        getAjax(raw_url.replace('#', customerId), async function (responseHtml)
        {
            document.querySelector('.main-product-list').innerHTML = responseHtml;

            console.log('gst global',GLOBAL_GST_LIST);



            window.addEventListener("load", updateTotals);

            // ===== TAX SECTION =====
            const addressCheck = (await isCustomerAddressAvailable(customerId)) || {};
            const taxData = addressCheck.tax_data || {};

            const taxTypeRowEl = document.getElementById("tax-type-row");
            syncTaxJsonInput(taxData);


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

            // GST table header
            const gstHeader = document.querySelector('.dynamic_gst_name');
            if (gstHeader) {
                gstHeader.textContent = 'GST';

                if (Object.keys(GLOBAL_GST_LIST).length > 0) {
                    const gstType = Object.keys(GLOBAL_GST_LIST)[0]; // CGST+SGST OR IGST
                    gstHeader.textContent = gstType.replace('+', ' + ');
                }
            }

            // Apply GST dropdown to already existing rows
            document.querySelectorAll('.gst-record').forEach(select => {
                fillGstDropdown(select);


            });

            //----------------- default gst selected -----------------
            document.querySelectorAll('.product-row').forEach(row =>
            {
                const gstSelect = row.querySelector('.gst-record');
                if (!gstSelect)
                {
                    console.log('gst-rcd not get');
                    return;
                }

                // Fill dropdown AFTER GST list is ready
                fillGstDropdown(gstSelect);

                const defaultGst = parseFloat(row.dataset.defaultGst || 0);
                const gstValInput = row.querySelector('.gst-value');
                if (defaultGst && gstSelect)
                {
                    setTimeout(() => {
                        gstSelect.value = defaultGst;
                        if (gstValInput)
                        {
                            gstValInput.value = defaultGst;
                        }

                        gstSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    }, 0);
                }
            });
            //-------------------------------


            updateTotals();
        });
    }



    await fetchCustomerDetails(customerId);

        applyDiscountVisibility();
}

async function fetchCustomerDetails(customerId) {
    if (!customerId) return;

    const gstUrl = "{{ route('leads.get-gst', ['#']) }}".replace('#', customerId);

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

        const customerInput = document.getElementById('company_name_id');
        const gstInput = document.getElementById('gst_no_id');
        const adharInput = document.getElementById('adhar_no_id');
        const udhaymInput = document.getElementById('udhaym_no_id');

        const compNm = data.company_name || '';
        if (customerInput) {
            customerInput.value = compNm;
            compNm !== '' ? customerInput.setAttribute('disabled', true) : customerInput.removeAttribute('disabled');
        }

        const gst = data.gst_no || '';
        if (gstInput) {
            gstInput.value = gst;
            gstInput.readOnly = gst !== '';
            gst !== '' ? gstInput.setAttribute('disabled', true) : gstInput.removeAttribute('disabled');
        }

        const adhar = data.adhar_nub || '';
        if (adharInput) {
            adharInput.value = adhar;
            adharInput.readOnly = adhar !== '';
            adhar !== '' ? adharInput.setAttribute('disabled', true) : adharInput.removeAttribute('disabled');
        }

        const udhyam = data.udhyam_nub || '';
        if (udhaymInput) {
            udhaymInput.value = udhyam;
            udhaymInput.readOnly = udhyam !== '';
            udhyam !== '' ? udhaymInput.setAttribute('disabled', true) : udhaymInput.removeAttribute('disabled');
        }
    } catch (err) {
        const customerInput = document.getElementById('company_name_id');
        const gstInput = document.getElementById('gst_no_id');
        const adharInput = document.getElementById('adhar_no_id');
        const udhaymInput = document.getElementById('udhaym_no_id');

        if (customerInput) {
            customerInput.value = '';
            customerInput.removeAttribute('disabled');
        }
        if (gstInput) {
            gstInput.value = '';
            gstInput.readOnly = false;
            gstInput.removeAttribute('disabled');
        }
        if (adharInput) {
            adharInput.value = '';
            adharInput.readOnly = false;
            adharInput.removeAttribute('disabled');
        }
        if (udhaymInput) {
            udhaymInput.value = '';
            udhaymInput.readOnly = false;
            udhaymInput.removeAttribute('disabled');
        }

        console.error("Failed to fetch GST:", err);
    }
}

//gst dropdown build from check address key
function fillGstDropdown(selectEl)
{
    console.log('fill gst function ()',selectEl);
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

function callAddress(url, resolve) {

    getAjax(url, function(res) {

        document.querySelector('.address-section').innerHTML = res;

        if (typeof initAddressCardEvents === 'function') {
            initAddressCardEvents();
        }

        resolve(true);
    });
}

async function loadAddresses(company_id) {

    console.log('============ get address ============');

    console.log("======== B ============");
    const companyId = company_id.value; //customer-id

    const address_url = "{{ route('addresses.fetch', [':company_id',':lead_billing_id',':lead_shipping_id' ]) }}";
    document.querySelector('.address-section').innerHTML = '';


    return new Promise((resolve, reject) => {


        let url = '';

        @if($lead_billing_id || $lead_shipping_id)
            console.log('lead billing n shiping adr is');
            // get address from create function when lead-view-from-quote-create
            url = address_url
                .replace(':company_id', companyId)
                .replace(':lead_billing_id', {{ $lead_billing_id ?? 'null' }})
                .replace(':lead_shipping_id', {{ $lead_shipping_id ?? 'null' }});

            fetchAddresses(url);

        @else
        console.log('lead billing n shiping adr not else');
            // customer onchange select address
            let custUrl =
                "{{ route('addresses.get_cust_address', ':company_id') }}"
                .replace(':company_id', companyId);

            getAjax(custUrl, function(res) {

                let billingId  = res.billing_address_id ?? 'null';
                let shippingId = res.shipping_address_id ?? 'null';

                url = address_url
                    .replace(':company_id', companyId)
                    .replace(':lead_billing_id', billingId)
                    .replace(':lead_shipping_id', shippingId);

                fetchAddresses(url);
            });

        @endif

        function fetchAddresses(url) {

            getAjax(url, function(res) {

                console.log("======== C ============");
                document.querySelector('.address-section').innerHTML = res;

                initAddressCardEvents();

                console.log("======== D ============");
                resolve(true);
            });
        }
    });

}


async function loadProducts(l_id) {


    console.log('============ loadProducts() A ============ ');
    console.log("load product start");

    const leadId = l_id.value;
    await fetchCustomerDetails(leadId);
    await loadAddresses(l_id);

    await _check_address(_address);

    console.log('_address billing_address ',_address.billing_address);

    if(_address.billing_address != ''){


        const isAvailableStart = await isCustomerAddressAvailable(leadId);

        console.log('isAvailableStart ', isAvailableStart);

        if (!isAvailableStart.success) {
            btn.disabled = true;
            show_toastr('error', 'Customer billing address not found.');
            return;
        } else {
            btn.disabled = false;

            GLOBAL_GST_LIST = isAvailableStart.gst_list || {};

            await has_leads(l_id,new_lead_id); //customer-id ,lead-id
            // await display_product(l_id);
        }
    } else {
        console.log("======== F ============");

        // Ensure GST list is available even when no address radio is selected yet.
        const addressCheck = await isCustomerAddressAvailable(leadId);
        GLOBAL_GST_LIST = (addressCheck && addressCheck.gst_list) ? addressCheck.gst_list : {};

        await has_leads(l_id, new_lead_id);
    }
}

/* ============================================================
   TOTALS & CALCULATIONS
============================================================ */

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

    document.querySelectorAll("tbody tr.product-row").forEach(row => {
        const qty = parseFloat(row.querySelector(".product-quantity")?.value) || 0;
        const dealer = parseFloat(row.querySelector(".dealer-price")?.value) || 0;
        const discount = parseFloat(row.querySelector(".discount")?.value) || 0;

        //  Discount first
        const discountAmountPerUnit = (dealer * discount) / 100;
        const priceAfterDiscount = dealer - discountAmountPerUnit;

        //  Row subtotal after discount
        const rowSubtotal = qty * priceAfterDiscount;

        //  GST amount
        const gstPercent = parseFloat(row.querySelector('.gst-record')?.value) || 0;
        const rowGST = (rowSubtotal * gstPercent) / 100;

        //  GST split logic
        if (gstType === 'CGST+SGST') {
            const halfGST = rowGST / 2;
            totalCGST += halfGST;
            totalSGST += halfGST;
        } else if (gstType === 'IGST') {
            totalIGST += rowGST;
        }
        else if (gstType === 'GST') {
            totalIGST += rowGST;
        }

        totalGST += rowGST;

        //  Final row total (after discount + GST)
        const lineTotal = rowSubtotal + rowGST;

        //  Update row UI
        const textEndEl = row.querySelector(".product_tol");
        if (textEndEl) textEndEl.textContent = lineTotal.toFixed(2);

        const productTotalInput = row.querySelector(".product-total");
        if (productTotalInput) productTotalInput.value = lineTotal.toFixed(2);

        subtotal += rowSubtotal;
        grandTotal += lineTotal;
        totalDiscount += qty * discountAmountPerUnit;
    });

    // 🔹 Update totals in footer
    document.querySelector(".subTotal").textContent = subtotal.toFixed(2);
    document.querySelector(".totalDiscount").textContent = totalDiscount.toFixed(2);
    document.querySelector(".totalTax").textContent = totalGST.toFixed(2);
    document.querySelector(".totalAmount").textContent = grandTotal.toFixed(2);

    // 🔹 Hidden inputs
    const subtotalRow = document.querySelector('.subtotal-row');

    if (subtotalRow) {
        if (subtotal > 0) {
            subtotalRow.style.display = '';
        } else {
            subtotalRow.style.display = 'none';
        }
    }

    document.querySelector('input[name="sub_total"]').value = subtotal.toFixed(2);
    document.querySelector('input[name="tax"]').value = totalGST.toFixed(2);
    document.querySelector('input[name="total_amt"]').value = grandTotal.toFixed(2);


    //before code gst all sum of per value 2%sgst+2%cgst
    document.querySelector(".tax_rate_sum").textContent = '0.00';
    document.querySelector('input[name="tax_rate_sum"]').value = '0.00';

    //  Update dynamic GST name and value
    const gstHeader = document.querySelector('.dynamic_gst_name');
    if (gstHeader) {
        gstHeader.textContent = gstType ? gstType.replace('+', ' + ') : 'GST';
    }

    // Optional: show CGST / SGST split
    console.log({
        gstType,
        CGST: totalCGST.toFixed(2),
        SGST: totalSGST.toFixed(2),
        IGST: totalIGST.toFixed(2),
        TotalGST: totalGST.toFixed(2)
    });

    //------------------------- gst value print
    const taxTypeRowEl = document.getElementById("tax-type-row");
    const totalTaxEl = document.querySelector(".totalTax");

    if (taxTypeRowEl && totalTaxEl) {

        // Remove old dynamic tax rows
        while (
            taxTypeRowEl.nextElementSibling &&
            taxTypeRowEl.nextElementSibling.dataset.taxRow === "1"
        ) {
            taxTypeRowEl.nextElementSibling.remove();
        }

        const totalTax = parseFloat(totalTaxEl.textContent) || 0;
        // if (totalTax <= 0) return;

        // Detect GST type from GLOBAL_GST_LIST
        const gstTypeKey = Object.keys(GLOBAL_GST_LIST || {})[0];
        syncTaxJsonInput(buildTaxJsonFromGstList());

        if (!gstTypeKey) return;

        let taxEntries = [];

        if (gstTypeKey === 'IGST') {
            taxEntries.push(['IGST', totalTax]);
        }

        if (gstTypeKey === 'GST') {
            taxEntries.push(['GST', totalTax]);
        }

        if (gstTypeKey === 'CGST+SGST') {
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

            taxTypeRowEl.parentNode.insertBefore(
                tr,
                taxTypeRowEl.nextSibling
            );
        });
    }

    applyDiscountVisibility();
}

/* ============================================================
   EVENTS: Quantity +/-, Input updates
============================================================ */

document.addEventListener("input", function (e) {
    if (
        e.target.classList.contains("product-quantity") ||
        e.target.classList.contains("dealer-price") ||
        e.target.classList.contains("discount")
    ) {
        updateTotals();
    }
});

document.addEventListener("click", function (e) {
    if (e.target.classList.contains("plus") || e.target.classList.contains("minus")) {
        const row = e.target.closest("tr");
        const qtyInput = row.querySelector(".product-quantity");
        let qty = parseInt(qtyInput.value) || 1;

        if (e.target.classList.contains("plus")) qty++;
        else if (e.target.classList.contains("minus")) qty = qty > 1 ? qty - 1 : 1;

        qtyInput.value = qty;
        updateTotals();
    }
});

//new_cd
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


/* ============================================================
   CLIENT TYPE HANDLER
============================================================ */

$(document).ready(function () {
    const client_dp = document.getElementById("customer_type");
    const adv_payment = document.getElementById('advance_payment');
    const after_payment = document.getElementById('payment_after_days');

    client_dp.addEventListener('change', function () {
        if (client_dp.value === 'regular') {
            after_payment.style.display = "block";
            adv_payment.style.display = "none";
        } else {
            adv_payment.style.display = "block";
            after_payment.style.display = "none";
        }
    });

    // Remove product row
    $(document).on('click', '.remove-row', function () {
        $(this).closest('.product-row').remove();
        updateTotals();
    });
});

/* ============================================================
   ADD PRODUCT HANDLER
============================================================ */

document.getElementById('raw_id').addEventListener('change', function () {

    console.log("=============== product single add =============== ");
    const leadDropdown = document.getElementById('lead_id');
    const lead_id = leadDropdown ? leadDropdown.value.trim() : '';

    const selectedOption = this.options[this.selectedIndex];
    if (!selectedOption || !selectedOption.value) return;

    const productId = selectedOption.dataset.productId || selectedOption.value;
    const marketplaceListingId = selectedOption.dataset.marketplaceListingId || '';
    const rowKey = marketplaceListingId ? `${productId}:${marketplaceListingId}` : `${productId}:master`;


    if (!lead_id) {
        show_toastr('error', 'Please select a customer before adding a product.');
        this.value = '';
        leadDropdown.focus();
        return;
    }

    const productName = selectedOption.dataset.name;
    const listingBadge = marketplaceListingId ? `<div class="small text-muted">${(selectedOption.dataset.platform || '').toUpperCase()} SKU: ${selectedOption.dataset.platformSku || ''}</div>` : '';
    const productImg = selectedOption.dataset.image || "{{ \App\Models\Utility::defaultImage() }}";
    const unitList = JSON.parse(selectedOption.dataset.units || '{}');
    const defaultUnit = selectedOption.dataset.defaultUnit;

    const existingIds = Array.from(document.querySelectorAll('input[name="products[row_key][]"]')).map(i => i.value);
    if (existingIds.includes(rowKey)) {
        show_toastr('error', 'This product is already added.');
        this.value = '';
        return;
    }

   const defaultGst = selectedOption.getAttribute('data-default-gst') || 0;
   console.log("product gst:", defaultGst);

    //get customer previous price,disc val
    //lead-id store cust-id original
    const urlTemplate = "{{ route('quotes.get_customer_price_history', ['lead_id' => ':lead_id', 'product_id' => ':product_id']) }}";
    const fetchUrl = urlTemplate
        .replace(':lead_id', lead_id)
        .replace(':product_id', productId);

    fetch(fetchUrl)
        .then(res => res.json())
        .then(data => {

            let productPrice, productDiscount;

            if (data.success && data.price !== undefined && data.price !== null && data.price != 0) {
                productPrice = parseFloat(data.price).toFixed(2);
                productDiscount = parseFloat(data.discount || 0).toFixed(2);
            } else {
                productPrice = parseFloat(selectedOption.dataset.price || 0).toFixed(2);
                productDiscount = '0.00';
            }

            console.log("Final product price:", productPrice, "discount:", productDiscount);

            let unitDropdown = '<select name="products[units][]" class="form-select item-unit" required>';
            Object.entries(unitList).forEach(([key, label]) => {
                const selected = key == defaultUnit ? 'selected' : '';
                unitDropdown += `<option value="${key}" ${selected}>${label}</option>`;
            });
            unitDropdown += '</select>';

            const newRow = `
               <tr class="product-row" data-default-gst="${defaultGst}">

                <td>
                    ${productName}
                    <input type="hidden" name="products[id][]" value="${productId}">
                    <input type="hidden" name="products[listing_id][]" value="${marketplaceListingId}">
                    <input type="hidden" name="products[row_key][]" value="${rowKey}">
                    ${listingBadge}
                </td>

                <td>
                    <img src="${productImg}" height="70px" width="70px">
                </td>

                <td>
                    <textarea name="products[short_notes][]" class="form-control" cols="8" rows="3"></textarea>
                </td>

                <td>
                    <div class="input-step">
                        <button type="button" class="minus btn btn-danger">–</button>
                        <input type="number" class="product-quantity" name="products[qty][]" value="1">
                        <button type="button" class="plus btn btn-success">+</button>
                    </div>
                </td>

                <td>${unitDropdown}</td>

                <td>
                    <input type="hidden" name="products[mrp][]" value="${productPrice}">
                    <input type="number" name="products[price][]" class="form-control dealer-price" value="${productPrice}" >
                </td>

                <td>
                    <select name="products[gst][]" class="form-select gst-record"></select>
                    <input type="hidden" name="products[gst_value][]" class="gst-value" value="0">
                </td>

                <td class="hide_discount">
                    <input type="number" name="products[discount][]" class="form-control discount" value="${productDiscount}">
                </td>

                <td class="text-end">
                    <span class="product_tol">${productPrice}</span>
                     <input type="hidden"
                        name="products[product_total][]"
                        class="product-total"
                        value="${productPrice}">
                </td>


                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remove-row">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>

            </tr>
            `;

            document.querySelector('.product-list').insertAdjacentHTML('beforeend', newRow);

            const lastRow = document.querySelector('.product-list tr:last-child');

            if (lastRow) {
                const gstSelect = lastRow.querySelector('.gst-record');
                if (gstSelect) {
                    fillGstDropdown(gstSelect);

                    const gstVal = parseFloat(defaultGst) || 0;
                    if (gstVal > 0) {
                        const hasMatchingOption = Array.from(gstSelect.options).some(opt => parseFloat(opt.value) === gstVal);
                        if (!hasMatchingOption) {
                            const fallbackOpt = document.createElement('option');
                            fallbackOpt.value = gstVal;
                            fallbackOpt.textContent = `${gstVal}%`;
                            gstSelect.appendChild(fallbackOpt);
                        }

                        gstSelect.value = String(gstVal);
                        const gstInput = lastRow.querySelector('.gst-value');
                        if (gstInput) gstInput.value = gstVal;
                        gstSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            }


            updateTotals();
        })
        .catch(err => {
            console.error("Error fetching customer price:", err);
            show_toastr('error', 'Error loading customer price data.');
        });

    this.value = '';
});



document.getElementById("quotesForm").addEventListener("submit", function (e) {
    e.preventDefault();

    let isValid = true;
    const requiredFieldIds = [
        'lead_id',
        'customer_type',
        'datepicker-range',
        //'transport_id',
        // 'gst_no_id',
    ];

    requiredFieldIds.forEach(id => {
        const errorEl = document.getElementById('error-' + id);
        if (errorEl) errorEl.textContent = '';
    });
    document.getElementById('error-products').textContent = '';

    requiredFieldIds.forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;

        if (!el.value.trim()) {
            document.getElementById('error-' + id).textContent = "This field is required.";
            isValid = false;
        }
    });

    const customerType = document.getElementById('customer_type').value;
    const advPaymentEl = document.querySelector('input[name="advance_payment"]');
    const afterDaysEl = document.querySelector('input[name="payment_after_days"]');
    const gstNoEl = document.getElementById('gst_no_id');
    const totalAmtEl = document.querySelector('input[name="total_amt"]');

    if (customerType === 'regular') {
        if (!afterDaysEl.value.trim()) {
            show_toastr('error', "Payment After Days is required.");
            afterDaysEl.focus();
            isValid = false;
        } else if (Number(afterDaysEl.value) < 0 || Number(afterDaysEl.value) > 365) {
            show_toastr('error', "Payment After Days must be between 0 and 365.");
            afterDaysEl.focus();
            isValid = false;
        }
    } else {
        if (!advPaymentEl.value.trim()) {
            show_toastr('error', "Advance Payment is required.");
            advPaymentEl.focus();
            isValid = false;
        } else if (Number(advPaymentEl.value) < 0) {
            show_toastr('error', "Advance Payment cannot be negative.");
            advPaymentEl.focus();
            isValid = false;
        }
    }

    const productIds = document.querySelectorAll('input[name="products[id][]"]');
    if (productIds.length === 0) {
        document.getElementById('error-products').textContent = "Please add at least one product.";
        isValid = false;
    }

    const productRows = document.querySelectorAll('.product-list tr');
    productRows.forEach((row) => {
        const qtyEl = row.querySelector('.product-quantity');
        const priceEl = row.querySelector('.dealer-price');
        const discountEl = row.querySelector('.discount');

        const qty = Number(qtyEl?.value || 0);
        const price = Number(priceEl?.value || 0);
        const discount = Number(discountEl?.value || 0);

        if (!qty || qty <= 0) {
            isValid = false;
            if (qtyEl) qtyEl.classList.add('is-invalid');
        } else if (qtyEl) {
            qtyEl.classList.remove('is-invalid');
        }

        if (price < 0) {
            isValid = false;
            if (priceEl) priceEl.classList.add('is-invalid');
        } else if (priceEl) {
            priceEl.classList.remove('is-invalid');
        }

        if (discount < 0 || discount > 100) {
            isValid = false;
            if (discountEl) discountEl.classList.add('is-invalid');
        } else if (discountEl) {
            discountEl.classList.remove('is-invalid');
        }
    });

    const totalAmt = Number(totalAmtEl?.value || 0);
    if (totalAmt <= 0) {
        show_toastr('error', "Total amount must be greater than zero.");
        isValid = false;
    }

    if (customerType !== 'regular' && advPaymentEl && Number(advPaymentEl.value || 0) > totalAmt) {
        show_toastr('error', "Advance Payment cannot be greater than Total Amount.");
        advPaymentEl.focus();
        isValid = false;
    }

    if (gstNoEl && gstNoEl.value.trim()) {
        const gstValue = gstNoEl.value.trim().toUpperCase();
        gstNoEl.value = gstValue;
        const gstRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
        if (!gstRegex.test(gstValue)) {
            const gstErrEl = document.getElementById('error-gst_no_id');
            if (gstErrEl) gstErrEl.textContent = "Invalid GST number format.";
            show_toastr('error', "Invalid GST number format.");
            gstNoEl.focus();
            isValid = false;
        }
    }


    if (isValid) {
        this.submit();
    } else {
        const firstError = document.querySelector('[id^="error-"]:not(:empty)');
        if (firstError) {
            const fieldId = firstError.id.replace('error-', '');
            const field = document.getElementById(fieldId);
            if (field) {
                field.scrollIntoView({ behavior: 'smooth', block: 'center' });
                field.focus();
            }
        }
    }
});
</script>


<script>
document.addEventListener('DOMContentLoaded', async function ()
{
    const leadDropdown = document.getElementById('lead_id');
    const gstInput = document.getElementById('gst_no_id');
    const adharInput = document.getElementById('adhar_no_id');
    const udhyamInput = document.getElementById('udhaym_no_id');

    // If new_customer_id is present & selected
    if (leadDropdown && leadDropdown.value) {
        await loadProducts(leadDropdown);
    }

     document.querySelectorAll('.subTotal')
        .forEach(el => el.closest('tr').style.display = 'none');

    if (gstInput) {
        gstInput.addEventListener('input', function () {
            this.value = this.value.toUpperCase().replace(/[^0-9A-Z]/g, '').slice(0, 15);
        });
    }

    if (adharInput) {
        adharInput.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 12);
        });
    }

    if (udhyamInput) {
        udhyamInput.addEventListener('input', function () {
            this.value = this.value.toUpperCase().replace(/[^0-9A-Z-]/g, '').slice(0, 20);
        });
    }
});
</script>




@endsection
