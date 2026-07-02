@extends('layouts.app')
@section('page-css')
    <style>
        .ecom-status {
            list-style: none;
            padding: 0;
            margin: 0;
            position: relative;
        }

        .ecom-status::before {
            content: "";
            position: absolute;
            left: 9px;
            top: 0;
            width: 2px;
            height: 100%;
            background: #e5e7eb;
        }

        .ecom-status-item {
            position: relative;
            padding-left: 35px;
            margin-bottom: 22px;
        }

        .status-dot {
            position: absolute;
            left: 0;
            top: 3px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
        }

        .ecom-status-item.active .status-dot {
            box-shadow: 0 0 0 3px rgba(10, 179, 156, .15);
        }

        .status-content h6 {
            font-size: 14px;
            font-weight: 600;
        }
    </style>
@endsection


@section('content')
 @php
        $check_discount_flag = \App\Models\Utility::isDiscountAllowed();
    @endphp
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Order Details</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">Order</a></li>
                                <li class="breadcrumb-item active">Order Details</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row">
                <div class="col-xl-9 ">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center">
                                {{-- <h5 class="card-title flex-grow-1 mb-0">Order - {{ $order['order_number'] }} ({{ $order->Orderstatus->name ?? '' }} )</h5> --}}
                                <h5 class="card-title flex-grow-1 mb-0">Order - {{ $order['order_number'] }}</h5>
                                <div class="d-flex justify-content-end gap-3">
                                    <div class="">
                                        @if (count($order_status_list) > 0)
                                            <select name="order_status" class="form-control form-control-sm"
                                                id="order_status_dropdown" data-order-id="{{ $order->id }}">
                                                <option value="">Select Order Status</option>
                                                @foreach ($order_status_list as $id => $name)
                                                    <option value="{{ $id }}"
                                                        @if ($order['status'] == $id) selected @endif>
                                                        {{ $name }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>


                                    {{-- <a href="{{ route('orders.invoice', $order['id']) }}" class="btn btn-success "><i
                                            class="ri-download-2-fill align-middle me-1"></i> Invoice </a> --}}

                                    {{-- <a href="javascript:void(0);" class="btn btn-sm btn-success" data-size="lg"
                                        data-url="{{ route('orders.invoice.file', $order->id) }}" data-ajax-popup="true"
                                        data-bs-original-title="Invoice Options"><i class="ri-download-2-fill"></i>
                                        Invoice</a> --}}

                                </div>
                            </div>

                            {{-- <div>
                                <div class="d-flex align-items-center">
                                    <p class="text-muted mb-0" id="bill-number-text">
                                        Bill No :- {{ $order->bill_number ?? '' }}
                                    </p>

                                    <input type="text" id="bill-number-input"
                                        class="form-control form-control-sm d-none border-bottom" style="width:150px"
                                        value="{{ $order->bill_number }}">

                                    <a id="edit-bill-number-btn" class="ms-2">
                                        <i class="ri-pencil-line" id="bill-edit-icon"></i>
                                        <i class="ri-save-line d-none" id="bill-save-icon"></i>
                                    </a>
                                </div>
                            </div> --}}
                        </div>
                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-sm table-nowrap align-middle table-bordered mb-0">
                                    <thead class=" ">
                                        <tr>
                                            <th scope="col">Product Details</th>
                                            <th scope="col">Price</th>
                                            <th scope="col">Quantity</th>

                                            <th scope="col " class="hide_discount">Discount </th>
                                            <th scope="col" class="text-end">Total Amount</th>
                                        </tr>
                                    </thead>
                                    @php
                                        $cust_avatar = $order->getCustomer->avatar ?? null;

                                        $filePath = storage_path('uploads/avatar/' . $cust_avatar);

                                        $cust_avatarUrl =
                                            !empty($cust_avatar) && \File::exists($filePath)
                                                ? asset('storage/uploads/avatar/' . $cust_avatar)
                                                : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
                                    @endphp
                                    <tbody>
                                        @php
                                            $sum = 0;
                                        @endphp
                                        @foreach ($order->orderProducts as $order_product)
                                            @php
                                                $sum += $order_product['total'];
                                                // $cust_avatar = $order->getCustomer->avatar;
                                                // $filePath = storage_path('uploads/avatar/' . $cust_avatar);
                                                // $cust_avatarUrl =
                                                //     !empty($cust_avatar) && \File::exists($filePath)
                                                //         ? asset('storage/uploads/avatar/' . $cust_avatar)
                                                //         : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';

                                                // $product_image = $order_product->product->image;
                                                // $product_img_path = storage_path('/uploads/product/' . $product_image);
                                                // $product_Url =
                                                //     !empty($product_image) && \File::exists($product_img_path)
                                                //         ? asset('storage/uploads/avatar/' . $product_image)
                                                //         : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0  bg-light rounded p-1"
                                                            style="width: 50px; height: 50px;">
                                                            <img src="{{ $order_product->product->image ?? '' }}"alt=""
                                                                style="width: 100%; height: 100%; object-fit: cover">
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h5 class="fs-15"><a href="apps-ecommerce-product-details.html"
                                                                    class="link-primary">{{ $order_product->product->name ?? '' }}</a>
                                                            </h5>

                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ number_format($order_product->price, 2) }}</td>
                                                <td>{{ $order_product->qty }}</td>
                                                <td class="hide_discount">
                                                    {{ $order_product->discount }}%
                                                </td>
                                                <td class="fw-medium text-end">
                                                    {{ $order_product->total }}
                                                </td>
                                            </tr>
                                        @endforeach
                                        @php

                                            $final_amt = 0;
                                            $final_amt = number_format(
                                                (float) $order['transport_charge'] + (float) $order['grand_total'],
                                                2,
                                                '.',
                                                '',
                                            );
                                        @endphp
                                        {{-- <tr>
                                            <th colspan="@if($check_discount_flag && $check_discount_flag == 1) 3 @else 2 @endif" class=""></th>
                                            <th class="text-end bg-light">Sub Total</th>
                                            <th class="text-end bg-light">{{ $sum }}</th>
                                        </tr> --}}
                                        <tr>
                                            <th colspan="@if($check_discount_flag && $check_discount_flag == 1) 3 @else 2 @endif" class=""></th>
                                            <th class="text-end bg-light">Tax</th>
                                            <th class="text-end bg-light">{{ number_format($order['gst'], 2) }}</th>
                                        </tr>
                                        {{-- <tr>
                                            <th colspan="@if($check_discount_flag && $check_discount_flag == 1) 3 @else 2 @endif" class=""></th>
                                            <th class="text-end bg-light">Product Total</th>
                                            <th class="text-end bg-light">{{ number_format($order['grand_total'], 2) }}
                                            </th>
                                        </tr> --}}
                                        <tr>
                                            <th colspan="@if($check_discount_flag && $check_discount_flag == 1) 3 @else 2 @endif" class=""></th>
                                            <th class="text-end bg-light">Transport Charge</th>
                                            <th class="text-end bg-light">
                                                {{ number_format($order['transport_charge'], 2) }}</th>
                                        </tr>
                                        <tr>
                                            <th colspan="@if($check_discount_flag && $check_discount_flag == 1) 3 @else 2 @endif" class=""></th>
                                            <th class="text-end bg-light">Final Total</th>
                                            <th class="text-end bg-light">{{ number_format($final_amt, 2) }}</th>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="col-xl-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Order Status</h5>
                        </div>

                        <div class="card-body">
                            @php
                                $currentStage = $order_stages->firstWhere('id', $order->status);
                            @endphp

                            <ul class="ecom-status">
                                @foreach ($order_stages as $stage)
                                    <li
                                        class="ecom-status-item {{ $stage->order <= $currentStage->order ? 'active' : '' }}">
                                        <span class="status-dot"
                                            style="background: {{ $stage->order <= $currentStage->order ? $stage->color : '#d1d5db' }}">
                                        </span>

                                        <div class="status-content">
                                            <h6 class="mb-1">{{ $stage->name }}</h6>

                                            @if ($stage->id == $order->status)
                                                <small class="text-muted">
                                                    {{ $order->updated_at->format('d M Y') }}
                                                </small>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>

                        </div>
                    </div>
                </div>
	            </div>
	            <!--end row-->

                <div class="row mt-4" id="activity-history">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Activity History</h5>
                            </div>
                            <div class="card-body">
                                @include('activity._timeline', [
                                    'activities' => $activityTimeline,
                                    'emptyMessage' => 'No activity found for this order.',
                                ])
                            </div>
                        </div>
                    </div>
                </div>

	        </div><!-- container-fluid -->
	    </div>
@endsection
@section('page-script')
    <script>
        // $(document).on('click', '#generate-invoice-btn', function() {

        //     var billNumber = $('#bill-number').val();
        //      var lrNumber = $('#lr-number').val();
        //       var noArticles = $('#no_article').val();
        //         var tranptCharge = $('#transport_charge').val();

        //     // Add bill number to order.
        //     let requestData = {
        //         bill_number: billNumber,
        //         lr_number: lrNumber,
        //         no_article: noArticles,
        //         transport_charge: tranptCharge,
        //     };

        //     const url = "{{ route('orders.add-bill-number', [$order['id']]) }}";
        //     postAjax(url, requestData, function(response) {

        //         show_toastr('success', response.message);
        //         window.location.href = response.url;

        //     });

        // });
    </script>
    <script>
        $(document).on('change', '#order_status_dropdown', function() {

            let status_id = $(this).val();
            let order_id = $(this).data('order-id');

            if (status_id === "") {
                return;
            }

            $.ajax({
                url: "{{ route('orders.update.status') }}",
                type: "POST",
                data: {
                    order_id: order_id,
                    status_id: status_id,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    show_toastr('success', 'Order status updated!');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                },
                error: function(err) {
                    show_toastr('error', 'Failed to update status!');
                    console.log(err);
                }
            });

        });
    </script>
    <script>
        applyDiscountVisibility();
    </script>

    <script>
        function updateData(cb) {

            var billNumber = $('#bill-number-input').val();
            var lrNumber = $('#lr-number-input').val();
            var tranptCharge = $('#transport-charge-input').val();
            var no_article = $('#no-articales-charge-input').val();

            // Add bill number to order.
            let requestData = {
                bill_number: billNumber,
                lr_number: lrNumber,
                transport_charge: tranptCharge,
                no_article: no_article,
            };

            const url = "{{ route('orders.add-bill-number', [$order['id']]) }}";
            postAjax(url, requestData, cb);

        }


        $("#edit-lr-number-btn").on("click", function() {
            let isEditing = $("#lr-number-input").hasClass("d-none") === false;

            if (!isEditing) {
                // Enter Edit Mode
                $("#lr-number-text").addClass("d-none");
                $("#lr-number-input").removeClass("d-none").focus();
                $("#lr-edit-icon").addClass("d-none");
                $("#lr-save-icon").removeClass("d-none");

            } else {
                // Save Mode
                let newNumber = $("#lr-number-input").val();
                let orderId = "{{ $order->id }}";

                updateData(function(response) {

                    show_toastr('success', response.message);
                    show_toastr('success', "LR Number has been updated.");

                    $("#lr-number-text").text("LR Number : " + newNumber);

                    // Back to View Mode
                    $("#lr-number-text").removeClass("d-none");
                    $("#lr-number-input").addClass("d-none");
                    $("#lr-edit-icon").removeClass("d-none");
                    $("#lr-save-icon").addClass("d-none");

                });
            }
        });


        $("#edit-transport-charge-btn").on("click", function() {
            let isEditing = $("#transport-charge-input").hasClass("d-none") === false;

            if (!isEditing) {
                // Enter Edit Mode
                $("#transport-charge-text").addClass("d-none");
                $("#transport-charge-input").removeClass("d-none").focus();
                $("#transport-charge-edit-icon").addClass("d-none");
                $("#transport-charge-save-icon").removeClass("d-none");

            } else {
                // Save Mode
                let newNumber = $("#transport-charge-input").val();

                updateData(function(response) {

                    show_toastr('success', "Transport Charge has been updated.");

                    $("#transport-charge-text").text("Trans Chrg : " + newNumber);

                    // Back to View Mode
                    $("#transport-charge-text").removeClass("d-none");
                    $("#transport-charge-input").addClass("d-none");
                    $("#transport-charge-edit-icon").removeClass("d-none");
                    $("#transport-charge-save-icon").addClass("d-none");

                });

            }
        });


        $("#edit-no-articales-charge-btn").on("click", function() {

            let isEditing = $("#no-articales-charge-input").hasClass("d-none") === false;

            if (!isEditing) {
                // Enter edit mode
                $("#no-articales-text").addClass("d-none");
                $("#no-articales-charge-input").removeClass("d-none").focus();
                $("#no-articales-charge-edit-icon").addClass("d-none");
                $("#no-articales-charge-save-icon").removeClass("d-none");

            } else {
                // Save mode
                let newNumber = $("#no-articales-charge-input").val();

                updateData(function(response) {

                    show_toastr('success', 'No Of Article updated.');

                    $("#no-articales-text").text("No Of Article : " + newNumber);

                    // Back to view mode
                    $("#no-articales-text").removeClass("d-none");
                    $("#no-articales-charge-input").addClass("d-none");
                    $("#no-articales-charge-edit-icon").removeClass("d-none");
                    $("#no-articales-charge-save-icon").addClass("d-none");
                });
            }
        });

        $("#edit-bill-number-btn").on("click", function() {

            let isEditing = $("#bill-number-input").hasClass("d-none") === false;

            if (!isEditing) {
                // Edit mode
                $("#bill-number-text").addClass("d-none");
                $("#bill-number-input").removeClass("d-none").focus();
                $("#bill-edit-icon").addClass("d-none");
                $("#bill-save-icon").removeClass("d-none");

            } else {
                // Save mode
                let billNumber = $("#bill-number-input").val();

                updateData(function(response) {

                    show_toastr('success', 'Bill number updated.');

                    $("#bill-number-text").text("Bill No : " + billNumber);

                    $("#bill-number-text").removeClass("d-none");
                    $("#bill-number-input").addClass("d-none");
                    $("#bill-edit-icon").removeClass("d-none");
                    $("#bill-save-icon").addClass("d-none");
                });
            }
        });
    </script>
@endsection
