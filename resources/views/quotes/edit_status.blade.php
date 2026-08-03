@extends('layouts.app')

@section('page-css')
<style>
    .quote-status-suite {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
    }

    .quote-status-suite .hero-shell,
    .quote-status-suite .form-shell {
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
    }

    .quote-status-suite .hero-shell {
        background:
            radial-gradient(circle at top right, rgba(249, 115, 22, 0.14), transparent 30%),
            radial-gradient(circle at left center, rgba(59, 130, 246, 0.14), transparent 30%),
            linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }

    .quote-status-suite .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        padding: 7px 12px;
        border-radius: 999px;
        border: 1px solid #fdba74;
        background: rgba(255, 255, 255, 0.86);
        color: #c2410c;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .checkbox-xl .form-check-input {
        width: 24px;
        height: 24px;
    }

    .cursor-pointer {
        cursor: pointer;
    }
</style>
@endsection

@section('content')
<div class="page-content quote-status-suite">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="card hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <span class="hero-eyebrow">Quote Conversion</span>
                                <h2 class="mt-3 mb-2">Quotes Section</h2>
                                <p class="text-muted mb-0">Update customer, billing, shipping, and payment status details from a cleaner quote-finalization form shell.</p>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('quotes.index') }}">Quotes</a></li>
                                        <li class="breadcrumb-item active">Edit</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card form-shell">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title mb-0">Quote Status</h5>
                        </div>
                    </div>
                    <div class="card-body">

                        <form action="{{ route('quotes.status_update',$quote_id) }}" method="POST" id="quoteStatusForm" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-lg-12">

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label" for="name">Customer Name </label>
                                                <input type="text" name="name" class="form-control" id="name" placeholder="Enter Name" value="{{ $customer_data->name ?? '' }}" readonly>
                                                <span class="text-danger" id="error-name"></span>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label" for="comp_name">Company Name </label>
                                                <input type="text" name="comp_name" class="form-control" id="comp_name" placeholder="Enter Company Name" value="{{ $customer_data->company_name ?? '' }}" readonly>
                                                <span class="text-danger" id="error-comp_name"></span>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label" for="name">Email </label>
                                                <input type="text" name="email" class="form-control" id="email" placeholder="Enter Email" value="{{ $customer_data->email ?? '' }}" >
                                                <span class="text-danger" id="error-email"></span>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label" for="gst_no">GST No </label>
                                                <input type="text" name="gst_no" class="form-control" id="gst_no" placeholder="Enter GST No" value="{{ $customer_data->gst_no ?? '' }}" >
                                                <span class="text-danger" id="error-gst_no"></span>
                                            </div>
                                        </div>
                                        {{-- <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label" for="avatar">Avatar <span class="text-danger">*</span></label>
                                                <input type="file" name="avatar" class="form-control" >
                                                <span class="text-danger" id="error-avatar"></span>
                                            </div>
                                        </div> --}}
                                    </div>



                                    <hr>
                                        <div class="text-center mb-2">
                                            <h3 class="mt-2">Billing Information</h3>
                                        </div>
                                        <div class="row">
                                            @foreach ($address_list as $index => $address_rcd)

                                                @if ($index == 1)
                                                    <h4 class="mt-3 mb-3">Shipping Address</h4>

                                                    <div class="form-check d-flex align-items-center checkbox-xl mb-3">
                                                        <label class="d-flex align-items-center cursor-pointer mb-0">
                                                            <input type="checkbox" name="is_check" class="form-check-input me-2 " id="is_check" value="1">
                                                            <h5 class="mb-0">Same As Above</h5>
                                                        </label>
                                                    </div>

                                                    <div class="shipping_addr_section">
                                                @endif

                                                {{-- <input type="hidden" class="form-control" value="{{ $address_rcd->id ?? '' }}" name="address_id[]"  data-index="{{ $index }}"> --}}
                                                @include('address.multiple_address_list',compact('index', 'address_rcd'))
                                            @endforeach
                                        </div>

                                </div>



                            </div>

                             <hr/>

                                @if($quote_data['is_advance_payment'] == 1)
                                <div class="row mb-2">
                                    <h3 class="mt-2 text-center">Payment Detail</h3>

                                    <div class="col-md-3">
                                        <label for="account_transaction_type" class="form-label">Account Transaction Type</label>
                                        <select  class="form-control" disabled>
                                            <option>Select Account Transaction</option>
                                            @foreach ($account_transaction_type as $key=>$account_list)
                                                <option value="{{ $key }}"  @if(isset($account_list) && $key == 'credit') selected @endif>{{ $account_list }} </option>
                                            @endforeach
                                        </select>
                                       <input type="hidden" name="account_transaction_type" class="form-control" value="credit">
                                    </div>

                                    <div class="col-md-3" id="bank_detail_id">
                                        <label for="bank_detail_id" class="form-label">Bank Account</label> <x-required></x-required>
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

                                    <div class="col-md-3">
                                        <label for="entity_id" class="form-label">Select Customer</label>
                                        <select  id="dynamic_select" class="form-select mb-3 form-control" disabled>
                                            <option value="">Select Customer</option>
                                            @foreach ($customer_list as $id=>$clist)
                                                <option value="{{ $id }}"
                                                    @if(isset($quote_data) && $id == $quote_data['customer_id']) selected @endif >
                                                    {{ $clist }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="entity_id" class="form-control" value="{{ $quote_data['customer_id'] }}">
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="amount_id" class="form-label">Amount</label> <x-required></x-required>
                                        <input type="text" class="form-control" name="amount" value="{{ $quote_data->advance_payment ?? 0 }}" readonly>
                                        <span class="text-danger" id="error-amount"></span>
                                    </div>

                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-3">

                                        <label for="payment_method" class="form-label">Payment Method</label> <x-required></x-required>
                                        {{ Form::select('payment_method',  ['' => 'Select Method'] + $paymentMethods, null, [
                                            'class' => 'form-select mb-3 choices-select form-control',
                                            'id' => 'payment_method',
                                            'data-choices',
                                            'data-choices-removeItem',
                                            'onChange="showTransaction(this)"',
                                        ]) }}
                                        <span class="text-danger" id="error-payment_method"></span>
                                    </div>

                                    <div class="col-md-3 d-none" id="transaction_list">
                                        <label for="transaction_id" class="form-label">Transaction ID / Cheque No</label>
                                        <input type="text" name="transaction_id" id="transaction_id" class="form-control" placeholder="Transaction ID">
                                        <span class="text-danger" id="error-transaction_id"></span>
                                    </div>

                                    <div class="col-md-3 ">

                                        <label for="payment_date" class="form-label">Date</label> <x-required></x-required>
                                        <input type="date" name="payment_date" id="datepicker-range" class="form-control datepicker-range" placeholder="Payment Date" required data-provider="flatpickr" data-range="true">
                                        <span class="text-danger" id="error-payment_date"></span>

                                    </div>

                                    <div class="col-md-3">
                                        <label for="description" class="form-label">Description</label> <x-required></x-required>
                                        <textarea name="description" id="description" class="form-control" placeholder="Description" required rows="3"></textarea>
                                        <span class="text-danger" id="error-description"></span>
                                    </div>

                                    <div class="col-md-3">

                                        <div>
                                            <label for="attachment" class="form-label">Attachment</label>
                                            <input class="form-control" type="file" id="attachment" name="attachment">
                                            <small class="form-text text-muted">Upload receipt or related document (optional)</small>
                                            <small id="imageError" class="text-danger d-block mt-1"></small>
                                        </div>

                                    </div>
                                </div>
                                @endif


                            <div class="text-center mb-3">
                                <button type="submit" class="btn btn-success w-sm">Submit</button>
                            </div>

                        </form>
                        <!-- end card body -->
                        </div>
                </div>
            </div><!--end col-->
        </div><!--end row-->

    </div>
    <!-- container-fluid -->
</div>

<script>
   $('#quoteStatusForm').on('submit', function(e) {
            e.preventDefault();

            $('#error-bank_detail_id').text('');
            $('#error-payment_method').text('');
            $('#error-payment_date').text('');

            if ($('select[name="bank_detail_id"]').is(':visible')) {
                let bankVal = $('select[name="bank_detail_id"]').val();
                if (!bankVal) {
                    $('#error-bank_detail_id').text('Please select a Bank Account.');
                    return;
                }
            }

            if ($('#payment_method').is(':visible')) {
                let PayVal = $('#payment_method').val();
                if (!PayVal || PayVal.trim() === "") {
                    $('#error-payment_method').text('Please select Payment Method.');
                    return;
                }
            }

            let fieldName = "payment_date";
            let $field = $('[name="' + fieldName + '"]');

            if ($field.is(':visible')) {
                let fieldVal = $field.val().trim();
                if (!fieldVal) {
                    $('#error-' + fieldName).text('Payment date is required.');
                    return;
                } else {
                    $('#error-' + fieldName).text('');
                }
            }

            let form = $(this)[0];
            let formData = new FormData(form);
            let url = $(this).attr('action');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                success: function(res) {
                    // $('#success-message').removeClass('d-none');
                     if(res.error == 'yes')
                    {
                        show_toastr('error', res.message_success);
                    }

                    if(res.success == 'true')
                    {
                        show_toastr('success', res.message_success || 'Quote has been updated successfully.');
                        setTimeout(function() {
                            window.location.href = res.redirect_url;
                        }, 1500);
                    }

                },
                error: function(xhr) {
                    let errors = xhr.responseJSON.errors;
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            $('#error-' + key).text(value[0]);
                        });
                        $('a[href="#quoteStatusForm"]').tab('show');
                        console.log('Unexpected error:', xhr.responseText);
                    } else {
                        console.log('Something went wrong.');
                        console.log('Unexpected error:', xhr.responseText);
                    }
                }
            });
        });

</script>
<script>
$(document).ready(function() {
    $('.remove-address-btn').hide();
    $('#is_check').change(function() {
        if ($(this).is(':checked')) {
            $('.shipping_addr_section').hide();
        } else {
            $('.shipping_addr_section').show();
        }
    });
});
</script>
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
    </script>

@endsection
