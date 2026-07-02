<form action="{{ route('transports.quick_store') }}" method="POST" id="transport-quick-form">
    @csrf
    <div class="row">
        <div class="col-md-12">

                <div class="mb-3">
                    <label class="form-label" for="name">Name <span
                            class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" id="name"
                        placeholder="Enter name"
                        value="{{ old('name') }}">
                    <span class="text-danger" id="error-name"></span>
                </div>
                <div class="row mt-3">

                    <div class=" ">
                        <label class="form-label mb-2">Contact Numbers<span
                            class="text-danger">*</span></label>

                        <div id="contact-wrapper">

                            <div class="row mb-2 contact-row">
                                <div class="col-md-6">
                                    <input type="number" name="contact[]" class="form-control contact-input"
                                        placeholder="Enter Contact" value="" required>
                                    <span class="text-danger ajax-error-contact d-block"></span>
                                </div>

                                <div class="col-md-6 d-flex">
                                    <button type="button" class="btn btn-sm btn-primary ms-2" id="add-contact-btn"><i class="ri-add-line align-bottom me-1"></i> Add More</button>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>
        </div>
    </div>

    <div class="text-center mt-3">
        <button type="submit" class="btn btn-success w-sm">Submit</button>
    </div>

</form>
<!-- end card body -->


<script>

    //add more contact
    document.addEventListener("click", function(e) {
        // Add contact
        if (e.target && e.target.id === "add-contact-btn") {
            const contactWrapper = document.getElementById("contact-wrapper");

            const newRow = document.createElement("div");
            newRow.classList.add("row", "mb-2", "contact-row");

            newRow.innerHTML = `
                <div class="col-md-6">
                    <input type="number" name="contact[]" class="form-control contact-input" placeholder="Enter Contact" required>
                    <span class="text-danger ajax-error-contact d-block"></span>
                </div>
                <div class="col-md-6 d-flex">
                    <button type="button" class="btn btn-sm btn-danger ms-2 remove-contact-btn"><i class="ri-delete-bin-5-line align-bottom me-1"></i></button>
                </div>
            `;

            contactWrapper.appendChild(newRow);
        }

        // Remove contact
        if (e.target && e.target.classList.contains("remove-contact-btn")) {
            e.target.closest(".contact-row").remove();
        }
    });


</script>


<script>
$(document).ready(function () {


    $('#transport-quick-form').on('submit', function (e) {
        e.preventDefault();

        let form = $(this);
        let url = form.attr('action');
        let formData = form.serialize();


        $('.is-invalid').removeClass('is-invalid');
        $('.text-danger').text('');

        $.post(url, formData, function (res) {
            if (res.success) {
                show_toastr('success',res.success);

                window.location.reload();

                // setTimeout(() => window.location.href = res.redirect_url, 1500);
            }
        }).fail(function (xhr) {

            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;

                $.each(errors, function (key, messages)
                {
                    if (key.startsWith('contact')) {
                        let $inputs = $('.contact-input');
                        $.each(messages, function (i, msg) {
                            let $input = $inputs.eq(i);
                            $input.addClass('is-invalid');
                            $input.siblings('.ajax-error-contact').text(msg);
                        });
                    } else if ($('#error-' + key).length) {
                        $('#error-' + key).text(messages[0]);
                    } else {
                        show_toastr('error', "Please check all fields.");
                    }
                });
            } else {
                show_toastr('error', "Something went wrong.");
            }

        });
    });

});
</script>
