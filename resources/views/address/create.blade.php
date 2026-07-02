<form method="post" action="{{ route('addresses.store', [$type, $company_id,$id]) }}" id="addressStore{{time()}}">
     @csrf
    <div class="row">
        <div class="col-12">

            <h6 class="text-muted text-uppercase mb-3">
                Address
            </h6>

            {{-- Country & State --}}
            <div class="row g-3">
                <div class="col-12 col-md-6 ">
                    <label for="country" class="form-label">
                        Country <span class="text-danger">*</span>
                    </label>

                    {{ Form::select(
                        'country',
                        ['' => 'Select Country'] + $countries->toArray(),
                        old('country', $address_rcd?->country),
                        ['class' => 'form-select form-select-sm choices-select', 'id' => 'country', 'onChange' => 'loadShippingState(this)'],
                    ) }}

                    <span class="text-danger small" id="error-country"></span>
                </div>

                <div class="col-12 col-md-6">
                    <label for="state" class="form-label ">
                        State <span class="text-danger">*</span>
                    </label>
                    {{ Form::select(
                        'state',
                        ['' => 'Select State'] + $states->toArray(),
                        old('state', $address_rcd?->state ?? ''),
                        ['class' => 'form-select form-select-sm choices-select', 'id' => 'state',
                        'data-choices',
                        'data-choices-removeItem',
                        'onChange' => 'loadShippingCity(this)'],
                    ) }}

                    <span class="text-danger small" id="error-state"></span>
                </div>

                <div class="col-12 col-md-6">
                    <label for="city" class="form-label">
                        City
                    </label>
                    {{ Form::select(
                        'city',
                        ['' => 'Select City'],
                        old('city', $address_rcd?->city ?? ''),
                        ['class' => 'form-select form-select-sm choices-select', 'id' => 'city'],
                    ) }}
                    <span class="text-danger small" id="error-city"></span>
                </div>

                <div class="col-12 col-md-6">
                    <label for="zipcode" class="form-label">
                        Zip Code
                    </label>
                    <input type="text" name="zipcode" id="zipcode"
                        class="form-control bg-light border-0" placeholder="Enter Zip Code"
                        value="{{ old('zipcode', $address_rcd->zipcode ?? '') }}">
                    <span class="text-danger small" id="error-zipcode"></span>
                </div>
            </div>

            {{-- City & Zip --}}
            <div class="row g-3">

            </div>
            <div class="mb-1">
                <label for="address_line_1" class="form-label ">
                    Address Line 1
                </label>
                <textarea name="address_line_1" id="address_line_1" class="form-control form-control-sm bg-light border-0"
                    rows="3" placeholder="Address Line One">{{ old('address_line_1', $address_rcd->address_line_1 ?? '') }}</textarea>
                <span class="text-danger small" id="error-address_line_1"></span>
            </div>
            <div class="mb-1">
                <label for="address_line_2" class="form-label ">
                    Address Line 2
                </label>
                <textarea name="address_line_2" id="address_line_2" class="form-control form-control-sm bg-light border-0"
                    rows="3" placeholder="Address Line Two">{{ old('address_line_2', $address_rcd->address_line_2 ?? '') }}</textarea>
            </div>

            {{-- Submit --}}
            <div class="text-end mt-2">
                <button type="submit" class="btn btn-success btn-sm px-4">
                    Save Address
                </button>
            </div>
        </div>
    </div>
</form>

<script>
    let shippingCountryChoices;
    let shippingStateChoices;
    let shippingCityChoices;

    function loadState(e, stateId = null, cb = null){

        postAjax("{{ route('get.states') }}", {
            country_id: $(e).val()
        }, function(res) {

            if (typeof cb === "function") {
                cb(res);
            }

        });

    }

    function loadCity(e, cityId = null, cb = null){

        postAjax("{{ route('get.cities') }}", {
            state_id: $(e).val()
        }, function(res) {

            if (typeof cb === "function") {
                cb(res);
            }

        });

    }

    function loadShippingState(e, stateId = null, cb = null){

        console.log("function B");


        loadState(e, stateId, function(res){

            console.log("function C");

            const states = Object.entries(res.states).map(([id, name]) => ({
                value: id,
                label: name,
            }));

            console.log(shippingStateChoices);

            shippingStateChoices.clearStore();
            shippingStateChoices.setChoices(states, 'value', 'label', true);
            shippingStateChoices.setChoiceByValue(String(stateId));

            shippingCityChoices.clearStore();
            if (typeof cb === "function") {
                cb(true);
            }

        })

    }

    function loadShippingCity(e, cityId = null){

        loadCity(e, cityId, function(res){

           /* old code
           const cities = Object.entries(res.cities).map(([id, name]) => ({
                value: id,
                label: name,
            }));

            shippingCityChoices.clearStore();
            shippingCityChoices.setChoices(cities, 'value', 'label', true);
            shippingCityChoices.setChoiceByValue(String(cityId));
            */

            const cities = [
            { value: '', label: 'Select City', disabled: true, selected: true },
                ...Object.entries(res.cities).map(([id, name]) => ({
                    value: id,
                    label: name,
                }))
            ];

            shippingCityChoices.clearStore();
            shippingCityChoices.setChoices(cities, 'value', 'label', false);

            if (cityId) {
                shippingCityChoices.setChoiceByValue(String(cityId));
            }

        })


    }



    $(document).ready(function() {

       const shippingCountry = document.getElementById('country');
        shippingCountryChoices = new Choices(shippingCountry, {
            placeholder: true,
            placeholderValue: 'Country',
            searchEnabled: true,
            shouldSort: false,
        });

        const shippingState = document.getElementById('state');
        shippingStateChoices = new Choices(shippingState, {
            placeholder: true,
            placeholderValue: 'State',
            searchEnabled: true,
            shouldSort: false,
        });

        const shippingCity = document.getElementById('city');
        shippingCityChoices = new Choices(shippingCity, {
            placeholder: true,
            placeholderValue: 'City',
            searchEnabled: true,
            shouldSort: false,
        });


        // Load state and city based on address.

        @if (isset($address_rcd) && $address_rcd)

            loadShippingState(shippingCountry, '{{ $address_rcd?->state }}', function (res){

               loadShippingCity(shippingState, '{{ $address_rcd?->city }}');

            });

            $("#address_line_1").val('{{ $address_rcd?->address_line_1 }}');
            $("#address_line_2").val('{{ $address_rcd?->address_line_2 }}');
            $("#zipcode").val('{{ $address_rcd?->zipcode }}');

        @endif

        // Ends.

    });
</script>

<script>
    async function reloadAddresses(company_id) {

        if (!company_id) {
            console.error('Company ID is missing');
            return;
        }

        console.log('Reloading addresses for company:', company_id);

        const address_url = "{{ route('addresses.fetch', ':company_id') }}";
        document.querySelector('.address-section').innerHTML = '';

        return new Promise((resolve) => {

            getAjax(address_url.replace(':company_id', company_id), async  function(res) {

                document.querySelector('.address-section').innerHTML = res;

                if (typeof initAddressCardEvents === 'function') {
                    initAddressCardEvents();
                }

                //when update address then latest gst value get



    if (typeof initAddressCardEvents === 'function') {
        initAddressCardEvents();
    }

    const leadEl = document.getElementById('lead_id');
    if (!leadEl?.value) return;

    const addressCheck = await isCustomerAddressAvailable(leadEl.value);
    if (!addressCheck || !addressCheck.tax_data) return;

    const taxData = addressCheck.tax_data;

    const taxTypeRowEl = document.getElementById("tax-type-row");
    const taxJsonInputEl = document.querySelector('input[name="tax_json_data"]');
    const taxRateSumDisplayEl = document.querySelector('.tax_rate_sum');
    const taxRateSumInputEl = document.querySelector('input[name="tax_rate_sum"]');

    // Remove old tax rows
    while (
        taxTypeRowEl.nextElementSibling &&
        taxTypeRowEl.nextElementSibling.dataset.taxRow === "1"
    ) {
        taxTypeRowEl.parentNode.removeChild(taxTypeRowEl.nextElementSibling);
    }

    let taxJsonResult = {};
    let taxRateTotal = 0;

    for (const [key, value] of Object.entries(taxData)) {
        if (key !== 'tax_type' && value) {

            const tr = document.createElement('tr');
            tr.setAttribute("data-tax-row", "1");
            tr.innerHTML = `
                <td colspan="6"></td>
                <td><strong>${key}</strong></td>
                <td class="text-end">${value}%</td>
            `;

            taxTypeRowEl.parentNode.insertBefore(tr, taxTypeRowEl.nextSibling);

            taxJsonResult[key] = value;
            taxRateTotal += parseFloat(value) || 0;
        }
    }

    taxJsonInputEl.value = JSON.stringify(taxJsonResult);
    taxRateSumDisplayEl.textContent = taxRateTotal + '%';
    taxRateSumInputEl.value = taxRateTotal.toFixed(2);

    updateTotals();
                //--------------

                resolve(true);
            });
        });
    }
</script>

<script>

    $(document).on('submit', '#addressStore{{time()}}', function(e) {

        console.log('start');
        e.preventDefault();

        $('[id^="error-"]').text('');

        let formData ='';
        let form = $(this);
        let url = form.attr('action');

         formData = {
            country: $('#country').val(),
            state: $('#state').val(),
            city: $('#city').val(),
            zipcode: $('input[name="zipcode"]').val(),
            address_line_1: $('textarea[name="address_line_1"]').val(),
            address_line_2: $('textarea[name="address_line_2"]').val(),
        };

        postAjax(url, formData, async function(res) {
            if (res.success) {
                console.log(res)
                show_toastr('success', res.message);
                 btn.disabled = false;

                $('#commonModal').modal('hide');

                // address data latest fetch
                await reloadAddresses(res.company_id);

                //---------------- update address according gst dropdown show
                // If new_customer_id is present & selected

                const leadDropdown = document.getElementById('lead_id');

                // If new_customer_id is present & selected
                if (leadDropdown && leadDropdown.value) {
                    await loadProducts(leadDropdown);
                }
                //------------------ end ------------------------------

            } else {
                 btn.disabled = true;
                show_toastr('error', res.message);
            }

        });
    });
</script>





