<style>
    .payroll-payment-suite .summary-card {
        border: 1px solid rgba(255, 255, 255, 0.82);
        border-radius: 20px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
    }

    .payroll-payment-suite .summary-card .label {
        display: block;
        margin-bottom: 8px;
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .payroll-payment-suite .summary-card h3 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #0f172a;
    }

    .payroll-payment-suite .form-shell {
        border: 1px solid #e2e8f0;
        border-radius: 22px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.06);
        padding: 20px;
    }

    .payroll-payment-suite .section-intro {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #f8fafc;
        padding: 14px 16px;
        margin-bottom: 18px;
    }

    .payroll-payment-suite .section-intro h6 {
        margin-bottom: 6px;
        font-weight: 800;
        color: #0f172a;
    }

    .payroll-payment-suite .section-card {
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        background: #ffffff;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        padding: 18px;
    }

    .payroll-payment-suite .form-actions {
        margin-top: 8px;
        padding-top: 18px;
        border-top: 1px solid #e2e8f0;
    }
</style>

<div class="payroll-payment-suite">
{{ Form::open(['route' => ['payrolls.pay',$selected], 'method' => 'post',  'enctype' => "multipart/form-data", 'id'=>'paymentForm','autocomplete' => 'off']) }}

<div class="form-shell">
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card summary-card h-100">
                <div class="card-body">
                    <span class="label">Workflow</span>
                    <h3>Batch Payment</h3>
                    <p class="text-muted mb-0 mt-2">Confirm the source account, payment method, and reference details before payroll is processed.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card summary-card h-100">
                <div class="card-body">
                    <span class="label">Funding</span>
                    <h3>Bank Linked</h3>
                    <p class="text-muted mb-0 mt-2">Choose the correct operational account so the salary transaction trail stays clear.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card summary-card h-100">
                <div class="card-body">
                    <span class="label">Proof</span>
                    <h3>Receipt Ready</h3>
                    <p class="text-muted mb-0 mt-2">Attach an optional receipt or payment proof for cleaner payroll history and audits.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="section-intro">
            <h6>Payroll payment details</h6>
            <p class="text-muted mb-0">Select the funding account, payment method, date, and supporting notes before processing the selected salary rows.</p>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="account_transaction_type" class="form-label">Account Transaction Type</label> <x-required></x-required>
                {{ Form::select('account_transaction_type', ['' => 'Select Transaction Type']+$account_transaction_type, 'debit', [
                    'class' => 'form-select mb-3 choices-select form-control',
                    'id' => 'account_transaction_type',
                    'required',
                    'data-choices',
                    'data-choices-removeItem',
                ]) }}
                <span class="text-danger" id="error-account_transaction_type"></span>
            </div>

            <div class="col-md-8" id="bank_detail_id">
                <label for="bank_detail_id" class="form-label">Bank Account <small>( Where amount is credit / debit.)</small></label> <x-required></x-required>
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
        </div>

        <div class="row">
            <div class="col-md-6 mt-2">
                <label for="payment_method" class="form-label">Payment Method</label> <x-required></x-required>
                {{ Form::select('payment_method',  ['' => 'Select Method'] +$paymentMethods, null, [
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
                <input type="text" name="transaction_id" id="transaction_id" class="form-control" placeholder="Transaction ID">
                <span class="text-danger" id="error-transaction_id"></span>
            </div>

            <div class="col-md-6 mt-2">
                <label for="payment_date" class="form-label">Date</label> <x-required></x-required>
                <input type="date" name="payment_date" id="datepicker-range" class="form-control datepicker-range" placeholder="Payment Date" required data-provider="flatpickr" data-range="true">
                <span class="text-danger" id="error-payment_date"></span>
            </div>

            <div class="col-md-12 mt-2">
                <label for="description" class="form-label">Description</label> <x-required></x-required>
                <textarea name="description" id="description" class="form-control" placeholder="Description" required rows="3">{{ 'Payroll payments ' }}</textarea>
                <span class="text-danger" id="error-description"></span>
            </div>

            <div class="col-md-6 mt-2">
                <div class="border mt-3 border-dashed"></div>
                <div>
                    <label for="attachment" class="form-label">Attachment</label>
                    <input class="form-control" type="file" id="attachment" name="attachment">
                    <small class="form-text text-muted">Upload receipt or related document (optional)</small>
                    <input type="hidden" name="selected" value="{{$selected}}">
                    <small id="imageError" class="text-danger d-block mt-1"></small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="mt-4">
                <div class="hstack gap-2 justify-content-end form-actions">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="payrollPaymentSubmit" name="save" value="only_save">Save Payment</button>
                </div>
            </div>
        </div>
    </div>

</div>
{{ Form::close() }}
</div>

<!-- Product Modal  Ends -->

<script>

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

        const submitButton = document.getElementById('payrollPaymentSubmit');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerText = 'Saving...';
        }

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

                window.location.href = '{{ route("payrolls.process") }}';

            },
            error: function(xhr) {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerText = 'Save Payment';
                }

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
                    show_toastr('error','Unable to process payroll payment right now.');
                }
            }
        });

    });

});

</script>
