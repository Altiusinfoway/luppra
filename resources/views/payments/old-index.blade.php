@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Payments</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('quotes.index') }}">Payments</a></li>
                            <li class="breadcrumb-item active">List</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>

        <div class="row">
            <!-- Varying Modal Content -->
            <div class="col-lg-12">
                <div class="card">

                    @if(request()->has('message_success'))
                        <div class="alert bg-primary text-white alert-dismissible fade show col-md-6 m-3" role="alert" id="success_msg">
                            {{ request()->get('message_success') }}
                            <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="card-header">
                        <h5 class="card-title  mb-0">Payments List</h5>

                        <div class="d-flex mt-3 justify-content-between align-items-center w-100">
                            {{--
                            <form method="get" action="{{ route('quotes.index') }}" id="quote_form" class="d-flex gap-2">

                                <select name="lead_filter" class="form-control mr-3 " onchange="document.getElementById('quote_form').submit();">
                                    <option value="">Select Lead Name</option>
                                    @foreach ($lead_list as $l_list)
                                    <option value="{{ $l_list['id'] }}" {{ request('lead_filter') == $l_list['id'] ? 'selected' : '' }} >{{ $l_list['name'] }} </option>
                                    @endforeach
                                </select>

                                <select name="status_filter" class="form-control" onchange="document.getElementById('quote_form').submit();">
                                    <option value="">Select Status</option>
                                    <option value="1" {{ request('status_filter') == 1 ? 'selected' : '' }} >Pending </option>
                                    <option value="2" {{ request('status_filter') == 2 ? 'selected' : '' }} >Send </option>
                                    <option value="3" {{ request('status_filter') == 3 ? 'selected' : '' }} >Final </option>
                                </select>

                                <input class="form-control datepicker-range flatpickr-input active" id="datepicker-range" data-provider="flatpickr" data-range="true" name="start_date_filter" onchange="document.getElementById('quote_form').submit();" type="text" readonly="readonly" placeholder="Select Start Date" value="{{ request('start_date_filter') }}" >

                                <input class="form-control datepicker-range flatpickr-input active" id="datepicker-range" data-provider="flatpickr" data-range="true" name="end_date_filter" onchange="document.getElementById('quote_form').submit();" type="text" readonly="readonly" placeholder="Select End Date" value="{{ request('end_date_filter') }}">

                            </form>
                            --}}

                            @can('create payment')
                            <div>
                                <a  href="javascript:void(0);"
                                    class="btn btn-success"
                                    data-size="xl"
                                    data-url="{{ route('payments.create') }}"
                                    data-ajax-popup="true"
                                    data-bs-original-title="{{__('Add Payment')}}"><i class="ri-add-line align-bottom me-1"></i> Add Payment</a>
                            </div>
                            @endcan

                        </div>
                    </div>

                    <div class="card-body">
                        <table id="example" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 150px;">id</th>
                                    <th data-ordering="false">Order ID</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Transaction ID</th>
                                    <th>Payment Method</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($payments as $payment)
                                <tr class="main-row">

                                    <td>
                                        {{ $payment->id }}
                                    </td>

                                    <td>
                                        {{ $payment->order_id }}
                                    </td>

                                    <td>
                                        {{ $payment->amount }}
                                    </td>

                                    <td>
                                        {{ App\Models\Utility::getDateFormated($payment->date) }}
                                    </td>
                                    <td>
                                        {{ $payment->transaction_id }}
                                    </td>

                                    <td>
                                        {{ $payment->payment_method }}
                                    </td>

                                    <td>
                                        {{ $payment->payment_status }}
                                    </td>

                                    <td>
                                        @if (Gate::check('edit payment') || Gate::check('delete payment'))
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-more-fill align-middle"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                            @can('edit payment')

                                                <li><a href="javascript:void(0);" class="dropdown-item edit-item-btn" data-size="xl" data-url="{{ route('quotes.edit',[$payment->id]) }}" data-ajax-popup="true" data-bs-original-title="{{__('Edit Quotes')}}"><i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit</a></li>

                                            @endcan
                                            @can('delete payment')
                                                <li>
                                                    <a class="dropdown-item remove-item-btn"
                                                        data-delete-popup="true"
                                                        data-bs-original-title="You are about to delete a Quotes ?"  data-bs-original-description="Deleting your Quotes will remove all of your information from our database."
                                                        data-original-title=""
                                                        data-url="{{ route('quotes.delete',[$payment->id]) }}"
                                                        data-method="DELETE"
                                                        data-cb="afterDelete"
                                                        href="javascript:void(0)">
                                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i>
                                                        {{ __('Delete') }}
                                                    </a>
                                                </li>
                                            @endcan

                                            </ul>
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!--end col-->
        </div>
    </div>
</div>

<script>
    function openStatusEditPage(element)
    {
        const url = element.getAttribute('data-url');
        window.location.href = url;
    }
</script>
<script>
    $(document).ready(function(){
        setTimeout(function(){
            $('#success_msg').fadeOut(1000);
        }, 3000);
    });
</script>
@endsection