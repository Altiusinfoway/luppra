@extends('layouts.app')

@section('page-css')
<style>
    .workflow-suite {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
    }
    .workflow-suite .hero-shell,
    .workflow-suite .toolbar-shell,
    .workflow-suite .summary-card {
        border: 1px solid rgba(255, 255, 255, 0.78);
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
    }
    .workflow-suite .hero-shell {
        border-radius: 28px;
        background:
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
            radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
            linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
    }
    .workflow-suite .toolbar-shell,
    .workflow-suite .summary-card {
        border-radius: 22px;
    }
    .workflow-suite .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.76);
        border: 1px solid #dbeafe;
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .workflow-suite .hero-title {
        font-size: clamp(2rem, 3vw, 2.7rem);
        line-height: 1.05;
        letter-spacing: -0.04em;
        font-weight: 800;
        margin: 1rem 0 .45rem;
        color: #0f172a;
    }
    .workflow-suite .hero-subtitle,
    .workflow-suite .toolbar-note {
        color: #64748b;
    }
    .workflow-suite .hero-action-btn {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border-radius: 14px;
        font-weight: 700;
        padding: .7rem 1rem;
    }
    .workflow-suite .summary-card .label {
        color: #64748b;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: .08em;
        font-weight: 800;
        margin-bottom: .45rem;
    }
    .workflow-suite .summary-card h3 {
        margin: 0;
        font-size: 1.7rem;
        line-height: 1.1;
        letter-spacing: -0.03em;
        font-weight: 800;
        color: #0f172a;
    }
    .workflow-suite .search-shell {
        position: relative;
    }
    .workflow-suite .search-shell .search-icon {
        position: absolute;
        top: 50%;
        right: 14px;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
    }
    .workflow-suite .board-shell {
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.72);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        padding: 1rem;
    }
    .workflow-suite .tasks-list {
        background: rgba(255, 255, 255, 0.86);
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 20px;
        padding: 1rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }
    .workflow-suite .tasks-board {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 1rem;
        align-items: start;
    }
    .workflow-suite .tasks-list {
        min-height: 100%;
    }
    .workflow-suite .stage-heading {
        display: flex;
        align-items: center;
        gap: .6rem;
        margin: 0;
        color: #0f172a;
        font-size: .82rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .workflow-suite .stage-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 28px;
        padding: 0 10px;
        border-radius: 999px;
        background: #dcfce7;
        color: #166534;
        font-size: .75rem;
        font-weight: 800;
    }
    .workflow-suite .tasks-wrapper {
        max-height: 72vh;
        padding-top: .25rem;
    }
    .workflow-suite .tasks-box {
        border: 1px solid #e2e8f0 !important;
        border-radius: 18px !important;
        background: #ffffff !important;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
        overflow: hidden;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .workflow-suite .tasks-box:hover {
        transform: translateY(-2px);
        border-color: #bfdbfe !important;
        box-shadow: 0 18px 34px rgba(37, 99, 235, 0.12);
    }
    .workflow-suite .lead-toggle {
        gap: .9rem;
    }
    .workflow-suite .lead-title {
        margin-bottom: .1rem;
        color: #0f172a;
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.35;
    }
    .workflow-suite .lead-owner-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .42rem .65rem;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: .76rem;
        font-weight: 700;
    }
    .workflow-suite .lead-meta-grid {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 .55rem;
    }
    .workflow-suite .lead-meta-grid td {
        vertical-align: top;
    }
    .workflow-suite .lead-meta {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        color: #64748b;
        font-size: .8rem;
    }
    .workflow-suite .lead-meta i {
        color: #2563eb;
    }
    .workflow-suite .lead-chat-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 999px;
        background: #ecfdf5;
        color: #16a34a !important;
    }
    .workflow-suite .lead-collapse {
        border-top: 1px solid #e2e8f0;
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.75) 0%, rgba(255, 255, 255, 1) 100%);
    }
    .workflow-suite .lead-note {
        color: #475569;
        line-height: 1.55;
    }
    .workflow-suite .lead-detail-table {
        margin-top: .5rem !important;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
    }
    .workflow-suite .lead-detail-table td {
        background: transparent !important;
    }
    .workflow-suite .lead-actions {
        padding: .85rem 1rem 1rem;
        border-top: 1px solid #e2e8f0;
        background: rgba(255, 255, 255, 0.95);
    }
    .workflow-suite .lead-action-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 12px;
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .workflow-suite .lead-action-link:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.08);
    }
    .workflow-suite .filters-panel .offcanvas-header {
        border-bottom: 1px solid #e2e8f0;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }
    .workflow-suite .filters-panel .offcanvas-title {
        font-weight: 800;
        letter-spacing: -0.02em;
    }
    .workflow-suite .filters-panel .offcanvas-body {
        background: #f8fafc;
    }
    .workflow-suite .filters-panel .offcanvas-footer {
        background: #ffffff;
    }
    @media (max-width: 991.98px) {
        .workflow-suite .tasks-board {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
 @include('leads.lead_import_all_section')
    @php
        $totalLeadCount = 0;
        $stagesWithLeads = 0;
        $todayLeadCount = 0;
        foreach ($leadStagies as $leadStageItem) {
            $stageLeadCount = $leadStageItem->leads->count();
            $totalLeadCount += $stageLeadCount;
            if ($stageLeadCount > 0) {
                $stagesWithLeads++;
            }
            $todayLeadCount += $leadStageItem->leads->where('date', now()->toDateString())->count();
        }
        $filteredLeadCount = isset($lead_data) ? $lead_data->count() : $totalLeadCount;
    @endphp
    <div class="page-content workflow-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-8">
                                    <span class="hero-eyebrow">Pipeline Dashboard</span>
                                    <h1 class="hero-title">Leads</h1>
                                    <p class="hero-subtitle mb-0">Review incoming pipeline, move opportunities across stages, and keep team follow-up work visible from one cleaner workspace.</p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-lg-end flex-wrap gap-2">
                                        <a href="javascript:void(0);"
                                            class="btn btn-primary hero-action-btn add-btn"
                                            data-size="lg" data-url="{{ route('leads.create') }}"
                                            data-ajax-popup="true"
                                            data-bs-original-title="{{ __('Add Lead') }}">
                                            <i class="ri-add-circle-line"></i> Add Lead
                                        </a>
                                        <a href="{{ route('leads.list') }}" class="btn btn-light hero-action-btn">
                                            <i class="bx bx-menu fs-5"></i> Table View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="summary-card h-100">
                        <div class="card-body">
                            <div class="label">Total Leads</div>
                            <h3>{{ number_format($totalLeadCount) }}</h3>
                            <p class="text-muted mb-0 mt-2">All active opportunities in the current board.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="summary-card h-100">
                        <div class="card-body">
                            <div class="label">Visible Leads</div>
                            <h3>{{ number_format($filteredLeadCount) }}</h3>
                            <p class="text-muted mb-0 mt-2">Rows matching the current filters and board scope.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="summary-card h-100">
                        <div class="card-body">
                            <div class="label">Active Stages</div>
                            <h3>{{ number_format($stagesWithLeads) }}</h3>
                            <p class="text-muted mb-0 mt-2">Pipeline stages currently holding at least one lead.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="summary-card h-100">
                        <div class="card-body">
                            <div class="label">Added Today</div>
                            <h3>{{ number_format($todayLeadCount) }}</h3>
                            <p class="text-muted mb-0 mt-2">New leads created on {{ now()->format('d M Y') }}.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card toolbar-shell mb-4">
                <div class="card-body p-2">
                    <div class="row align-items-center ">

                        <div class="col-lg-3">
                            <div class="search-shell">
                                <input type="text" class="form-control form-control-sm search" id="search-task-options"
                                    placeholder="Search for Customer Name,Phone">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                        </div>

                        <div class="col-lg-9">
                            <div class="d-flex justify-content-end gap-2">
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
                                    <i class="ri-filter-3-line align-bottom me-1"></i> Filters
                                </button>
                            </div>
                        </div>

                        <!--end col-->
                    </div>
                    <!--end row-->
                </div>
                <!--end card-body-->
                </div>
                <div class="px-3 pb-3 toolbar-note small">Switch filters, import lead data, or continue managing opportunities directly in the kanban board below.</div>
            </div>





            <!-- ---------------------- new --- -->
            <div class="board-shell">
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
                                <h6 class="stage-heading">{{ $lead_stage->name }} <small
                                        class="stage-badge totaltask-badge">{{ count($leads) }}</small>
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

                                            <a class="d-flex align-items-center justify-content-between collapsed text-dark text-decoration-none lead-toggle"
                                                data-bs-toggle="collapse" href="#leadCollapse{{ $lead->id }}"
                                                role="button" aria-expanded="false"
                                                aria-controls="leadCollapse{{ $lead->id }}">

                                                <div class="flex-grow-1">
                                                    <h6 class="lead-title text-truncate text-wrap">
                                                        {{ ucwords(strtolower($lead->name)) }}</h6>

                                                </div>
                                                <div class="flex-shrink-0">
                                                    <i class="ri-arrow-down-s-line fs-20 text-muted"></i>
                                                </div>
                                            </a>
                                            <div class="row">
                                                <div class="col">
                                                    <span class="lead-owner-badge me-1 mb-1"> <i
                                                            class="ri-account-circle-fill"></i>
                                                        {{ $lead?->user?->name }}</span>
                                                </div>
                                            </div>

                                            <table class="lead-meta-grid">
                                                <tr>
                                                    <td>
                                                        <p class="lead-meta mb-0"><i class="ri-calendar-fill"></i>
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
                                                        <p class="lead-meta mb-0"><i class=" ri-phone-fill"></i>
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
                                                                    class="lead-chat-link js-wa-chat-entry"
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
                                                        <p class="lead-meta mb-0"><i class="ri-map-pin-2-fill"></i>
                                                            {{ $lead?->customer?->getBillingAddress?->get_city?->name ?? '' }},
                                                            {{ $lead?->customer?->getBillingAddress?->get_state?->name ?? '' }}
                                                        </p>
                                                    </td>
                                                </tr>
                                            </table>

                                        </div>

                                        <!-- Collapsible Section -->
                                        <div class="lead-collapse collapse"
                                            id="leadCollapse{{ $lead->id }}">
                                            <div class="card-body pt-2">
                                                <div class="mb-3">
                                                    <p class="lead-note m-0" id="note-{{ $lead->id }}">
                                                        {{ Str::words($lead->notes, 9, '...') }}
                                                    </p>
                                                    @if (Str::wordCount($lead->notes) > 9)
                                                        <a href="javascript:void(0);" class="text-primary"
                                                            onclick="toggleReadMore({{ $lead->id }}, '{{ e($lead->notes) }}')">
                                                            Read more
                                                        </a>
                                                    @endif
                                                </div>
                                                <table class="w-100 mt-3 table table-bordered text-center lead-detail-table">

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
                                            <div class="card-footer hstack gap-2 justify-content-center lead-actions">
                                                <ul class="list-inline mb-0">
                                                    @if ($whatsapp)
                                                        <li class="list-inline-item avatar-xs">
                                                            <a href="https://api.whatsapp.com/send/?phone={{ $whatsapp?->phone }}"
                                                                target="_blank"
                                                                class="avatar-title bg-success-subtle text-success fs-15 rounded lead-action-link">
                                                                <i class="ri-whatsapp-fill"></i>
                                                            </a>
                                                        </li>
                                                    @endif

                                                    @if ($primary)
                                                        <li class="list-inline-item avatar-xs">
                                                            <a href="tel:{{ $primary?->phone }}"
                                                                class="avatar-title bg-success-subtle text-success fs-15 rounded lead-action-link">
                                                                <i class="ri-phone-fill"></i>
                                                            </a>
                                                        </li>
                                                    @endif



                                                    <li class="list-inline-item avatar-xs">
                                                        <a href="javascript:void(0);" data-size="lg"
                                                            data-url="{{ URL::to('leads/' . $lead->id . '/edit') }}"
                                                            data-ajax-popup="true"
                                                            data-bs-original-title="{{ __('Edit Lead') }}"
                                                            class="avatar-title bg-danger-subtle text-danger fs-15 rounded lead-action-link">
                                                            <i class="ri-edit-2-fill"></i>
                                                        </a>

                                                    </li>
                                                    <li class="list-inline-item avatar-xs">
                                                        <a href="{{ route('leads.view', [$lead->id]) }}"
                                                            class="avatar-title bg-warning-subtle text-warning fs-15 rounded lead-action-link">
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
    </div>





    <!-- filter model -->
    <div class="offcanvas offcanvas-end fade filters-panel" tabindex="-1" id="offcanvasExample"
        aria-labelledby="offcanvasExampleLabel" aria-modal="true" role="dialog">
        <div class="offcanvas-header bg-light">
            <h5 class="offcanvas-title" id="offcanvasExampleLabel">Lead Filters</h5>
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
            <button type="submit" class="btn btn-success w-100">Apply Filters</button>
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
                            '" alt="profile" class="rounded-3" style="width:32px;height:32px;object-fit:cover;">')
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
