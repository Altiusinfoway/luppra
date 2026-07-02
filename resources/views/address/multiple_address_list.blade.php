@php
    use App\Models\State;
    use App\Models\City;

    $selected_country = $address_rcd->country ?? '';
    $selected_state = $address_rcd->state ?? '';
    $selected_city = $address_rcd->city ?? '';

    $country_list = \App\Models\Country::isActive()->pluck('name', 'id');
    $state_list = $selected_country ? State::where('country_id', $selected_country)->pluck('name', 'id') : [];
    $city_list = $selected_state ? City::where('state_id', $selected_state)->pluck('name', 'id') : [];

@endphp

<div class="col-md-12">
    @php
        $inputIndex = $index ?? 0;
    @endphp

    <div class="address-block-wrapper" data-index="{{ $inputIndex }}" id="shipping_block_{{ $inputIndex }}">

        @if ($inputIndex > 0)
            <hr class="divider">
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-danger btn-sm remove-address-btn mt-3">Remove</button>
            </div>
        @endif


        <input type="hidden" name="address_id[]" value="{{ $address_rcd->id ?? '' }}">

        <div class="row">
            <div class="col-md-3">
                <label>Country</label>
                <select name="country[]"
                    class="form-control form-control-sm country-input @error('country.' . $inputIndex) is-invalid @enderror">
                    <option value="">Select Country</option>
                    @foreach ($country_list as $id => $name)
                        <option value="{{ $id }}" {{ $selected_country == $id ? 'selected' : '' }}>
                            {{ $name }}</option>
                    @endforeach
                </select>

            </div>

            <div class="col-md-3">
                <label>State</label>
                <select name="state[]"
                    class="form-control form-control-sm state-input @error('state.' . $inputIndex) is-invalid @enderror">
                    <option value="">Select State</option>
                    @foreach ($state_list as $id => $name)
                        <option value="{{ $id }}" {{ $selected_state == $id ? 'selected' : '' }}>
                            {{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>City</label>
                <select name="city[]"
                    class="form-control form-control-sm city-input @error('city.' . $inputIndex) is-invalid @enderror">
                    <option value="">Select City</option>
                    @foreach ($city_list as $id => $name)
                        <option value="{{ $id }}" {{ $selected_city == $id ? 'selected' : '' }}>
                            {{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Zipcode</label>
                <input type="text" name="zipcode[]"
                    class="form-control form-control-sm zipcode-input @error('zipcode.' . $inputIndex) is-invalid @enderror"
                    value="{{ $address_rcd->zipcode ?? '' }}">
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-6">
                <label>Address Line 1</label>
                <textarea name="address_line_1[]"
                    class="form-control  address_line_1-input @error('address_line_1.' . $inputIndex) is-invalid @enderror">{{ $address_rcd->address_line_1 ?? '' }}</textarea>
            </div>

            <div class="col-md-6">
                <label>Address Line 2</label>
                <textarea name="address_line_2[]"
                    class="form-control  address_line_2-input @error('address_line_2.' . $inputIndex) is-invalid @enderror">{{ $address_rcd->address_line_2 ?? '' }}</textarea>

            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        // $('.remove-address-btn').hide();28_11

        $(document).on('change', '.country-input', function() {
            let countryID = $(this).val();
            let $block = $(this).closest('.address-block-wrapper');
            let $state = $block.find('.state-input');
            let $city = $block.find('.city-input');

            $state.html('<option value="">Loading...</option>');
            $city.html('<option value="">Select City</option>');

            $.post("{{ route('get.states') }}", {
                country_id: countryID,
                _token: '{{ csrf_token() }}'
            }, function(res) {
                $state.html('<option value="">Select State</option>');
                $.each(res.states, function(id, name) {
                    $state.append('<option value="' + id + '">' + name + '</option>');
                });
            });
        });

        $(document).on('change', '.state-input', function() {
            let stateID = $(this).val();
            let $block = $(this).closest('.address-block-wrapper');
            let $city = $block.find('.city-input');

            $city.html('<option value="">Loading...</option>');

            $.post("{{ route('get.cities') }}", {
                state_id: stateID,
                _token: '{{ csrf_token() }}'
            }, function(res) {
                $city.html('<option value="">Select City</option>');
                $.each(res.cities, function(id, name) {
                    $city.append('<option value="' + id + '">' + name + '</option>');
                });
            });
        });
        $('#is_check').change(function() {
            if ($(this).is(':checked')) {
                $('.shipping_addr_section').hide();
            } else {
                $('.shipping_addr_section').show();
            }
        });
    });
</script>
