{{ Form::open(['route' => ['payrolls.pay',$selected], 'method' => 'post',  'enctype' => "multipart/form-data", 'id'=>'paymentForm','autocomplete' => 'off']) }}

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
            <div class="hstack gap-2 justify-content-end">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success"  name="save" value="only_save">Save</button>
            </div>
        </div>
 </div>

{{ Form::close() }}

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
