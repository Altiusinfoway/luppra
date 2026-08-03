<form action="{{ route('transports.store') }}" method="POST" id="main-form">
    @csrf
    <div class="row g-3">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="section-card h-100">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" id="name"
                            placeholder="Enter name" value="{{ old('name') }}">
                        <span class="text-danger" id="error-name"></span>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control form-control-sm" id="email"
                            placeholder="Enter Email" value="{{ old('email') }}">
                        <span class="text-danger" id="error-email"></span>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="company_name">Company Name <span
                                class="text-danger">*</span></label>
                        <input type="text" name="company_name" class="form-control form-control-sm" id="company_name"
                            placeholder="Enter Company Name" value="{{ old('company_name') }}">

                        <span class="text-danger" id="error-company_name"></span>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="gst_no">GST No </label>
                        <input type="text" name="gst_no" class="form-control form-control-sm"
                            id="manufacturer-name-input" placeholder="Enter GST No." value="{{ old('gst_no') }}">
                        <span class="text-danger" id="error-gst_no"></span>
                    </div>
                    <div class="col-md-12">
                        <label for="specification" class="form-label">Specification <span
                                class="text-danger">*</span></label>
                        <textarea class="form-control" name="specification" id="specification" rows="3" placeholder="Enter Specification">{{ old('specification') }}</textarea>
                        {{-- @if ($errors->has('specification'))
                        <div class="error text-danger">{{ $errors->first('specification') }}
                        </div>
                    @endif --}}
                        <span class="text-danger" id="error-specification"></span>
                    </div>
                </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="section-card h-100">
                <label class="form-label mb-2">Contact Numbers<span class="text-danger">*</span></label>
                <div id="contact-wrapper">
                    @php $oldContacts = old('contact', ['']); @endphp

                    @foreach ($oldContacts as $index => $contact)
                        <div class="row mb-2 contact-row">
                            <div class="col-md-8">
                                <input type="number" name="contact[]"
                                    class="form-control form-control-sm contact-input" placeholder="Enter Contact"
                                    value="{{ $contact }}" required>
                                <span class="text-danger ajax-error-contact d-block"></span>
                            </div>
                            <div class="col-md-4">
                                @if ($loop->first)
                                    <button type="button" class="btn btn-sm btn-primary w-100" id="add-contact-btn">Add
                                        Contact</button>
                                @else
                                    <button type="button"
                                        class="btn btn-sm btn-danger ms-2 remove-contact-btn w-100">Remove</button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                {{-- <div class="mt-3 mb-3">
                    <label for="" class="form-label">Status <span class="text-danger">*</span></label>
                    <div>
                        <input class="form-check-input" class="form-check" type="radio" name="is_active"
                            id="active" value="1" checked>
                        <label class="form-check-label" for="flexRadioDefault1">
                            Active
                        </label>
                        &nbsp;

                        <input class="form-check-input" class="form-check" type="radio" name="is_active"
                            value="0" id="inactive">
                        <label class="form-check-label" for="flexRadioDefault2">
                            In Active
                        </label>

                    </div>
                </div> --}}
                </div>
            </div>
        </div>




        <hr class="divider">
        <div class="col-lg-12">
            <div class="section-card">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title  mb-0">Address Details<span class="text-danger">*</span></h5>
                <button type="button" class="btn btn-sm btn-primary" id="add-address-btn">+ Add Address</button>
            </div>

            <div id="address-container">
                <div class="address-block">
                    @include('address.multiple_address_list')
                    <hr class="divider">
                </div>
            </div>
            </div>
        </div>
    </div>


    <div class="text-center mt-3">
        <button type="submit" class="btn btn-sm btn-primary w-sm" id="transportAddBtn">Submit</button>
    </div>

</form>
<!-- end card body -->


<script>
    //add more contact
    document.addEventListener("DOMContentLoaded", function() {
        const contactWrapper = document.getElementById("contact-wrapper");
        const addBtn = document.getElementById("add-contact-btn");

        addBtn.addEventListener("click", function() {
            const newRow = document.createElement("div");
            newRow.classList.add("row", "mb-2", "contact-row");

            newRow.innerHTML = `
                <div class="col-md-8">
                    <input type="number" name="contact[]" class="form-control form-control-sm contact-input" placeholder="Enter Contact" required>
                    <span class="text-danger ajax-error-contact d-block"></span>
                </div>
                <div class="col-md-4 d-flex">
                    <button type="button" class="btn btn-sm btn-danger ms-2 remove-contact-btn w-100">Remove</button>
                </div>
            `;

            contactWrapper.appendChild(newRow);
        });

        // Remove contact row
        contactWrapper.addEventListener("click", function(e) {
            if (e.target && e.target.classList.contains("remove-contact-btn")) {
                e.target.closest(".contact-row").remove();
            }
        });
    });
</script>


<script>
    $(document).ready(function() {
        $('#add-address-btn').on('click', function() {
            let $original = $('#address-container .address-block').first();
            let $clone = $original.clone();

            // Reset values
            $clone.find('input, textarea, select').val('');
            $clone.find('.text-danger').remove();
            $clone.find('.is-invalid').removeClass('is-invalid');
            $clone.find('.remove-address-btn').remove();

            $clone.prepend(`
            <div class="d-flex justify-content-end mt-3 ">
                <button type="button" class="btn btn-sm btn-danger remove-address-btn">Remove</button>
            </div>
        `);

            $('#address-container').append($clone);
        });

        $(document).on('click', '.remove-address-btn', function() {
            $(this).closest('.address-block').remove();
        });



        $('#main-form').on('submit', function(e) {
            e.preventDefault();

            let submitBtn = $('#transportAddBtn');
            if (submitBtn.prop('disabled')) {
                return false;
            }
            submitBtn.prop('disabled', true).text('Processing...');

            let form = $(this);
            let url = form.attr('action');
            let formData = form.serialize();


            $('.is-invalid').removeClass('is-invalid');
            $('.text-danger').text('');

            $.post(url, formData, function(res) {
                if (res.success) {
                    show_toastr('success', res.success);
                    setTimeout(() => window.location.href = res.redirect_url, 1500);
                }

                 if (res.error) {
                    submitBtn.prop('disabled', false).text('Submit');
                    show_toastr('error', res.message);
                }
            }).fail(function(xhr) {
                if (xhr.status === 422) {
                    submitBtn.prop('disabled', false).text('Submit');
                    let errors = xhr.responseJSON.errors;

                    $.each(errors, function(key, messages) {
                        if (key.startsWith('contact.')) {
                            let index = key.split('.')[1];

                            let $input = $('.contact-input').eq(index);
                            let $errorBox = $input.siblings('.ajax-error-contact');

                            $input.addClass('is-invalid');
                            $errorBox.text(messages[0]);
                            return; // stop here for this key
                        }

                        // 2️⃣ Address errors next
                        if (key.includes('.')) {
                            let [field, index] = key.split('.');

                            let $block = $('.address-block').eq(index);
                            let $input = $block.find(`.${field}-input`);
                            let $errorBox = $block.find(`.ajax-error-${field}`);

                            $input.addClass('is-invalid');
                            messages.forEach(msg => {
                                $errorBox.append(`<div>${msg}</div>`);
                            });
                            return;
                        }

                        if ($('#error-' + key).length) {
                            $('#error-' + key).text(messages[0]);
                        } else {
                            show_toastr('error', messages[0]);
                        }

                    });
                } else {
                    show_toastr('error', "Something went wrong.");
                }
            });
        });

    });
</script>
