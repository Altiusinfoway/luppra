{{ Form::open(['route' => 'payments.store', 'method' => 'post', 'enctype' => 'multipart/form-data', 'id' => 'paymentForm', 'autocomplete' => 'off']) }}

<div class="row">
    <div class="col-md-6">
        <label for="account_transaction_type" class="form-label">Account Transaction Type</label>
        <x-required></x-required>
        {{ Form::select(
            'account_transaction_type',
            ['' => 'Select Transaction Type'] + $account_transaction_type,
            null,
            [
                'class' => 'form-select mb-3 choices-select form-control',
                'id' => 'account_transaction_type',
                'required',
                'data-choices',
                'data-choices-removeItem',
            ],
        ) }}
        <span class="text-danger" id="error-account_transaction_type"></span>
    </div>

    <div class="col-md-6" id="bank_detail_id">
        <label for="bank_detail_id" class="form-label">Bank Account <small>( Where amount is credit /
                debit.)</small></label> <x-required></x-required>
        <select name="bank_detail_id" class="form-control" required>
            <option value="">Select Bank Account Number</option>
            @if ($bank_detail_list)
                @foreach ($bank_detail_list as $bk)
                    <option value="{{ $bk->id }}">{{ $bk->account_no }}</option>
                @endforeach
            @endif
        </select>
        <span class="text-danger" id="error-bank_detail_id"></span>
    </div>

    <div class="col-md-6 mt-2">
        <label for="entity_id" class="form-label">Select Customer/Transport/Vendor</label> <x-required></x-required>
        <select name="entity_id" id="dynamic_select" class="form-select mb-3 form-control" required>
            <option value="">Select Option</option>
        </select>
        <span class="text-danger" id="error-entity_id"></span>
    </div>

    <div class="col-md-6 mt-2">
        <label for="amount_id" class="form-label">Amount</label> <x-required></x-required>
        <input type="text" class="form-control" name="amount" value="0">
        <span class="text-danger" id="error-amount"></span>
    </div>




</div>
<div class="row">
    <div class="col-md-6 mt-2">

        <label for="payment_method" class="form-label">Payment Method</label> <x-required></x-required>
        {{ Form::select('payment_method', ['' => 'Select Method'] + $paymentMethods, null, [
            'class' => 'form-select mb-3 choices-select form-control',
            'id' => 'payment_method',
            'data-choices',
            'data-choices-removeItem',
            'onChange="showTransaction(this)"',
        ]) }}
        <span class="text-danger" id="error-payment_method"></span>
    </div>

    <div class="col-md-6 mt-2 d-none" id="transaction_list">
        <label for="transaction_id" class="form-label">Transaction ID / Cheque No</label>
        <input type="text" name="transaction_id" id="transaction_id" class="form-control"
            placeholder="Transaction ID">
        <span class="text-danger" id="error-transaction_id"></span>
    </div>

    <div class="col-md-6 mt-2">

        <label for="payment_date" class="form-label">Date</label> <x-required></x-required>
        <input type="date" name="payment_date" id="datepicker-range" class="form-control datepicker-range"
            placeholder="Payment Date" required data-provider="flatpickr" data-range="true">
        <span class="text-danger" id="error-payment_date"></span>

    </div>

    <div class="col-md-12 mt-2">
        <label for="description" class="form-label">Description</label> <x-required></x-required>
        <textarea name="description" id="description" class="form-control" placeholder="Description" required rows="3"></textarea>
        <span class="text-danger" id="error-description"></span>
    </div>

    <div class="col-md-6 mt-2">

        <div class="border mt-3 border-dashed"></div>
        <div>
            <label for="attachment" class="form-label">Attachment</label>
            <input class="form-control" type="file" id="attachment" name="attachment">
            <small class="form-text text-muted">Upload receipt or related document (optional)</small>
            <small id="imageError" class="text-danger d-block mt-1"></small>
        </div>

    </div>
</div>

<div class="row">
    <div class="mt-4">
        <div class="hstack gap-2 justify-content-end">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success" name="save" value="only_save" id="paymentAddBtn">Save</button>
        </div>
    </div>
</div>

{{ Form::close() }}

<!-- Product Modal  Ends -->

<script>
    $(document).ready(function() {
        $('#account_transaction_type').on('change', function() {
            var type = $(this).val();

            if (type) {
                $.ajax({
                    url: "{{ route('payments.getDropdownData') }}",
                    type: "GET",
                    data: {
                        type: type
                    },
                    success: function(data) {
                        var $dropdown = $('#dynamic_select');
                        $dropdown.empty();
                        $dropdown.append('<option value="">Select Option</option>');

                        $.each(data, function(id, name) {
                            $dropdown.append('<option value="' + id + '">' + name +
                                '</option>');
                        });
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            } else {
                $('#dynamic_select').empty().append('<option value="">Select Option</option>');
            }
        });


        $('#dynamic_select').on('change', function() {
            var entityId = $(this).val();

            if (entityId) {
                $.ajax({
                    url: "{{ route('payments.getDueAmount') }}",
                    type: "GET",
                    data: {
                        id: entityId
                    },
                    success: function(data) {
                        $('input[name="amount"]').val(data.due_amount ?? 0);
                    },
                    error: function() {
                        $('input[name="amount"]').val(0);
                    }
                });
            } else {
                $('input[name="amount"]').val(0);
            }
        });

    });

    function showTransaction(e) {
        const transactionId = document.getElementById('transaction_id');
        if ($(e).val() != "cash") {
            $("#transaction_list").removeClass('d-none');
            transactionId.setAttribute('required', 'required');
        } else {
            $("#transaction_list").addClass('d-none');
            transactionId.removeAttribute('required');
        }
    }

    $(document).ready(function() {
        $('#paymentForm').on('submit', function(e) {
            e.preventDefault();

            let submitBtn = $('#paymentAddBtn');
            if (submitBtn.prop('disabled')) {
                return false;
            }
            submitBtn.prop('disabled', true).text('Processing...');

            let form = $(this);
            let url = form.attr('action');
            let formData = new FormData(form[0]);

            let fileInput = $('#attachment')[0];
            if (fileInput && fileInput.files[0]) {
                formData.append('attachment', fileInput.files[0]);
            }

            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                processData: false,
                contentType: false,
                success: function(response) {

                    $("#commonModal").modal('hide');
                    show_toastr('success', response.message);
                    if (window.parent && typeof window.parent.reloadPaymentsTables === 'function') {
                        window.parent.reloadPaymentsTables();
                    } else if (typeof reloadPaymentsTables === 'function') {
                        reloadPaymentsTables();
                    } else if (typeof reloadTable === 'function') {
                        reloadTable();
                    }
                },
                error: function(xhr) {
                     submitBtn.prop('disabled', false).text('Save');
                    if (xhr.status === 422) {

                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {

                            if (document.getElementById("error-" + key)) {
                                $('#error-' + key).text(value[0]);
                            } else {
                                show_toastr('error',
                                    "Please fill all required fields.");
                            }

                        });

                    } else {
                        show_toastr('error', response.message);
                    }
                }
            });

        });

    });
</script>
