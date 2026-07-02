@extends('layouts.app')

@section('content')
 @include('leads.lead_import_all_section')
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

            <div class="card">
                <div class="card-body p-2">
                    <div class="row align-items-center ">

                        <div class="col-lg-3">
                            <div class="search-box">
                                <input type="text" class="form-control form-control-sm search" id="search-task-options"
                                    placeholder="Search for Customer Name,Phone">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                        </div>

                        <div class="col-lg-9">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="javascript:void(0);"
                                                class="btn btn-sm btn-success add-btn"
                                                data-size="lg" data-url="{{ route('leads.create') }}"
                                                data-ajax-popup="true"
                                                data-bs-original-title="{{ __('Add Lead') }}"><i
                                                    class="ri-add-circle-line me-1"></i> Add leads</a>



                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-primary btn-sm dropdown" type="button" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="ri-file-download-line align-bottom "> Import</i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        {{-- <li>
                                            <a href="javascript:void(0)" class="dropdown-item edit-item-btn"
                                                data-bs-toggle="modal" data-bs-target="#indiamartModel">
                                                <i class="ri-octagon-fill align-bottom me-2 text-muted"></i> India Mart
                                            </a>
                                        </li> --}}
                                        {{-- <li>
                                            <a class="dropdown-item remove-item-btn" href="javascript:void(0)"
                                                data-bs-toggle="modal" data-bs-target="#facebookModel">
                                                <i class="ri-octagon-fill align-bottom me-2 text-muted"></i> Facebook
                                            </a>
                                        </li> --}}

                                        <li>
                                            <a class="dropdown-item remove-item-btn"
                                                href="{{ route('leads.upload_excel_lead') }}">
                                                <i class="ri-octagon-fill align-bottom me-2 text-muted"></i> Upload data
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="offcanvas"
                                    href="#offcanvasExample">
                                    <i class="ri-filter-3-line align-bottom me-1"></i> Fliters
                                </button>
                                <a href="{{ route('leads.list') }}"
                                    class="btn btn-danger btn-icon waves-effect waves-light btn-sm">
                                    <i class="bx bx-menu fs-3"></i>
                                </a>
                            </div>
                        </div>

                        <!-- <div class="col-auto ms-sm-auto">
                                                                                                                                                                        <div class="avatar-group" id="newMembar">

                                                                                                                                                                            <a href="javascript: void(0);" class="avatar-group-item material-shadow"
                                                                                                                                                                                data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top"
                                                                                                                                                                                title="Nancy">
                                                                                                                                                                                <img src="https://kk.asiantechnocast.com/assets/images/users/avatar-5.jpg" alt=""
                                                                                                                                                                                    class="rounded-circle avatar-xs">
                                                                                                                                                                            </a>
                                                                                                                                                                            <a href="javascript: void(0);" class="avatar-group-item material-shadow"
                                                                                                                                                                                data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top"
                                                                                                                                                                                title="Frank">
                                                                                                                                                                                <img src="https://kk.asiantechnocast.com/assets/images/users/avatar-3.jpg" alt=""
                                                                                                                                                                                    class="rounded-circle avatar-xs">
                                                                                                                                                                            </a>
                                                                                                                                                                            <a href="javascript: void(0);" class="avatar-group-item material-shadow"
                                                                                                                                                                                data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top"
                                                                                                                                                                                title="Tonya">
                                                                                                                                                                                <img src="https://kk.asiantechnocast.com/assets/images/users/avatar-10.jpg" alt=""
                                                                                                                                                                                    class="rounded-circle avatar-xs">
                                                                                                                                                                            </a>
                                                                                                                                                                            <a href="javascript: void(0);" class="avatar-group-item material-shadow"
                                                                                                                                                                                data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top"
                                                                                                                                                                                title="Thomas">
                                                                                                                                                                                <img src="https://kk.asiantechnocast.com/assets/images/users/avatar-8.jpg" alt=""
                                                                                                                                                                                    class="rounded-circle avatar-xs">
                                                                                                                                                                            </a>
                                                                                                                                                                            <a href="javascript: void(0);" class="avatar-group-item material-shadow"
                                                                                                                                                                                data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top"
                                                                                                                                                                                title="Herbert">
                                                                                                                                                                                <img src="https://kk.asiantechnocast.com/assets/images/users/avatar-2.jpg" alt=""
                                                                                                                                                                                    class="rounded-circle avatar-xs">
                                                                                                                                                                            </a>
                                                                                                                                                                            <a href="#addmemberModal" data-bs-toggle="modal"
                                                                                                                                                                                class="avatar-group-item material-shadow">
                                                                                                                                                                                <div class="avatar-xs">
                                                                                                                                                                                    <div class="avatar-title rounded-circle">
                                                                                                                                                                                        +
                                                                                                                                                                                    </div>
                                                                                                                                                                                </div>
                                                                                                                                                                            </a>
                                                                                                                                                                        </div>
                                                                                                                                                                    </div> -->
                        <!--end col-->
                    </div>
                    <!--end row-->
                </div>
                <!--end card-body-->
            </div>
            <!--end card-->





            <!-- ---------------------- new --- -->
            <div class="tasks-board mb-3" id="kanbanboard">


                @php
                    $json = [];
                    foreach ($leadStagies as $lead_stage) {
                        $json[] = 'task-list-' . $lead_stage->id;
                    }
                @endphp

                @foreach ($leadStagies as $lead_stage)
                    @php
                        //before code
                        // $hasFilter = request()->filled('date') ||
                        //  request()->filled('sources') ||
                        //  request()->filled('products') ||
                        //  request()->filled('stage');

                        // $leads = $hasFilter
                        //     ? $lead_data->where('stage_id', $lead_stage->id)
                        //     : $lead_stage->leads;

                        $hasFilter =
                            request()->filled('date') ||
                            request()->filled('sources') ||
                            request()->filled('products') ||
                            request()->filled('stage') ||
                            request()->filled('lead_type_filter');

                        if (\Auth::user()->type == 'Sales') {
                            $userLeadIds = $assignedLeadIds ?? [];

                            $leads = $hasFilter
                                ? $lead_data->where('stage_id', $lead_stage->id)->whereIn('id', $userLeadIds)
                                : $lead_stage->leads->whereIn('id', $userLeadIds);
                        } else {
                            $leads = $hasFilter ? $lead_data->where('stage_id', $lead_stage->id) : $lead_stage->leads;
                        }

                    @endphp

                    <div class="tasks-list">
                        <div class="d-flex mb-3">
                            <div class="flex-grow-1">
                                <h6 class="fs-14 text-uppercase fw-semibold mb-0">{{ $lead_stage->name }} <small
                                        class="badge bg-success align-bottom ms-1 totaltask-badge">{{ count($leads) }}</small>
                                </h6>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="dropdown card-header-dropdown">
                                    <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <span class="fw-medium text-muted fs-12">Priority<i
                                                class="mdi mdi-chevron-down ms-1"></i></span>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="#">Priority</a>
                                        <a class="dropdown-item" href="#">Date Added</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div data-simplebar class="tasks-wrapper px-3 mx-n3">
                            <div id="{{ \Str::slug($lead_stage->name) }}-task" class="tasks"
                                data-list-key='{{ $lead_stage->id }}'>
                                @foreach ($leads as $lead)
                                    <div class="card tasks-box mb-2" data-box-key='{{ $lead->id }}'>
                                        <div class="card-body p-2 px-3">
                                            <!-- Click Header -->

                                            <a class="d-flex align-items-center justify-content-between collapsed text-dark text-decoration-none"
                                                data-bs-toggle="collapse" href="#leadCollapse{{ $lead->id }}"
                                                role="button" aria-expanded="false"
                                                aria-controls="leadCollapse{{ $lead->id }}">

                                                <div class="flex-grow-1">
                                                    <h6 class="fs-15 mb-2 text-truncate text-wrap">
                                                        {{ ucwords(strtolower($lead->name)) }}</h6>

                                                </div>
                                                <div class="flex-shrink-0">
                                                    <i class="ri-arrow-down-s-line fs-20 text-muted"></i>
                                                </div>
                                            </a>
                                            <div class="row">
                                                <div class="col">
                                                    <span class="badge bg-primary me-1 mb-1"> <i
                                                            class="ri-account-circle-fill"></i>
                                                        {{ $lead?->user?->name }}</span>
                                                </div>
                                            </div>

                                            <table class="w-100">
                                                <tr>
                                                    <td>
                                                        <p class="text-muted mb-0 small"><i class="ri-calendar-fill"></i>
                                                            {{ App\Models\Utility::getDateFormated($lead->date) }}</p>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $primary = $lead
                                                                ->customerPhone()
                                                                ->where('is_primary', 1)
                                                                ->first();
                                                            $whatsapp = $lead
                                                                ->customerPhone()
                                                                ->where('is_whatsapp', 1)
                                                                ->first();
                                                        @endphp
                                                        <p class="text-muted mb-0 small"><i class=" ri-phone-fill"></i>
                                                            {{ $primary?->phone }}</p>
                                                    </td>
                                                    <td class="text-end">
                                                        @if($device && $whatsapp?->phone)
                                                            @php
                                                                $rawPhone = preg_replace('/\D+/', '', (string) $whatsapp->phone);
                                                                if (\Illuminate\Support\Str::startsWith($rawPhone, '91') && strlen($rawPhone) === 12) {
                                                                    $chatPhone = $rawPhone;
                                                                } elseif (strlen($rawPhone) === 10) {
                                                                    $chatPhone = '91' . $rawPhone;
                                                                } elseif (\Illuminate\Support\Str::startsWith($rawPhone, '0') && strlen($rawPhone) === 11) {
                                                                    $chatPhone = '91' . substr($rawPhone, 1);
                                                                } else {
                                                                    $chatPhone = $rawPhone;
                                                                }
                                                            @endphp
                                                            @if(!empty($chatPhone))
                                                                <a href="{{ url('device/chats/'.$device->uuid).'?phone='.$chatPhone }}"
                                                                    class="text-success js-wa-chat-entry"
                                                                    title="WhatsApp Chat"
                                                                    data-chat-url="{{ url('device/chats/'.$device->uuid).'?phone='.$chatPhone }}"
                                                                    data-qr-url="{{ route('device.scan', $device->uuid) }}"
                                                                    data-device-uuid="{{ $device->uuid }}">
                                                                    <i class="ri-whatsapp-line fs-18"></i>
                                                                </a>
                                                            @endif
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>

                                                    <td>
                                                        <p class="text-muted mb-0 small"><i class="ri-map-pin-2-fill"></i>
                                                            {{ $lead?->customer?->getBillingAddress?->get_city?->name ?? '' }},
                                                            {{ $lead?->customer?->getBillingAddress?->get_state?->name ?? '' }}
                                                        </p>
                                                    </td>
                                                </tr>
                                            </table>

                                        </div>

                                        <!-- Collapsible Section -->
                                        <div class="border-top border-top-dashed collapse"
                                            id="leadCollapse{{ $lead->id }}">
                                            <div class="card-body pt-2">
                                                <div class="mb-3">
                                                    <p class="text-muted m-0" id="note-{{ $lead->id }}">
                                                        {{ Str::words($lead->notes, 9, '...') }}
                                                    </p>
                                                    @if (Str::wordCount($lead->notes) > 9)
                                                        <a href="javascript:void(0);" class="text-primary"
                                                            onclick="toggleReadMore({{ $lead->id }}, '{{ e($lead->notes) }}')">
                                                            Read more
                                                        </a>
                                                    @endif
                                                </div>
                                                <table class="w-100 mt-3 table table-bordered text-center">

                                                    {{-- Reminding Date --}}
                                                    @if (!empty($lead['next_contact_date']))
                                                        <tr>
                                                            <td class="p-2">
                                                                <p class="mb-0"><i class="ri-alarm-fill"></i></p>
                                                            </td>
                                                            <td class="p-2">
                                                                <p class="text-muted mb-0 small">
                                                                    {{ App\Models\Utility::getDateFormated($lead['next_contact_date']) }}
                                                                </p>
                                                            </td>
                                                        </tr>
                                                    @endif

                                                    {{-- Source --}}
                                                    @php
                                                        $ids = !empty($lead['sources'])
                                                            ? explode(',', $lead['sources'])
                                                            : [];
                                                        $sourceNames = count($ids)
                                                            ? \App\Models\LeadSource::withTrashed()->whereIn('id', $ids)
                                                                ->pluck('name')
                                                                ->toArray()
                                                            : [];
                                                    @endphp

                                                    @if (!empty($sourceNames))
                                                        <tr>
                                                            <td class="p-2">
                                                                <p class="mb-0"><i class=" ri-open-source-fill"></i></p>
                                                            </td>
                                                            <td class="p-2">
                                                                <p class="text-muted mb-0 small">
                                                                    {{ implode(', ', $sourceNames) }}
                                                                </p>
                                                            </td>
                                                        </tr>
                                                    @endif

                                                    {{-- Products --}}
                                                    @php
                                                        $productIds = !empty($lead['products'])
                                                            ? explode(',', $lead['products'])
                                                            : [];
                                                        $productNames = count($productIds)
                                                            ? \App\Models\Products::whereIn('id', $productIds)
                                                                ->pluck('name')
                                                                ->toArray()
                                                            : [];

                                                        $leadProVal = \App\Models\LeadProducts::where(
                                                            'lead_id',
                                                            $lead['id'],
                                                        )
                                                            ->pluck('price')
                                                            ->toArray();
                                                        $sum = array_sum($leadProVal);
                                                    @endphp

                                                    @if (!empty($productNames))
                                                        <tr>
                                                            <td class="p-2">
                                                                <p class="mb-0"><i class=" ri-product-hunt-fill"></i>
                                                                </p>
                                                            </td>
                                                            <td class="p-2">
                                                                <p class="text-muted mb-0 small">
                                                                    {{ implode(', ', $productNames) }}
                                                                </p>
                                                            </td>
                                                        </tr>
                                                    @endif

                                                </table>

                                            </div>

                                            <!-- Footer Buttons -->
                                            <div class="card-footer border-top-dashed hstack gap-2 justify-content-center">
                                                <ul class="list-inline mb-0">
                                                    @if ($whatsapp)
                                                        <li class="list-inline-item avatar-xs">
                                                            <a href="https://api.whatsapp.com/send/?phone={{ $whatsapp?->phone }}"
                                                                target="_blank"
                                                                class="avatar-title bg-success-subtle text-success fs-15 rounded">
                                                                <i class="ri-whatsapp-fill"></i>
                                                            </a>
                                                        </li>
                                                    @endif

                                                    @if ($primary)
                                                        <li class="list-inline-item avatar-xs">
                                                            <a href="tel:{{ $primary?->phone }}"
                                                                class="avatar-title bg-success-subtle text-success fs-15 rounded">
                                                                <i class="ri-phone-fill"></i>
                                                            </a>
                                                        </li>
                                                    @endif



                                                    <li class="list-inline-item avatar-xs">
                                                        <a href="javascript:void(0);" data-size="lg"
                                                            data-url="{{ URL::to('leads/' . $lead->id . '/edit') }}"
                                                            data-ajax-popup="true"
                                                            data-bs-original-title="{{ __('Edit Lead') }}"
                                                            class="avatar-title bg-danger-subtle text-danger fs-15 rounded">
                                                            <i class="ri-edit-2-fill"></i>
                                                        </a>

                                                    </li>
                                                    <li class="list-inline-item avatar-xs">
                                                        <a href="{{ route('leads.view', [$lead->id]) }}"
                                                            class="avatar-title bg-warning-subtle text-warning fs-15 rounded">
                                                            <i class="ri-eye-fill"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!--end card-->
                                @endforeach
                            </div>
                            <!--end tasks-->
                        </div>

                    </div>
                    <!--end tasks-list-->
                @endforeach

            </div>
            <!--end task-board-->
        </div>
    </div>





    <!-- filter model -->
    <div class="offcanvas offcanvas-end fade" tabindex="-1" id="offcanvasExample"
        aria-labelledby="offcanvasExampleLabel" aria-modal="true" role="dialog">
        <div class="offcanvas-header bg-light">
            <h5 class="offcanvas-title" id="offcanvasExampleLabel">Leads Fliters</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        {{ Form::open(['route' => 'leads.index', 'method' => 'get', 'id' => 'lead-filter-form', 'class' => 'form-inline d-flex flex-column justify-content-end h-100']) }}
        <div class="offcanvas-body">
            <div class="mb-2">
                <label for="country-select" class="form-label text-muted text-uppercase fw-semibold">Source</label>
                <div class="">
                    {{ Form::select('sources[]', $sources, request('sources'), ['class' => 'form-control choices-select', 'multiple' => true, 'data-choices', 'data-choices-removeItem']) }}
                </div>
            </div>

            <div class="mb-2">
                <label for="country-select" class="form-label text-muted text-uppercase fw-semibold">Product</label>
                <div class="">
                    {{ Form::select('products[]', $products, request('products'), ['class' => 'form-control choices-select', 'multiple' => true, 'data-choices', 'data-choices-removeItem']) }}
                </div>
            </div>

            <div class="mb-2">
                <label for="country-select" class="form-label text-muted text-uppercase fw-semibold">Stage</label>
                <div class="">
                    {{ Form::select('stage', ['' => 'Select Stage'] + $stages->toArray(), request('stage'), ['class' => 'form-select mb-3', 'id' => 'stage_id', 'aria-label' => 'Select stage Source']) }}
                </div>
            </div>

            <div class="mb-2">
                <label for="country-select" class="form-label text-muted text-uppercase fw-semibold">Date</label>
                <div class="">
                    {{ Form::text('date', request('date'), ['class' => 'form-control datepicker-range', 'id' => 'datepicker-range', 'data-provider' => 'flatpickr', 'data-range' => 'true']) }}
                </div>
            </div>

            <div class="mb-2">
                <label for="lead_type-select" class="form-label text-muted text-uppercase fw-semibold">Lead
                    Type</label>
                <div class="">
                    {{ Form::select('lead_type_filter', ['' => 'Select Lead Type'] + $lead_type_list->toArray(), request('lead_type_filter'), ['class' => 'form-control ', 'data-choices', 'data-choices-removeItem']) }}
                </div>
            </div>
        </div>

        <div class="offcanvas-footer border-top p-3 text-center hstack gap-2">
            <a href="{{ route('leads.index') }}" class="btn btn-light w-100">Clear Filter</a>
            <button type="submit" class="btn btn-success w-100">Filters</button>
        </div>
        {{ Form::close() }}
    </div>
    <!-- End filter model -->

    {{-- @include('leads/create') --}}
@endsection

@section('scripts')
    <!--taks-kanban-->
    <!-- <script src="{{ asset('public/build/assets/js/pages/tasks-kanban.init.js') }}"></script> -->

    <script>
        var myModalEl, kanbanboard, scroll, addNewBoard, addMember, profileField, reader, tasks_list = Array.from(document
            .querySelectorAll("#kanbanboard .tasks"));

        function noTaskImage() {
            Array.from(document.querySelectorAll("#kanbanboard .tasks-list")).forEach((function(e) {
                0 < e.querySelectorAll(".tasks-box").length ? e.querySelector(".tasks").classList.remove(
                    "noTask") : e.querySelector(".tasks").classList.add("noTask")
            }))
        }

        function taskCounter() {
            (task_lists = document.querySelectorAll("#kanbanboard .tasks-list")) && Array.from(task_lists).forEach((
                function(e) {
                    tasks = e.getElementsByClassName("tasks"), Array.from(tasks).forEach((function(e) {
                        task_box = e.getElementsByClassName("tasks-box"), task_counted = task_box.length
                    })), badge = e.querySelector(".totaltask-badge").innerText = "", badge = e.querySelector(
                        ".totaltask-badge").innerText = task_counted
                }))
        }

        function newKanbanbaord() {
            var e = document.getElementById("boardName").value,
                a = Math.floor(100 * Math.random()),
                t = "review_task_" + a;
            kanbanlisthtml = '<div class="tasks-list" id=remove_item_' + a +
                '><div class="d-flex mb-3"><div class="flex-grow-1"><h6 class="fs-14 text-uppercase fw-semibold mb-0">' +
                e +
                '<small class="badge bg-success align-bottom ms-1 totaltask-badge">0</small></h6></div><div class="flex-shrink-0"><div class="dropdown card-header-dropdown"><a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="fw-medium text-muted fs-12">Priority<i class="mdi mdi-chevron-down ms-1"></i></span></a><div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="#">Priority</a><a class="dropdown-item" href="#">Date Added</a></div></div></div></div><div data-simplebar class="tasks-wrapper px-3 mx-n3"><div class="tasks" id="' +
                t +
                '" ></div></div><div class="my-3"><button class="btn btn-soft-info w-100" data-bs-toggle="modal" data-bs-target="#creatertaskModal">Add More</button></div></div>',
                document.getElementById("kanbanboard").insertAdjacentHTML("beforeend", kanbanlisthtml), document
                .getElementById("addBoardBtn-close").click(), noTaskImage(), taskCounter(), drake.destroy(), tasks_list
                .push(document.getElementById(t)), drake = dragula(tasks_list).on("out", (function(e, a) {
                    noTaskImage(), taskCounter()
                })), document.getElementById("boardName").value = ""
        }

        function newMemberAdd() {
            var e = document.getElementById("firstnameInput").value,
                a = localStorage.getItem("kanbanboard-member");
            newMembar =
                '<a href="javascript: void(0);" class="avatar-group-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="' +
                e + '">' + a + "</a>", document.getElementById("newMembar").insertAdjacentHTML("afterbegin", newMembar),
                document.getElementById("btn-close-member").click()
        }
        tasks_list && ((myModalEl = document.getElementById("deleteRecordModal")) && myModalEl.addEventListener(
                "show.bs.modal", (function(e) {
                    document.getElementById("delete-record").addEventListener("click", (function() {
                        e.relatedTarget.closest(".tasks-box").remove(), document.getElementById(
                            "delete-btn-close").click(), taskCounter()
                    }))
                })), drake = dragula(tasks_list).on("drag", (function(e) {
                e.className = e.className.replace("ex-moved", "")
            })).on("drop", (function(e, a, t, d) {
                e.className += " ex-moved";
                const s = e.getAttribute("data-box-key"),
                    o = a.getAttribute("data-list-key");
                getAjax("{{ route('leads.stage.update', ['LID', 'LSID']) }}".replace("LID", s).replace("LSID",
                    o))
            })).on("over", (function(e, a) {
                a.className += " ex-over"
            })).on("out", (function(e, a) {
                a.className = a.className.replace("ex-over", ""), noTaskImage(), taskCounter()
            })), (kanbanboard = document.querySelectorAll("#kanbanboard")) && (scroll = autoScroll([document
                .querySelector("#kanbanboard")
            ], {
                margin: 20,
                maxSpeed: 100,
                scrollWhenOutside: !0,
                autoScroll: function() {
                    return this.down && drake.dragging
                }
            })), (addNewBoard = document.getElementById("addNewBoard")) && document.getElementById("addNewBoard")
            .addEventListener("click", newKanbanbaord), addMember = document.getElementById("addMember")) && (document
            .getElementById("addMember").addEventListener("click", newMemberAdd), profileField = document
            .getElementById("profileimgInput"), reader = new FileReader, profileField.addEventListener("change", (
                function(e) {
                    reader.readAsDataURL(profileField.files[0]), reader.onload = function() {
                        var e = reader.result;
                        localStorage.setItem("kanbanboard-member", '<img src="' + e +
                            '" alt="profile" class="rounded-circle avatar-xs">')
                    }
                })));
    </script>

    <script>
        document.getElementById('search-task-options').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();

            document.querySelectorAll('#kanbanboard .tasks-box').forEach(function(task) {
                // Find text fields to search
                const title = task.querySelector('.fs-15') ? task.querySelector('.fs-15').innerText
                    .toLowerCase() : '';
                const notes = task.querySelector('.text-muted') ? task.querySelector('.text-muted')
                    .innerText.toLowerCase() : '';
                const phone = task.querySelector('.ri-phone-fill') ? task.querySelector('.ri-phone-fill')
                    .parentElement.innerText.toLowerCase() : '';
                const city = task.querySelector('.ri-map-pin-2-fill') ? task.querySelector(
                    '.ri-map-pin-2-fill').parentElement.innerText.toLowerCase() : '';

                // Check if any of these match the search
                if (
                    title.includes(searchText) ||
                    notes.includes(searchText) ||
                    phone.includes(searchText) ||
                    city.includes(searchText)
                ) {
                    task.style.display = 'block';
                } else {
                    task.style.display = 'none';
                }
            });
        });
    </script>

    <script src="{{ asset('public/build/assets/js/pages/user/whatsapp-chat-entry.js') }}"></script>
@endsection
