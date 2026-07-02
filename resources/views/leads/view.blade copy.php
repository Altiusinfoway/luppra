@extends('layouts.app')

@section('page-css')
    <!-- quill css -->
    <link href="{{ asset('public/build/assets/libs/quill/quill.core.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('public/build/assets/libs/quill/quill.bubble.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('public/build/assets/libs/quill/quill.snow.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Leads</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
                                <li class="breadcrumb-item active">List</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end page title -->


            <!-- Content -->

            <div class="row">

                <div class="col-sm-12">
                    <div class="row">

                        <div class="col-xl-12">

                            <div class="pt-4 mb-4 mb-lg-3 pb-lg-4 card">
                                <div class="card-body">
                                <div class="row g-4">

                                    <div class="col-md-4">
                                        <div class="p-2">
                                            @php
                                                $companyName = optional($lead->customer)->company_name ?? '';
                                                $customerName = $lead->customer->name;
                                                $custLeadType = $lead->getLeadType->name ?? '';
                                            @endphp

                                            <h3 class="text-black mb-1">{{ $companyName ?? $customerName }}</h3>
                                            @if ($companyName)
                                                <p class="text-black text-opacity-75">({{$customerName}}) - {{ $custLeadType }} </p>
                                            @endif

                                            @php

                                                $getBillingAddress = optional($lead->customer)->getBillingAddress;

                                                $addressLine1 = $addressLine2 = $cityName = $stateName = $countryName = "";

                                                if($getBillingAddress){

                                                    $addressLine1 = $getBillingAddress->address_line_1;
                                                    $addressLine2 = $getBillingAddress->address_line_2;

                                                    $cityName = optional(optional($getBillingAddress)->get_city)->name;
                                                    $stateName = optional(optional($getBillingAddress)->get_state)->name;
                                                    $countryName = optional(optional($getBillingAddress)->get_country)->name;

                                                }

                                            @endphp
                                            <div class="hstack text-black-50 gap-1">
                                                <div class="me-2"><i class="ri-map-pin-user-line me-1 text-black text-opacity-75 fs-16 align-middle"></i>

                                                    {{ ($cityName) ? $cityName : '' }}
                                                    {{ ($stateName) ? ',' . $stateName : '' }}
                                                </div>
                                                <div>
                                                    <i class="ri-building-line me-1 text-black text-opacity-75 fs-16 align-middle"></i>{{ ($countryName) ? $countryName : '' }}
                                                </div>
                                            </div>
                                             <i class="ri-calendar-2-line me-1 text-black text-opacity-75 fs-16 align-middle"></i>{{ \App\Models\Utility::getDateFormated($lead->date) }}
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-md-8">
                                        <div class="row text text-black-50 text-center">
                                            @php

                                                $primary = $lead->customerPhone()->where('is_primary',1)->first();
                                                $secondary = $lead->customerPhone()->where('is_secondary',1)->first();
                                                $whatsapp = $lead->customerPhone()->where('is_whatsapp',1)->first();

                                            @endphp

                                            <div class="row">
                                                @if($primary)
                                                <div class="col-md-4 mb-3">
                                                    <div class="contact-item p-3 rounded">
                                                        <a href="tel:+{{$primary->phone}}">
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center fs-2" style="width: 40px; height: 40px;">
                                                                <i class="ri-phone-line text-white"></i>
                                                            </div>
                                                            <div>
                                                                <p class="mb-1 text-muted"><span class="badge bg-primary contact-badge">Primary</span></p>
                                                                <h6 class="mb-0">{{$primary->phone}}</h6>
                                                            </div>
                                                        </div>
                                                        </a>
                                                    </div>
                                                </div>
                                                @endif
                                                @if($secondary)
                                                <div class="col-md-4 mb-3">
                                                    <div class="contact-item p-3 rounded">
                                                        <a href="tel:+{{$secondary->phone}}">
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-info rounded-circle d-flex align-items-center justify-content-center fs-2" style="width: 40px; height: 40px;">
                                                                <i class="ri-phone-line text-white"></i>
                                                            </div>
                                                            <div>
                                                                <p class="mb-1 text-muted"><span class="badge bg-secondary contact-badge">Secondary</span></p>
                                                                <h6 class="mb-0">{{$secondary->phone}} </h6>
                                                            </div>
                                                        </div>
                                                        </a>
                                                    </div>
                                                </div>
                                                @endif
                                                @if($whatsapp)
                                                <div class="col-md-4 mb-3">
                                                    <div class="contact-item p-3 rounded">
                                                        <a href="https://api.whatsapp.com/send/?phone={{$whatsapp->phone}}" target="_blank">
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center fs-2" style="width: 40px; height: 40px;">
                                                                <i class=" ri-whatsapp-line text-white"></i>
                                                            </div>
                                                            <div>
                                                                <p class="mb-1 text-muted">WhatsApp</p>
                                                                <h6 class="mb-0">{{$whatsapp->phone}}</h6>
                                                            </div>
                                                        </div>
                                                        </a>
                                                    </div>
                                                </div>
                                                @endif

                                            </div>

                                        </div>
                                    </div>
                                    <!--end col-->

                                </div>
                                </div>
                            </div>
                                <!--end row-->

                            <div id="general" class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <div class="d-flex align-items-center">
                                                <div class="theme-avtar bg-primary badge">
                                                    <i class="mdi mdi-email fs-3"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">Email</p>
                                                    <h5 class="mb-0 text-primary">{{ $lead->email }}</h5>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col">
                                            <div class="d-flex align-items-center">
                                                <div class="theme-avtar bg-primary badge">
                                                    <i class="mdi mdi-server fs-3"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">Stage</p>

                                                    @php

                                                        $options = '<option value=""> Select Stage</option>';

                                                        foreach (\App\Models\LeadStage::all() as $stage) {

                                                            $selected = $lead->stage_id == $stage->id ? 'selected' : '';
                                                            $options .= '<option value="' . $stage->id . '" ' . $selected . '>' . $stage->name . '</option>';

                                                        }

                                                    @endphp

                                                    <select class="form-control stage-dropdown form-select need-confirmation" data-data="{id:{{$lead->id}}}" data-url="{{route('leads.stage.update', [$lead->id, '#sticky']) }}" aria-label="form-select-sm example">{!! $options !!}</select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col">
                                            <div class="d-flex align-items-center">
                                                <div class="theme-avtar bg-warning badge">
                                                    <i class="mdi mdi-calendar fs-3"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">Created</p>
                                                    <h5 class="mb-0 text-warning">
                                                        {{ \App\Models\Utility::getDateFormated($lead->date) }}</h5>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col">
                                            <div class="d-flex align-items-center">
                                                <div class="theme-avtar bg-info badge">
                                                    <i class="mdi mdi-chart-bar fs-3"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <h3 class="mb-0 text-info">{{ $precentage }} % </h3>
                                                    <div class="progress mb-0">
                                                        <div class="progress-bar bg-info" style="width: 20%;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row align-items-center justify-content-between">
                                                <div class="col-auto mb-3 mb-sm-0">
                                                    <small class="text-muted">Product</small>
                                                    <h3 class="m-0">
                                                        {{ count($lead->products()) > 0 ? $lead->products()->count() : 0 }}
                                                    </h3>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="theme-avtar bg-info badge">
                                                        <i class="mdi mdi-cart-outline  fs-3"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row align-items-center justify-content-between">
                                                <div class="col-auto mb-3 mb-sm-0">
                                                    <small class="text-muted">Source</small>
                                                    <h3 class="m-0">
                                                        {{ count($lead->sources()) ? $lead->sources()->count() : 0 }}
                                                    </h3>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="theme-avtar bg-primary badge">
                                                        <i class="mdi mdi-state-machine fs-3"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row align-items-center justify-content-between">
                                                <div class="col-auto mb-3 mb-sm-0">
                                                    <small class="text-muted">Files</small>
                                                    <h3 class="m-0">0</h3>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="theme-avtar bg-warning badge">
                                                        <i class="mdi mdi-file fs-3"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                              <div class="col-12">
                                    <div id="quote_list" class="card">
                                       <div class="card-header d-flex align-items-center">
                                            <h5 class="mb-0">Quote List</h5>

                                            <div class="ms-auto">
                                                <a href="{{ route('quotes.create', [$lead->customer_id, $lead->id]) }}"
                                                class="btn btn-sm btn-primary"
                                                title="Add">
                                                    <i class="ri-add-fill"></i>
                                                </a>
                                            </div>
                                        </div>



                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Id</th>
                                                            <th>Name</th>
                                                            <th>Date</th>
                                                            <th>Status</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($quote_list as $qtlist)
                                                            <tr>
                                                                <td>{{ $qtlist['code'] }}</td>
                                                                <td>
                                                                    {{ $qtlist->customer->name ?? '' }}
                                                                <td>
                                                                    <?php
                                                                    $data_new = \App\Models\Utility::getDateFormated($qtlist->date);
                                                                    ?>
                                                                    {{ $data_new ?? '' }}

                                                                </td>
                                                                <td>
                                                                    @if($qtlist->status == 1)
                                                                        Pending
                                                                    @elseif($qtlist->status == 2)
                                                                        Send
                                                                    @else
                                                                        Final
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <a href="{{ route('quotes.edit',$qtlist->id) }}" class=" btn btn-sm btn-primary">
                                                                        <i class="ri-pencil-fill align-bottom me-2"> Edit</i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @endforeach

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            <div id="users_products">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h5>Users</h5>
                                                    <div class="float-end">

                                                        <a data-size="md"
                                                            data-url="{{ route('leads.users', [$lead->id]) }}"
                                                            data-ajax-popup="true" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Add Users') }}"
                                                            title="Add Users" class="btn btn-sm btn-primary">
                                                            <i class="mdi mdi-plus text-white"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-hover mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Name</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @if ($lead->users->isNotEmpty())
                                                                @foreach ($lead->users as $user)
                                                                    <tr class="main-row">

                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                <div>
                                                                                    @php

                                                                                        $profile = \App\Models\Utility::get_file(
                                                                                            'uploads/avatar/',
                                                                                        );

                                                                                    @endphp
                                                                                    <img src="{{ $user->avatar ? $user->avatar : $profile . 'avatar.png' }}"
                                                                                        class="avatar-sm rounded material-shadow-none wid-40 me-3"
                                                                                        alt="{{ $user->name }}">

                                                                                </div>
                                                                                <p class="mb-0"> {{ $user->name }}</p>
                                                                            </div>
                                                                        </td>

                                                                        <td>
                                                                            @if ($lead->user_id != $user->id)
                                                                                <div class="action-btn me-2">
                                                                                    <a class="mx-3 btn btn-sm  align-items-center bs-pass-para bg-danger"
                                                                                        data-delete-popup="true"
                                                                                        data-bs-original-title="You are about to delete a lead user ?"
                                                                                        data-bs-original-description="Deleting your lead user will remove all of your information from our database."
                                                                                        data-original-title=""
                                                                                        data-url="{{ route('leads.users.delete', [$lead->id, $user->id]) }}"
                                                                                        data-method="DELETE"
                                                                                        data-cb="afterDelete"
                                                                                        href="javascript:void(0)">
                                                                                        <i
                                                                                            class="mdi mdi-trash-can-outline text-white"></i>


                                                                                    </a>
                                                                                </div>
                                                                            @else
                                                                                -
                                                                            @endif
                                                                        </td>

                                                                    </tr>
                                                                @endforeach
                                                            @endif

                                                        </tbody>
                                                    </table>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h5>Products</h5>
                                                    <div class="float-end">
                                                        <a data-size="lg"
                                                            data-url="{{ route('leads.products', [$lead->id]) }}"
                                                            data-ajax-popup="true" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Add Products') }}"
                                                            title="Add Product" class="btn btn-sm btn-primary">
                                                            <i class="mdi mdi-plus text-white"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-hover mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Name</th>
                                                                <th>Price</th>
                                                                <th>Qty</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>

                                                        <tbody>
                                                            {{-- temp error --}}
                                                            @if ($lead->product->isNotEmpty())
                                                                @foreach ($lead->product as $product)
                                                                    <tr class="main-row"
                                                                        id="row-{{ $product->pivot->id }}"
                                                                        data-uri="{{ route('leads.products.update', [$product->pivot->id]) }}"
                                                                        data-main>
                                                                        <td>
                                                                            {{ $product->name }}
                                                                        </td>
                                                                        <td class="price-view">
                                                                            {{ $product->pivot->price }}
                                                                        </td>
                                                                        <td class="qty-view">
                                                                            {{ $product->pivot->qty }}
                                                                        </td>

                                                                        <!-- Editable inputs, hidden by default -->
                                                                        <td class="price-edit d-none">
                                                                            <input type="number"
                                                                                class="form-control w-50"
                                                                                value="{{ $product->pivot->price }}" />
                                                                        </td>
                                                                        <td class="qty-edit d-none">
                                                                            <input type="number"
                                                                                class="form-control w-50"
                                                                                value="{{ $product->pivot->qty }}" />
                                                                        </td>

                                                                        <td>
                                                                            <div class="action-btn me-2">

                                                                                <a href="javascript:void(0)"
                                                                                    class="mx-3 btn btn-sm  align-items-center bs-pass-para bg-info"
                                                                                    data-bs-toggle="tooltip"
                                                                                    title="Edit"
                                                                                    onclick="editRow({{ $product->pivot->id }})"
                                                                                    id="edit-btn-{{ $product->pivot->id }}">
                                                                                    <i
                                                                                        class="mdi mdi-pencil text-white"></i>
                                                                                </a>

                                                                                <a class="mx-3 btn btn-sm  align-items-center bs-pass-para bg-danger text-white"
                                                                                    data-delete-popup="true"
                                                                                    data-bs-original-title="You are about to delete a lead product ?"
                                                                                    data-bs-original-description="Deleting your lead product will remove all of your information from our database."
                                                                                    data-original-title=""
                                                                                    data-url="{{ route('leads.products.delete', [$product->pivot->id]) }}"
                                                                                    data-method="DELETE"
                                                                                    data-cb="afterDelete"
                                                                                    href="javascript:void(0)">
                                                                                    <i
                                                                                        class="mdi mdi-trash-can-outline text-white"></i>
                                                                                </a>

                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                        </tbody>

                                                    </table>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="sources_emails">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h5>Sources</h5>
                                                    <div class="float-end">
                                                        <a data-size="md"
                                                            data-url="{{ route('leads.sources', [$lead->id]) }}"
                                                            data-ajax-popup="true" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Add Source') }}"
                                                            title="Add Source" class="btn btn-sm btn-primary">
                                                            <i class="mdi mdi-plus text-white"></i>
                                                        </a>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-hover mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Name</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>

                                                            @if ($lead->sources()->isNotEmpty())
                                                                @foreach ($lead->sources() as $source)
                                                                    <tr class="main-row">

                                                                        <td> {{ $source->name }} </td>
                                                                        <td>
                                                                            <div class="action-btn me-2">
                                                                                <a class="mx-3 btn btn-sm  align-items-center bs-pass-para bg-danger text-white"
                                                                                    data-delete-popup="true"
                                                                                    data-bs-original-title="You are about to delete a lead source ?"
                                                                                    data-bs-original-description="Deleting your lead source will remove all of your information from our database."
                                                                                    data-original-title=""
                                                                                    data-url="{{ route('leads.sources.delete', [$lead->id, $source->id]) }}"
                                                                                    data-method="DELETE"
                                                                                    data-cb="afterDelete"
                                                                                    href="javascript:void(0)">
                                                                                    <i
                                                                                        class="mdi mdi-trash-can-outline text-white"></i>
                                                                                </a>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h5>Description</h5>

                                                </div>

                                            </div>

                                            <div class="card-body">
                                                <form action="{{ route('leads.description', $lead['id']) }}"
                                                    method="post" id="leadnoteform">
                                                    @csrf

                                                    <textarea name="notes" class="form-control" rows="5">{{ $lead['notes'] }}</textarea>
                                                    <button type="submit" class="btn btn-primary mt-3">Update</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- <div class="col-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h5>Emails</h5>
                                                    <div class="float-end">
                                                        <a data-size="md"
                                                            data-url="http://localhost/kkProducts/leads/1/email"
                                                            data-ajax-popup="true" data-bs-toggle="tooltip"
                                                            title="Create Email" class="btn btn-sm btn-primary">
                                                            <i class="mdi mdi-plus text-white"></i>
                                                        </a>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="card-body">
                                                <div class="list-group list-group-flush mt-2">
                                                    <li class="text-center">
                                                        No Emails Available.!
                                                    </li>
                                                </div>
                                            </div>
                                        </div>
                                    </div> --}}
                                </div>
                            </div>

                            {{-- <div id="files" class="card">
                                <div class="card-header ">
                                    <h5>Files</h5>
                                </div>
                                <div class="card-body">
                                    <div class="col-md-12 dropzone top-5-scroll browse-file" id="dropzonewidget"></div>
                                </div>
                            </div> --}}
                             <div id="discussion_note">
                                <div class="row mt-0">
                                    <div class="col-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h5>Discussion</h5>
                                                    <div class="float-end">
                                                        {{-- <a data-size="lg" data-url="http://localhost/kkProducts/leads/1/discussions" data-ajax-popup="true" data-bs-toggle="tooltip" title="Add Message" class="btn btn-sm btn-primary">
                                                        <i class="mdi mdi-plus text-white"></i>
                                                    </a> --}}
                                                        <a data-size="md"
                                                            data-url="{{ route('leads.chat', [$lead->id]) }}"
                                                            data-ajax-popup="true" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Add Discussion') }}"
                                                            title="Add Discussion" class="btn btn-sm btn-primary">
                                                            <i class="mdi mdi-plus text-white"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>Id</th>
                                                            <th>Chat</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($leadChat as $leadCht)
                                                            <tr>
                                                                <td>{{ $leadCht['id'] }}</td>
                                                                <td>{{ $leadCht['chat'] }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                       <div class="col-6">
                                    <div id="calls" class="card">
                                        <div class="card-header">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h5>Calls</h5>

                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Id</th>
                                                            <th>Audio</th>
                                                            <th>Date-Time</th>
                                                            <th>User</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($leadCallList as $leadcall)
                                                            <tr>
                                                                <td>{{ $leadcall['id'] }}</td>
                                                                <td>
                                                                    @if (!empty($leadcall['audio']))
                                                                        <audio controls>
                                                                            <source src="{{ $leadcall['audio'] }}"
                                                                                type="audio/mpeg">
                                                                        </audio>
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </td>
                                                                <td>{{ $leadcall['date_time'] }}</td>
                                                                <td>{{ $leadcall->user['name'] }}</td>
                                                                <td>
                                                                    @if ($leadcall->status == 0)
                                                                        Not-Connected
                                                                    @else
                                                                        Connected
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                </div>
                            </div>
                            <div class="row">

                                <div class="col-6">
                                    <div id="activity" class="card">
                                        <div class="card-header">
                                            <h5>Activity</h5>
                                        </div>
                                        <div class="card-body ">

                                            <div class="row leads-scroll">
                                                <ul class="event-cards list-group list-group-flush w-100">

                                                    @foreach ($leadActivity as $leadAct)
                                                        <li class="list-group-item card  m-2">
                                                            <div class="row align-items-center justify-content-between">
                                                                <div class="col-auto mb-3 mb-sm-0">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="theme-avtar bg-primary badge">
                                                                            <i
                                                                                class="mdi mdi-account-arrow-right-outline"></i>
                                                                        </div>
                                                                        <div class="ms-3">
                                                                            <span
                                                                                class="text-dark text-sm">{{ $leadAct['action'] }}
                                                                                - {{ $leadAct->users->name }}</span>
                                                                            <h6 class="m-0">{{ $leadAct['desc'] }}</h6>
                                                                            <small
                                                                                class="text-muted">{{ $leadAct['date_time'] }}</small>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </li>
                                                    @endforeach

                                                </ul>
                                            </div>

                                        </div>
                                    </div>
                                </div>



                            </div>


                        </div>
                    </div>
                </div>

            </div><!--end row-->
            <!-- end Content -->


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
            if (priceView && qtyView && priceEdit && qtyEdit) {
                const isEditing = priceView.classList.contains('d-none');

                if (isEditing) {
                    // Save
                    const newPrice = priceEdit.querySelector('input').value;
                    const newQty = qtyEdit.querySelector('input').value;


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
                    editBtn.innerHTML = '<i class="mdi mdi-pencil text-white"></i>';
                } else {
                    // Edit
                    priceView.classList.add('d-none');
                    qtyView.classList.add('d-none');
                    priceEdit.classList.remove('d-none');
                    qtyEdit.classList.remove('d-none');
                    editBtn.innerHTML = '<i class="mdi mdi-content-save text-white"></i>';
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
    enableConfirmationOn('change',"need-confirmation","You want to change status?", function(url, data){

        getAjax(url, function(response){
            if(response.success == 'true'){
                show_toastr('success',response.message);
            } else {
                show_toastr('error',response.message);
            }

        });
    });

</script>
@endsection
