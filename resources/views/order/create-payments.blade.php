<style>
    .payment-modal-form .section-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px;
        background: #f8fafc;
    }
    .payment-modal-form .section-title {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .4px;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 10px;
    }
    .payment-modal-form .form-label {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
    }
    .payment-modal-form .helper-note {
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 10px 12px;
        background: linear-gradient(135deg, #eff6ff 0%, #f8fbff 100%);
        color: #1e3a8a;
        font-size: 12px;
    }
</style>

{{ Form::open(['route' => ['orders.collect-payment',$order->id], 'method' => 'post',  'enctype' => "multipart/form-data", 'id'=>'paymentForm', 'class' => 'payment-modal-form', 'autocomplete' => 'off']) }}

<div class="row g-3">
    <div class="col-12">
        <div class="helper-note">
            Collect the remaining order payment, map it to the right account, and keep receipt details attached in one clean finance action.
        </div>
    </div>

    <div class="col-12">
        <div class="section-card">
            <div class="section-title">Transaction Details</div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="account_transaction_type" class="form-label">Account Transaction Type</label> <x-required></x-required>
                    {{ Form::select('account_transaction_type', ['' => 'Select Transaction Type']+$account_transaction_type, 'credit', [
                        'class' => 'form-select choices-select form-control',
                        'id' => 'account_transaction_type',
                        'required',
                        'data-choices',
                        'data-choices-removeItem',
                    ]) }}
                    <span class="text-danger" id="error-account_transaction_type"></span>
                </div>

                <div class="col-md-8" id="bank_detail_id">
                    <label for="bank_detail_id" class="form-label">Bank Account <small>(Where amount is credit / debit)</small></label> <x-required></x-required>
                    <select name="bank_detail_id" class="form-control" required>
                        <option value="">Select Bank Account Number</option>
                        @if($bank_detail_list)
                        @foreach ($bank_detail_list as $bk)
                            <option value="{{ $bk->id }}">{{ $bk->account_no }}</option>
                        @endforeach
                        @endif
                    </select>
                    <span class="text-danger" id="error-bank_detail_id"></span>
                </div>

                <div class="col-md-6">
                    <label for="entity_id" class="form-label">Select Customer</label> <x-required></x-required>
                    <select name="entity_id" id="dynamic_select" class="form-select form-control" required>
                        <option value="">Select Option</option>
                        @if($customer)
                        @foreach ($customer as $k => $v)
                            <option value="{{ $v->id }}" {{ $order->customer_id == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                        @endforeach
                        @endif
                    </select>
                    <span class="text-danger" id="error-entity_id"></span>
                </div>

                <div class="col-md-6">
                    <label for="amount_id" class="form-label">Amount</label> <x-required></x-required>
                    <input type="text" class="form-control" name="amount" value="{{ round($order->remaining_payment,2)}}" data-order-remaining="{{ round($order->remaining_payment,2) }}">
                    <span class="text-danger" id="error-amount"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="section-card">
            <div class="section-title">Payment Capture</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="payment_method" class="form-label">Payment Method</label> <x-required></x-required>
                    {{ Form::select('payment_method',  ['' => 'Select Method'] +$paymentMethods, null, [
                        'class' => 'form-select choices-select form-control',
                        'id' => 'payment_method',
                        'data-choices',
                        'data-choices-removeItem',
                        'onChange="showTransaction(this)"',
                    ]) }}
                    <span class="text-danger" id="error-payment_method"></span>
                </div>

                <div class="col-md-6 d-none" id="transaction_list">
                    <label for="transaction_id" class="form-label">Transaction ID / Cheque No</label>
                    <input type="text" name="transaction_id" id="transaction_id" class="form-control" placeholder="Transaction ID">
                    <span class="text-danger" id="error-transaction_id"></span>
                </div>

                <div class="col-md-6">
                    <label for="payment_date" class="form-label">Date</label> <x-required></x-required>
                    <input type="date" name="payment_date" id="datepicker-range" class="form-control datepicker-range" placeholder="Payment Date" required data-provider="flatpickr" data-range="true">
                    <span class="text-danger" id="error-payment_date"></span>
                </div>

                <div class="col-md-12">
                    <label for="description" class="form-label">Description</label> <x-required></x-required>
                    <textarea name="description" id="description" class="form-control" placeholder="Description" required rows="3">{{ 'Order payment ' . $order->order_number }}</textarea>
                    <span class="text-danger" id="error-description"></span>
                </div>

                <div class="col-md-6">
                    <label for="attachment" class="form-label">Attachment</label>
                    <input class="form-control" type="file" id="attachment" name="attachment">
                    <small class="form-text text-muted">Upload receipt or related document (optional)</small>
                    <small id="imageError" class="text-danger d-block mt-1"></small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 mt-2">
        <div class="hstack gap-2 justify-content-end">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success"  name="save" value="only_save">Save</button>
        </div>
    </div>
</div>

{{ Form::close() }}

<!-- Product Modal  Ends -->

<script>
$(document).ready(function () {

    $('#account_transaction_type').on('change', function () {
        var type = $(this).val();

        if (type) {
            $.ajax({
                url: "{{ route('payments.getDropdownData') }}",
                type: "GET",
                data: { type: type },
                success: function (data) {
                    var $dropdown = $('#dynamic_select');
                    $dropdown.empty();
                    $dropdown.append('<option value="">Select Option</option>');

                    $.each(data, function (id, name) {
                        $dropdown.append('<option value="' + id + '">' + name + '</option>');
                    });
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                }
            });
        } else {
            $('#dynamic_select').empty().append('<option value="">Select Option</option>');
        }
    });


    const amountInput = $('input[name="amount"]');
    const orderRemaining = parseFloat(amountInput.data('order-remaining')) || 0;

    // For invoice collection, always default to this order's remaining amount.
    $('#dynamic_select').on('change', function () {
        amountInput.val(orderRemaining.toFixed(2));
    });

    amountInput.on('input', function () {
        const val = parseFloat($(this).val());
        if (!isNaN(val) && val > orderRemaining) {
            $(this).val(orderRemaining.toFixed(2));
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

$(document).ready(function () {
    $('#paymentForm').on('submit', function (e) {
        e.preventDefault();

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

                show_toastr('success',response.message);
                {{--
                /* var url = "{{ route('production-purchase-raw.showPo',':id') }}";
                url = url.replace(':id', {{ $order->id }});

                let link = $('<a>', {
                    href: 'javascript:void(0);',
                    'data-size':"xl",
                    'data-url':url,
                    'data-ajax-popup':"true",
                    'data-bs-original-title':"{{__('View Purchase Order')}}",
                }).appendTo('body');

                link[0].click();
                link.remove(); */
                --}}

            },
            error: function(xhr) {

                if (xhr.status === 422) {

                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function (key, value) {

                        if(document.getElementById("error-" + key)) {
                            $('#error-' + key).text(value[0]);
                        } else {
                            show_toastr('error',"Please fill all required fields.");
                        }

                    });

                } else {
                    show_toastr('error',response.message);
                }
            }
        });

    });

});

</script>
