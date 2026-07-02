@extends('layouts.app')

@section('page-css')
    <!-- quill css -->
    <link href="{{ asset('public/build/assets/libs/quill/quill.core.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('public/build/assets/libs/quill/quill.bubble.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('public/build/assets/libs/quill/quill.snow.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    @php
        $companyName = optional($lead->customer)->company_name ?? '';
        $customerName = $lead->customer->name;
        $firstLetter = strtoupper(substr($customerName ?? '', 0, 1));
        $lead_created = $lead->user->name ?? '';
        $primary = $lead->customerPhone()->where('is_primary', 1)->first();
        $secondary = $lead->customerPhone()->where('is_secondary', 1)->first();
        if (empty($secondary)) {
            $secondary = $lead->customerPhone()->where('is_whatsapp', 1)->first();
        }

        //address
        $getBillingAddress = optional($lead->customer)->getBillingAddress;

        $addressLine1 = $addressLine2 = $cityName = $stateName = $countryName = '';

        if ($getBillingAddress) {
            $addressLine1 = $getBillingAddress->address_line_1;
            $addressLine2 = $getBillingAddress->address_line_2;

            $cityName = optional(optional($getBillingAddress)->get_city)->name;
            $stateName = optional(optional($getBillingAddress)->get_state)->name;
            $countryName = optional(optional($getBillingAddress)->get_country)->name;
        }

        $percentage = $percentage ?? 0;
        $displayPercentage = max(min($percentage, 100), 1);

        $all_lead_sources = explode(',', $lead->sources);
        $sourceNames = \App\Models\LeadSource::withTrashed()->whereIn('id', $all_lead_sources)->pluck('name')->toArray();

        $total_lead_product_amount = \App\Models\LeadProducts::where('lead_id', $lead->id)
            ->get()
            ->sum(function ($p) {
                return $p->price * $p->qty;
            });
        $attachmentCount = isset($lead_attachments) ? count($lead_attachments) : 0;

    @endphp


    <div class="page-content">
        <div class="container-fluid">
            <div class="profile-foreground position-relative mx-n4 mt-n4">

                <div class="profile-wid-bg">
                </div>
            </div>
            <div class="pt-4 mb-3 profile-wrapper">
                <div class="row g-4">
                    <div class="col-auto">
                        <div
                            class="avatar-lg bg-light rounded-circle d-flex justify-content-center align-items-center border border-dark">
                            <h1><span>{{ $firstLetter ?? '' }}</span></h1>
                        </div>
                    </div>
                    <!--end col-->
                    <div class="col">
                        <div class="p-2">
                            <h3 class="mb-1">{{ $lead->name ?? '' }}</h3>
                            <div class="row">
                                <div class="col-4">
                                    <p class="m-0"><i
                                            class="ri-user-fill me-1 text-opacity-75 fs-16 align-middle"></i>name:- <span
                                            class="text-muted">{{ $customerName ?? '' }}</span></p>
                                </div>

                                <div class="col-4">
                                    <p class="m-0"><i
                                            class=" ri-building-3-fill me-1 text-opacity-75 fs-16 align-middle"></i>Company:-
                                        <span class="text-muted">{{ $companyName ?? '' }}</span>
                                    </p>
                                </div>
                                <div class="col-4">
                                    <p class="mb-3 "><i
                                            class="ri-file-user-fill me-1 text-opacity-75 fs-16 align-middle"></i>Lead By:-
                                        <span class="text-muted"> {{ $lead_created ?? '' }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="mt-auto mb-4">
                                <div class="d-flex mb-2">
                                    <div class="flex-grow-1">
                                        <div>Lead Status</div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <div>{{ $precentage ?? 0 }} %</div>
                                    </div>
                                </div>
                                {{-- <div class="progress progress-sm animated-progress">
                                    <div class="progress-bar bg-success" role="progressbar" aria-valuenow="34"
                                        aria-valuemin="0" aria-valuemax="100" style="width: 18%;"></div>
                                    <!-- /.progress-bar -->
                                </div><!-- /.progress --> --}}

                                <div class="progress progress-sm" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                        style="width: {{ $displayPercentage }}%; height: 8px;">
                                    </div>
                                </div>


                            </div>
                            <div class="row">
                                <div class="col-4">
                                    <div class="mb-2">
                                        <i
                                            class=" ri-phone-line me-1 text-opacity-75 fs-16 align-middle"></i>{{ $primary->phone ?? '' }}
                                        <span class="badge badge-label bg-primary">Primary</span>
                                    </div>

                                    <div class="mb-2">
                                        <i
                                            class="ri-whatsapp-line me-1 text-opacity-75 fs-16 align-middle"></i>{{ $secondary->phone ?? '' }}
                                        <span class="badge badge-label bg-success">Secondary</span>
                                    </div>

                                </div>
                                <div class="col-4">
                                    <div class="mb-2">
                                        <i class=" ri-calendar-2-line me-1 text-opacity-75 fs-16 align-middle"></i>
                                        {{ \App\Models\Utility::getDateFormated($lead->date) ?? '' }}
                                        <span class="badge badge-label bg-danger">Create Date</span>


                                    </div>
                                    <div class="mb-2">
                                        <i class=" ri-alarm-line me-1 text-opacity-75 fs-16 align-middle"></i>
                                        {{ \App\Models\Utility::getDateFormated($lead->next_contact_date) ?? '' }}
                                        <span class="badge badge-label bg-warning">Next Appointment Date</span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="mb-2">
                                        <i
                                            class=" ri-mail-line me-1 text-opacity-75 fs-16 align-middle"></i>{{ optional($lead->customer)->email ?? '' }}
                                    </div>
                                    <div class="mb-2">
                                        <i class="ri-map-pin-line me-1 text-opacity-75 fs-16 align-middle"></i>
                                        {{ $cityName ? $cityName : '' }}
                                        {{ $stateName ? ',' . $stateName : '' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end col-->
                    {{-- <div class="col-12 col-lg-auto order-last order-lg-0">
                        <div class="row text text-dark-50 text-center">
                            <div class="col-lg-6 col-4">
                                <!-- Vertical Variation -->
                                <div class="btn-group material-shadow" role="group"
                                    aria-label="Button group with nested dropdown">
                                    <div class="btn-group" role="group">
                                        <button id="btnGroupDrop1" type="button"
                                            class="btn btn-sm btn-primary dropdown-toggle material-shadow-none"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            Stage
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                            <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                            <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-4">
                                <a class="btn btn-sm btn-success waves-effect waves-light">Back</a>
                            </div>
                        </div>
                    </div> --}}

                    <div class="col-12 col-lg-auto order-last order-lg-0">
                        <div class="row text-dark-50 text-center">

                            <div class="col-12 col-lg-6 mb-2 mb-lg-0">

                                <div class="d-flex align-items-center justify-content-center justify-content-lg-start">
                                    <div>

                                        <select class="form-control form-select form-select-sm stage-dropdown need-confirmation w-100"
                                            data-data='{"id": {{ $lead->id }} }'
                                            data-url="{{ route('leads.stage.update', [$lead->id, '#sticky']) }}">

                                            <option value="">Select Stage</option>

                                            @foreach (\App\Models\LeadStage::all() as $stage)
                                                <option value="{{ $stage->id }}" {{ $lead->stage_id == $stage->id ? 'selected' : '' }}>
                                                    {{ $stage->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                    </div>
                                </div>

                            </div>

                            <div class="col-12 col-lg-6">
                                <a href="{{ route('leads.list','all_leads') }}" class="btn btn-sm btn-success w-100">Back</a>
                            </div>

                        </div>
                    </div>
                    <!--end col-->

                </div>
                <!--end row-->
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card crm-widget mt-3">
                            <div class="card-body p-0">
                                <div class="row row-cols-md-3 row-cols-1">
                                    <div class="col col-lg border-end">
                                        <div class="py-2 px-3">
                                            <h5 class="text-muted text-uppercase fs-13">Products <i
                                                    class="ri-arrow-up-circle-line  fs-18 float-end align-middle"></i>
                                            </h5>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ri-product-hunt-line display-6 text-muted"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h4 class="mb-0"><span class="counter-value"
                                                            data-target="{{ count($lead->products()) > 0 ? $lead->products()->count() : 0 }}">
                                                            {{ count($lead->products()) > 0 ? $lead->products()->count() : 0 }}</span>
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- end col -->
                                    {{-- <div class="col col-lg border-end">
                                        <div class="mt-3 mt-md-0 py-2 px-3">
                                            <h5 class="text-muted text-uppercase fs-13">Lead volume<i
                                                    class="ri-arrow-up-circle-line  fs-18 float-end align-middle"></i>
                                            </h5>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ri-price-tag-3-line display-6 text-muted"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h4 class="mb-0">₹<span class="counter-value"
                                                            data-target="{{ $total_lead_product_amount ?? 0 }}">{{ $total_lead_product_amount ?? 0 }}</span>
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div> --}}
                                    <div class="col col-lg border-end">
                                        <div class="mt-3 mt-md-0 py-2 px-3">
                                            <h5 class="text-muted text-uppercase fs-13">Lead Source<i
                                                    class="ri-arrow-up-circle-line  fs-18 float-end align-middle"></i>
                                            </h5>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ri-open-source-line display-6 text-muted"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h4 class="mb-0"> {{ implode(', ', $sourceNames) }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- end col -->
                                    <div class="col col-lg">
                                        <div class="mt-3 mt-lg-0 py-2 px-3">
                                            <h5 class="text-muted text-uppercase fs-13">
                                                Attachments<i
                                                    class="ri-arrow-up-circle-line  fs-18 float-end align-middle"></i>
                                            </h5>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ri-folder-2-line display-6 text-muted"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h4 class="mb-0"><span class="counter-value" data-attachment-counter
                                                            data-target="{{ $attachmentCount }}">{{ $attachmentCount }}</span></h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- end col -->
                                </div><!-- end row -->
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row gx-lg-5">
                                <div class="col-lg-7 col-xl-8">
                                    <div class="mt-xl-0 mt-5 mb-5">
                                        <div class="d-flex mb-3 align-items-center">
                                            <div class="flex-grow-1">
                                                <h4>Quote List</h4>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <div>
                                                    <a href="{{ route('quotes.create',[$lead->customer_id, $lead->id]) }}" class="btn btn-sm btn-light"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="add"><i class=" ri-add-fill align-bottom"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Bordered Tables -->
                                        <table class="table table-bordered table-nowrap">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Id</th>
                                                    <th scope="col">Name</th>
                                                    <th scope="col">Status</th>
                                                    <th scope="col">Date</th>
                                                    <th scope="col">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($quote_list as $qtlist)
                                                    <tr>
                                                        <th scope="row">{{ $loop->iteration }}</th>
                                                        <td>{{ $qtlist->customer->name ?? '' }}</td>
                                                        <td>
                                                            @if ($qtlist->status == 1)
                                                                <span class="badge bg-danger ">
                                                                    Pending
                                                                </span>
                                                            @elseif($qtlist->status == 2)
                                                                <span class="badge bg-primary ">
                                                                    Send
                                                                </span>
                                                            @else
                                                                <span class="badge bg-success ">
                                                                    Final
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $data_new = \App\Models\Utility::getDateFormated($qtlist->date);
                                                            ?>
                                                            {{ $data_new ?? '' }}
                                                        </td>
                                                        <td>
                                                            <div class="dropdown">
                                                                <a href="#" role="button" id="dropdownMenuLink"
                                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="ri-more-2-fill"></i>
                                                                </a>

                                                                <ul class="dropdown-menu"
                                                                    aria-labelledby="dropdownMenuLink">
                                                                    @if ($qtlist->status != 3)
                                                                        <li><a class="dropdown-item"
                                                                                href="{{ route('quotes.edit_status', $qtlist->id) }}">View</a>
                                                                    @endif
                                                                    </li>
                                                                    <li><a class="dropdown-item"
                                                                            href="{{ route('quotes.edit', $qtlist->id) }}">Edit</a>
                                                                    </li>
                                                                    <li>
                                                                        @php
                                                                            $deleteUrl = route(
                                                                                'quotes.delete',
                                                                                $qtlist->id,
                                                                            );
                                                                        @endphp

                                                                        <a class="dropdown-item remove-item-btn"
                                                                            data-delete-popup="true"
                                                                            data-bs-original-title="You are about to delete a Quote?"
                                                                            data-bs-original-description="Deleting this quote will remove it permanently."
                                                                            data-url="{{ $deleteUrl }}"
                                                                            data-method="DELETE" data-cb="afterDelete"
                                                                            href="javascript:void(0)">
                                                                            Delete
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach

                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-xl-0 mt-5 mb-5">
                                        <div class="d-flex mb-3 align-items-center">
                                            <div class="flex-grow-1">
                                                <h4>Product List</h4>
                                            </div>
                                            <div class="flex-shrink-0">

                                                <a data-size="lg" data-url="{{ route('leads.products', [$lead->id]) }}"
                                                    data-ajax-popup="true" data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{ __('Add Products') }}" title="Add Product"
                                                    class="btn btn-sm btn-light">
                                                    <i class="ri-add-fill align-bottom"></i>
                                                </a>


                                            </div>
                                        </div>
                                        <!-- Bordered Tables -->
                                        <table class="table table-bordered table-nowrap">
                                            <thead>
                                                <tr>
                                                    <th scope="col">SKU</th>
                                                    <th scope="col">Name</th>
                                                    <th scope="col">Price</th>
                                                    <th scope="col">Quntity</th>
                                                    <th scope="col">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($lead->product->isNotEmpty())
                                                    @foreach ($lead->product as $product)
                                                        <tr class="main-row" id="row-{{ $product->pivot->id }}"
                                                            data-uri="{{ route('leads.products.update', [$product->pivot->id]) }}"
                                                            data-main>

                                                            <td>{{ $product->sku_code }}</td>
                                                            <td><p class="text-wrap">{{ $product->name }}</p>
                                                            </td>
                                                            <td>
                                                                <span
                                                                    class="price-view">{{ $product->pivot->price }}</span>
                                                                <input type="number"
                                                                    class="form-control w-50 price-edit d-none"
                                                                    value="{{ $product->pivot->price }}">
                                                            </td>

                                                            <td>
                                                                <span class="qty-view">{{ $product->pivot->qty }}</span>
                                                                <input type="number"
                                                                    class="form-control w-50 qty-edit d-none"
                                                                    value="{{ $product->pivot->qty }}">
                                                            </td>

                                                            <td>
                                                                <a href="javascript:void(0)"
                                                                    class="mx-3 btn btn-sm  align-items-center bs-pass-para btn-light"
                                                                    data-bs-toggle="tooltip" title="Edit"
                                                                    onclick="editRow({{ $product->pivot->id }})"
                                                                    id="edit-btn-{{ $product->pivot->id }}">
                                                                    <i class="mdi mdi-pencil text-black"></i>
                                                                </a>


                                                            </td>

                                                        </tr>
                                                    @endforeach
                                                @endif

                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-xl-0 mt-5 mb-5">
                                        <div class="d-flex mb-3 align-items-center">
                                            <div class="flex-grow-1">
                                                <h4>Call List</h4>
                                            </div>

                                        </div>
                                        <!-- Bordered Tables -->
                                        <table class="table table-bordered table-nowrap">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Id</th>
                                                    {{-- <th scope="col">Audio</th> --}}
                                                    <th scope="col">Date Time</th>
                                                    <th scope="col">User </th>
                                                    <th scope="col">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($leadCallList as $leadcall)
                                                    <tr>
                                                        <th scope="row">{{ $loop->iteration }}</th>
                                                        {{-- <td class="p-0 px-2">
                                                            <div class="d-flex align-items-center gap-1">
                                                                <div class="p-1">

                                                                    @if (!empty($leadcall['audio']))
                                                                        <audio controls>
                                                                            <source src="{{ $leadcall['audio'] }}"
                                                                                type="audio/mpeg">
                                                                        </audio>
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </div>

                                                            </div>

                                                        </td> --}}
                                                        <td>
                                                            @php
                                                                $dt = \Carbon\Carbon::parse($leadcall->date_time);
                                                            @endphp
                                                            <div class="d-flex gap-4">
                                                                <div class="mb-2">
                                                                    <i
                                                                        class=" ri-alarm-line me-1 text-opacity-75 fs-16 align-middle"></i>
                                                                    {{ $dt->toTimeString() }}
                                                                </div>
                                                                <div class="mb-2">
                                                                    <i
                                                                        class=" ri-calendar-2-line me-1 text-opacity-75 fs-16 align-middle"></i>
                                                                    {{ $dt->toDateString() }}
                                                                </div>
                                                            </div>

                                                        </td>
                                                        <td>{{ $leadcall->user['name'] }}</td>
                                                        <td>
                                                            @if ($leadcall->status == 0)
                                                                <span
                                                                    class="badge bg-danger  badge-border">Not-Connecting</span>
                                                            @else
                                                                <span
                                                                    class="badge bg-success  badge-border">Connecting</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-xl-0 mt-5 mb-5">
                                        <div class="d-flex mb-3 align-items-center">
                                            <div class="flex-grow-1">
                                                <h4>Discussion List</h4>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <div>


                                                    <a data-size="md" data-url="{{ route('leads.chat', [$lead->id]) }}"
                                                        data-ajax-popup="true" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Add Discussion') }}"
                                                        title="Add Discussion" class="btn btn-sm btn-light">
                                                        <i class="ri-add-fill align-bottom"></i>
                                                    </a>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-4 bg-light p-3 ">
                                            <div class="col-lg-12">
                                                <div>
                                                    @foreach ($leadChat as $leadCht)
                                                        <div class="timeline-2">
                                                            <div class="timeline-continue">
                                                                <div class="row timeline-right">
                                                                    <div class="col-12">
                                                                        <p class="timeline-date">
                                                                            {{-- <i
                                                                            class=" ri-alarm-line me-1 text-opacity-75 fs-16 align-middle"></i>
                                                                        5:10pm&nbsp;&nbsp; --}}
                                                                            <i
                                                                                class=" ri-calendar-2-line me-1 text-opacity-75 fs-16 align-middle"></i>
                                                                            {{ \App\Models\Utility::getDateFormated($leadCht['next_date']) ?? '' }}
                                                                        </p>
                                                                    </div>
                                                                    <div class="col-12">
                                                                        <div class="timeline-box material-shadow">
                                                                            <div class="timeline-text">
                                                                                <div class="d-flex">
                                                                                    <div class="flex-grow-1 ms-3">
                                                                                        <p class="text-muted mb-0">
                                                                                            {{ $leadCht['chat'] }} </p>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    @endforeach

                                                </div>
                                            </div>
                                            <!--end col-->
                                        </div>
                                        <!--end row-->
                                    </div>

                                    <div class="card">
                                        <div class="card-header align-items-center d-flex border-bottom-dashed">
                                            <h4 class="card-title mb-0 flex-grow-1">Attachments</h4>
                                            <div class="flex-shrink-0">
                                                <form action="{{ route('leads.attachments.upload', $lead->id) }}" method="POST" enctype="multipart/form-data" class="d-inline-flex align-items-center gap-2">
                                                    @csrf
                                                    <input type="file" name="attachment" id="lead-attachment-input" class="d-none">
                                                    <button type="button" class="btn btn-soft-info btn-sm" onclick="document.getElementById('lead-attachment-input').click()">
                                                        <i class="ri-add-line me-1 align-bottom"></i> Add
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="card-body">

                                            <div class="vstack gap-2" id="lead-attachment-items">
                                                @forelse ($lead_attachments ?? [] as $file)
                                                    @php
                                                        $isAudio = in_array($file['ext'], ['mp3', 'wav', 'ogg', 'm4a', 'aac']);
                                                    @endphp
                                                    <div class="border rounded border-dashed p-2">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0 me-3">
                                                                <div class="avatar-sm">
                                                                    <div class="avatar-title bg-light text-secondary rounded fs-24">
                                                                        <i class="{{ $isAudio ? 'ri-file-music-line' : 'ri-file-line' }}"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1 overflow-hidden">
                                                                <h5 class="fs-13 mb-1">
                                                                    <a href="{{ $file['url'] }}" target="_blank" class="text-body text-truncate d-block">
                                                                        {{ $file['name'] }}
                                                                    </a>
                                                                </h5>
                                                                <div>{{ $file['size_formatted'] ?? '' }}</div>
                                                            </div>
                                                            <div class="flex-shrink-0 ms-2">
                                                                <a href="{{ $file['url'] }}" download class="btn btn-icon text-muted btn-sm fs-18 material-shadow-none">
                                                                    <i class="ri-download-2-line"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="text-muted text-center py-3">No attachments available for this lead.</div>
                                                @endforelse
                                            </div>
                                        </div>
                                        <!-- end card body -->
                                    </div>
                                </div>
                                <!-- end col -->
                                <div class="col-lg-5 col-xl-4 mx-auto">
                                    <div class="product-img-slider sticky-side-div">
                                        <div class="mb-3">
                                            <label for="Description" class="form-label">Description</label>
                                            <form action="{{ route('leads.description', $lead['id']) }}" method="post"
                                                id="leadnoteform">
                                                @csrf
                                                <textarea class="form-control border-dashed" id="notes" name="notes" rows="3">{{ $lead['notes'] }}</textarea>
                                                <button type="submit"
                                                    class="btn btn-sm btn-primary waves-effect waves-light mt-3 ">Submit</button>
                                            </form>
                                        </div>

                                        <div class="card">
                                            <div class="card-header">
                                                <div class="d-sm-flex align-items-center">
                                                    <h5 class="card-title flex-grow-1 mb-0">Activity</h5>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="profile-timeline">
                                                    <div class="accordion accordion-flush" id="accordionFlushExample">
                                                        <div class="accordion-item border-0">


                                                            @foreach ($leadActivity as $key => $leadAct)
                                                                <div class="accordion-item border-0">

                                                                    <div class="accordion-header"
                                                                        id="heading{{ $key }}">
                                                                        <a class="accordion-button p-2 shadow-none"
                                                                            data-bs-toggle="collapse"
                                                                            href="#collapse{{ $key }}"
                                                                            aria-expanded="true"
                                                                            aria-controls="collapse{{ $key }}">

                                                                            <div class="d-flex align-items-center">
                                                                                <div class="flex-shrink-0 avatar-xs">
                                                                                    <div
                                                                                        class="avatar-title bg-success rounded-circle material-shadow">
                                                                                        <i
                                                                                            class="mdi mdi-account-arrow-right-outline"></i>
                                                                                    </div>
                                                                                </div>

                                                                                <div class="flex-grow-1 ms-3">
                                                                                    <h6 class="fs-15 mb-0 fw-semibold">
                                                                                        {{ $leadAct['action'] }}
                                                                                        - {{ $leadAct->users->name }}</h6>
                                                                                </div>
                                                                            </div>

                                                                        </a>
                                                                    </div>

                                                                    <div id="collapse{{ $key }}"
                                                                        class="accordion-collapse collapse show"
                                                                        aria-labelledby="heading{{ $key }}"
                                                                        data-bs-parent="#accordionExample">

                                                                        <div class="accordion-body ms-2 ps-5 pt-0">
                                                                            <h6 class="mb-1">{{ $leadAct->desc }}</h6>
                                                                            <p class="text-muted">
                                                                                {{ \Carbon\Carbon::parse($leadAct->date_time)->format('D, d M Y - h:iA') }}
                                                                            </p>
                                                                        </div>

                                                                    </div>

                                                                </div>
                                                            @endforeach


                                                        </div>
                                                        <!--end accordion-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end col -->
                                </div>
                                <!-- end row -->
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->
	                </div>
	                <!-- end row -->
                    <div class="row mt-4" id="activity-history">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Activity History</h5>
                                </div>
                                <div class="card-body">
                                    @include('activity._timeline', [
                                        'activities' => $activityTimeline,
                                        'emptyMessage' => 'No activity found for this lead.',
                                    ])
                                </div>
                            </div>
                        </div>
                    </div>
	            </div>


	        </div>
    @endsection

    @section('scripts')
        <!-- quill js -->
        <script src="{{ asset('public/build/assets/libs/quill/quill.min.js') }}"></script>
    @endsection

    @section('page-script')
        <script>
            //Update inline products
            function editRow(id) {

                const row = document.getElementById('row-' + id);
                const editBtn = document.getElementById('edit-btn-' + id);

                const priceView = row.querySelector('.price-view');
                const qtyView = row.querySelector('.qty-view');
                const priceEdit = row.querySelector('.price-edit');
                const qtyEdit = row.querySelector('.qty-edit');

                // Toggle edit mode
                if (priceView && qtyView && priceEdit && qtyEdit)
                {
                    const isEditing = priceView.classList.contains('d-none');

                    if (isEditing) {
                        // Save
                        // const newPrice = priceEdit.querySelector('input').value;
                        // const newQty = qtyEdit.querySelector('input').value;

                        const newPrice = priceEdit.value;
                        const newQty = qtyEdit.value;


                        priceView.textContent = newPrice;
                        qtyView.textContent = newQty;

                        const uri = row.dataset.uri;

                        postAjax(uri, {
                            id: id,
                            price: newPrice,
                            qty: newQty
                        }, function(res) {

                            show_toastr('success', res.success);

                        });


                        priceView.classList.remove('d-none');
                        qtyView.classList.remove('d-none');
                        priceEdit.classList.add('d-none');
                        qtyEdit.classList.add('d-none');
                        editBtn.innerHTML = '<i class="mdi mdi-pencil text-black"></i>';
                    } else {
                        // Edit
                        priceView.classList.add('d-none');
                        qtyView.classList.add('d-none');
                        priceEdit.classList.remove('d-none');
                        qtyEdit.classList.remove('d-none');
                        editBtn.innerHTML = '<i class="mdi mdi-content-save text-black"></i>';
                    }
                }
            }


            //Editor
            var snowEditor = (document.querySelectorAll(".snow-editor")),
                bubbleEditor = (snowEditor && Array.from(snowEditor).forEach(function(e) {
                    var o = {};
                    1 == e.classList.contains("snow-editor") && (o.theme = "snow", o.modules = {
                        toolbar: [
                            [{
                                font: []
                            }, {
                                size: []
                            }],
                            ["bold", "italic", "underline", "strike"],
                            [{
                                color: []
                            }, {
                                background: []
                            }],
                            [{
                                script: "super"
                            }, {
                                script: "sub"
                            }],
                            [{
                                header: [!1, 1, 2, 3, 4, 5, 6]
                            }, "blockquote", "code-block"],
                            [{
                                list: "ordered"
                            }, {
                                list: "bullet"
                            }, {
                                indent: "-1"
                            }, {
                                indent: "+1"
                            }],
                            ["direction", {
                                align: []
                            }],
                            ["link", "image", "video"],
                            ["clean"]
                        ]
                    }), new Quill(e, o)
                }), document.querySelectorAll(".bubble-editor"));
            bubbleEditor && Array.from(bubbleEditor).forEach(function(e) {
                var o = {};
                1 == e.classList.contains("bubble-editor") && (o.theme = "bubble"), new Quill(e, o)
            });
        </script>
        <script>
            document.getElementById('leadnoteform').addEventListener('submit', function(e) {
                const quillEditor = document.querySelector('.snow-editor').__quill;
                document.getElementById('notes-input').value = quillEditor.root.innerHTML;
            });
        </script>


        <script>
            enableConfirmationOn('change', "need-confirmation", "You want to change status?", function(url, data) {

                getAjax(url, function(response) {
                    if (response.success == 'true') {
                        show_toastr('success', response.message);
                    } else {
                        show_toastr('error', response.message);
                    }

                });
            });
        </script>

        <script>
            (function() {
                const attachmentListElement = document.getElementById('lead-attachment-items');
                const attachmentCounter = document.querySelector('[data-attachment-counter]');
                const attachmentListUrl = "{{ route('leads.attachments.list', $lead->id) }}";
                const attachmentUploadUrl = "{{ route('leads.attachments.upload', $lead->id) }}";
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const audioExtensions = ['mp3', 'wav', 'ogg', 'm4a', 'aac'];

                const escapeHtml = (value = '') => value
                    .toString()
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');

                const attachmentCountFromPayload = (payload) => {
                    if (!payload) {
                        return 0;
                    }

                    if (typeof payload.count === 'number') {
                        return payload.count;
                    }

                    if (Array.isArray(payload.attachments)) {
                        return payload.attachments.length;
                    }

                    return 0;
                };

                const renderAttachments = (files) => {
                    if (!attachmentListElement) {
                        return;
                    }

                    if (!files || files.length === 0) {
                        attachmentListElement.innerHTML = '<div class="text-muted text-center py-3">No attachments available for this lead.</div>';
                        return;
                    }

                    attachmentListElement.innerHTML = files
                        .map((file) => {
                            const iconClass = audioExtensions.includes((file.ext || '').toLowerCase())
                                ? 'ri-file-music-line'
                                : 'ri-file-line';
                            const safeName = escapeHtml(file.name || 'attachment');
                            const safeSize = escapeHtml(file.size_formatted || '');
                            const safeUrl = file.url ? encodeURI(file.url) : '#';

                            return `
                                <div class="border rounded border-dashed p-2">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar-sm">
                                                <div class="avatar-title bg-light text-secondary rounded fs-24">
                                                    <i class="${iconClass}"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h5 class="fs-13 mb-1">
                                                <a href="${safeUrl}" target="_blank" rel="noreferrer" class="text-body text-truncate d-block">
                                                    ${safeName}
                                                </a>
                                            </h5>
                                            <div>${safeSize}</div>
                                        </div>
                                        <div class="flex-shrink-0 ms-2">
                                            <a href="${safeUrl}" download="${safeName}" class="btn btn-icon text-muted btn-sm fs-18 material-shadow-none">
                                                <i class="ri-download-2-line"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>`;
                        })
                        .join('');
                };

                const updateCounter = (count) => {
                    if (attachmentCounter) {
                        attachmentCounter.textContent = typeof count === 'number' ? count : attachmentCounter.textContent;
                    }
                };

                const loadAttachments = async () => {
                    try {
                        const response = await fetch(attachmentListUrl, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (!response.ok) {
                            throw new Error('Failed to load attachments');
                        }
                        const payload = await response.json();
                        renderAttachments(payload.attachments || []);
                        updateCounter(attachmentCountFromPayload(payload));
                    } catch (error) {
                        console.error('Attachment load failed', error);
                    }
                };

                const uploadAttachment = async (file) => {
                    if (!file) {
                        return;
                    }

                    const formData = new FormData();
                    formData.append('attachment', file);

                    try {
                        const response = await fetch(attachmentUploadUrl, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: formData
                        });
                        const payload = await response.json();

                        if (response.ok) {
                            renderAttachments(payload.attachments || []);
                            updateCounter(attachmentCountFromPayload(payload));
                            show_toastr('success', payload.message || 'Attachment uploaded successfully.');
                        } else {
                            show_toastr('error', payload.message || 'Attachment upload failed.');
                        }
                    } catch (error) {
                        show_toastr('error', error.message || 'Attachment upload failed.');
                        console.error('Attachment upload failed', error);
                    }
                };

                const fileInput = document.getElementById('lead-attachment-input');
                fileInput?.addEventListener('change', function() {
                    const selected = this.files[0];
                    if (selected) {
                        uploadAttachment(selected);
                        this.value = '';
                    }
                });

                loadAttachments();
            })();
        </script>
    @endsection
