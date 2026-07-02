@php
    $country_list = \App\Models\Country::isActive()->pluck('name', 'id');
    $address_id = $address_rcd ?? null;

    use App\Models\State;
    use App\Models\City;

    $selected_country = $address_rcd->country ?? '';
    $selected_state   = $address_rcd->state ?? '';
    $selected_city    = $address_rcd->city ?? '';

    $state_list = $selected_country ? State::where('country_id', $selected_country)->pluck('name', 'id') : [];
    $city_list = $selected_state ? City::where('state_id', $selected_state)->pluck('name', 'id') : [];

@endphp

<div class="col-md-12">
    <div class="">
        <div class="" id="address-rows">
            <div class="row">
                 <div class="col-md-3">
                    <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                    <select name="country" class="form-control" id="country">
                        <option value="">Select Country</option>
                        @foreach ($country_list as $id => $name)
                            <option value="{{ $id }}" {{ $selected_country == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('country'))
                        <div class="error text-danger">
                            {{ $errors->first('country') }}
                        </div>
                    @endif
                </div>

                <!-- State -->
                <div class="col-md-3">
                    <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                    <select name="state" class="form-control" id="state">
                        <option value="">Select State</option>
                        @foreach ($state_list as $id => $name)
                            <option value="{{ $id }}" {{ $selected_state == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('state'))
                        <div class="error text-danger">
                            {{ $errors->first('state') }}
                        </div>
                    @endif
                </div>

                <!-- City -->
                <div class="col-md-3">
                    <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                    <select name="city" class="form-control" id="city">
                        <option value="">Select City</option>
                        @foreach ($city_list as $id => $name)
                            <option value="{{ $id }}" {{ $selected_city == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                     @if ($errors->has('city'))
                        <div class="error text-danger">
                            {{ $errors->first('city') }}
                        </div>
                    @endif
                </div>

                <div class="col-md-3">
                    <label for="zipcode" class="form-label">Zip Code <span class="text-danger">*</span> </label>
                    <input type="number" class="form-control" name="zipcode" id="zipcode" placeholder="Enter Zipcode" value="{{ $address_rcd->zipcode ?? '' }}">
                    @if ($errors->has('zipcode'))
                        <div class="error text-danger">
                            {{ $errors->first('zipcode') }}
                        </div>
                    @endif
                </div>

            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label for="address_line_1" class="form-label">Address Line -1 <span class="text-danger">*</span> </label>
                    <textarea class="form-control" cols="5" rows="5" name="address_line_1"
                    style="height: 116px; direction: ltr; text-align: left;" id="address_line_1">{{ $address_rcd->address_line_1 ?? '' }}</textarea>
                    @if ($errors->has('address_line_1'))
                        <div class="error text-danger">
                            {{ $errors->first('address_line_1') }}
                        </div>
                    @endif
                </div>

                <div class="col-md-6">
                    <label for="address_line_2" class="form-label">Address Line -2</label>
                    <textarea class="form-control" cols="5" rows="5" name="address_line_2"
                    style="height: 116px; direction: ltr; text-align: left;" id="address_line_2">{{ $address_rcd->address_line_2 ?? '' }}</textarea>
                    @if ($errors->has('address_line_2'))
                        <div class="error text-danger">
                            {{ $errors->first('address_line_2') }}
                        </div>
                    @endif
                </div>

            </div>


        </div>
    </div>
</div>

<script>

    $(document).ready(function () {
        $('#country').on('change', function () {
            let countryID = $(this).val();
            $('#state').html('<option value="">Loading...</option>');
            $('#city').html('<option value="">Select City</option>');

            if (countryID) {
                $.ajax({
                    url: "{{ route('get.states') }}",
                    type: "POST",
                    data: {
                        country_id: countryID,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        $('#state').html('<option value="">Select State</option>');
                        $.each(res.states, function (id, name) {
                            $('#state').append('<option value="' + id + '">' + name + '</option>');
                        });
                    }
                });
            }
        });

        $('#state').on('change', function () {
            let stateID = $(this).val();
            $('#city').html('<option value="">Loading...</option>');

            if (stateID) {
                $.ajax({
                    url: "{{ route('get.cities') }}",
                    type: "POST",
                    data: {
                        state_id: stateID,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        $('#city').html('<option value="">Select City</option>');
                        $.each(res.cities, function (id, name) {
                            $('#city').append('<option value="' + id + '">' + name + '</option>');
                        });
                    }
                });
            }
        });
    });


</script>

