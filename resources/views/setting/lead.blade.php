@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Lead Settings</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Settings</a></li>
                            <li class="breadcrumb-item active">Lead </li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="row">
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-tabs nav-justified nav-border-top nav-border-top-success mb-3"
                                role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ isset($type) && $type == 'source' && !request()->has('lead_settings_activities_page') ? 'active' : '' }}" data-bs-toggle="tab" href="#Source-Management"
                                        role="tab" aria-selected="{{ isset($type) && $type == 'source' ? 'true' : 'false' }}">
                                        <i class="ri-home-5-line align-middle me-1"></i> Source Management
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ isset($type) && $type == 'status' && !request()->has('lead_settings_activities_page') ? 'active' : '' }}" data-bs-toggle="tab" href="#Status-Management"
                                        role="tab" aria-selected="{{ isset($type) && $type == 'status' ? 'true' : 'false' }}" tabindex="-1">
                                        <i class="ri-user-line me-1 align-middle"></i> Status Management
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ isset($type) && $type == 'lead_type' && !request()->has('lead_settings_activities_page') ? 'active' : '' }}" data-bs-toggle="tab" href="#Lead-Type"
                                        role="tab" aria-selected="{{ isset($type) && $type == 'lead_type' ? 'true' : 'false' }}" tabindex="-1">
                                        <i class="ri-question-answer-line align-middle me-1"></i>Lead Type
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ request()->has('lead_settings_activities_page') ? 'active' : '' }}" data-bs-toggle="tab" href="#Lead-Activities"
                                        role="tab" aria-selected="{{ request()->has('lead_settings_activities_page') ? 'true' : 'false' }}" tabindex="-1">
                                        <i class="ri-history-line align-middle me-1"></i>Activities
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content text-muted">
                                <div class="tab-pane {{ isset($type) && $type == 'source' && !request()->has('lead_settings_activities_page') ? 'active show' : '' }}" id="Source-Management" role="tabpanel">
                                    <!-- Lead Source -->
                                    <div class="col-md-12">
                                        <div class="card mb-4">
                                            <div class="card-header bg-primary text-white">
                                                <h5 class="card-title mb-0 text-light">Source Management</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    {{-- <h6>Add Lead Source</h6> --}}
                                                    {{ Form::open(['url' => (isset($source) && !empty($source)) ? route('setting.lead.update',['source', $source->id]) : route('setting.lead.save') , 'method' => 'post', 'class'=>'needs-validation', 'novalidate']) }}
                                                    <div class="row">
                                                        <input type="hidden" name="setting" id="setting" value="source">
                                                        <input type="hidden" name="id" id="id" value="{{ (isset($source) && !empty($source)) ? $source->id : '' }}">

                                                        <div class="col">
                                                            <label for="source" class="form-label">Source Name</label>
                                                            <input type="text" class="form-control" name="source" id="source" placeholder="Enter Source Name"
                                                            value="{{ (isset($source) && !empty($source)) ? $source->name : '' }}">
                                                        </div>
                                                        <div class="col align-self-end">
                                                            <input type="submit" class="btn btn-success" value="Save">
                                                        </div>
                                                    </div>
                                                    {{ Form::close() }}
                                                </div>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Source Name</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @if(!empty($sources))

                                                            @forelse ($sources as $source)
                                                            <tr>
                                                                <td>{{ $source->name }}</td>
                                                                <td>
                                                                    @if((int) ($source->is_editable ?? 1) === 1)
                                                                        <a href="{{ route('setting.lead.edit',['source', $source->id ]) }}" class="btn btn-warning btn-sm me-2">Edit</a>
                                                                        <a href="{{ route('setting.lead.delete',['source', $source->id ]) }}" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this lead source?')">Delete</a>
                                                                    @else
                                                                        <span class="badge bg-secondary">Predefined</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            @empty
                                                            <tr>
                                                                <td colspan="2"> Source Not Found! </td>
                                                            </tr>
                                                            @endforelse
                                                        @endif



                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Lead Source -->
                                </div>
                                <div class="tab-pane {{ isset($type) && $type == 'status' && !request()->has('lead_settings_activities_page') ? 'active show' : '' }}" id="Status-Management" role="tabpanel">
                                    <!-- Lead Status -->
                                    <div class="col-md-12">
                                        <div class="card mb-4">
                                            <div class="card-header bg-primary text-white">
                                                <h5 class="card-title mb-0 text-light">Status Management</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    {{-- <h6>Add Lead Status</h6> --}}
                                                    {{ Form::open(['url' => (isset($status) && !empty($status)) ? route('setting.lead.update',['status', $status->id]) : route('setting.lead.save') , 'method' => 'post', 'class'=>'needs-validation', 'novalidate']) }}
                                                    <div class="row">
                                                        <input type="hidden" name="setting" id="setting" value="status">
                                                        <input type="hidden" name="id" id="id" value="{{ (isset($status) && !empty($status)) ? $status->id : '' }}">
                                                        <div class="col">
                                                            <label for="statusName" class="form-label">Status Name</label>
                                                            <input type="text" class="form-control" name="statusName" id="statusName" placeholder="Enter status Name" value="{{ (isset($status) && !empty($status)) ? $status->name : '' }}">
                                                        </div>
                                                        <div class="col">
                                                            <label for="statusColor" class="form-label">Color</label>
                                                            <input type="color" class="form-control" id="statusColor" name="color" value="{{ (isset($status) && !empty($status)) ? $status->color : '' }}">
                                                        </div>
                                                        <div class="col align-self-end">
                                                            <input type="submit" class="btn btn-success" value="Save">
                                                        </div>
                                                    </div>
                                                    {{ Form::close() }}

                                                </div>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Status Name</th>
                                                            <th>Color</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    @if(!empty($statuses))

                                                        @forelse ($statuses as $status)
                                                        <tr>
                                                            <td>{{ $status->name }}</td>
                                                            <td><span class="badge" style="background-color: {{ $status->color }};">&nbsp;</span></td>
                                                            <td>
                                                                @if((int) ($status->is_editable ?? 1) === 1)
                                                                    <a href="{{ route('setting.lead.edit',['status', $status->id ]) }}" class="btn btn-warning btn-sm me-2">Edit</a>
                                                                    <a href="{{ route('setting.lead.delete',['status', $status->id ]) }}" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this lead status?')">Delete</a>
                                                                @else
                                                                    <span class="badge bg-secondary">Predefined</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @empty
                                                        <tr>
                                                            <td colspan="3"> Source Not Found! </td>
                                                        </tr>
                                                        @endforelse

                                                    @endif

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                    </div>
                                    <!-- End Lead Status -->
                                </div>
                                <div class="tab-pane {{ isset($type) && $type == 'lead_type' && !request()->has('lead_settings_activities_page') ? 'active show' : '' }}" id="Lead-Type" role="tabpanel">
                                    <!-- Lead Type start -->
                                    <div class="col-md-12">
                                        <div class="card mb-4">
                                            <div class="card-header bg-primary text-white">
                                                <h5 class="card-title mb-0 text-light">Lead Type</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    {{-- <h6>Add Lead Source</h6> --}}
                                                    {{ Form::open(['url' => isset($lead_type_id) && !empty($lead_type_id) ? route('setting.lead.update', ['lead_type', $lead_type_id->id]) : route('setting.lead.save'), 'method' => 'post', 'class' => 'needs-validation', 'novalidate']) }}
                                                    <div class="row">
                                                        <input type="hidden" name="setting" id="setting"
                                                            value="lead_type">
                                                        <input type="hidden" name="id" id="id"
                                                            value="{{ isset($lead_type_id) && !empty($lead_type_id) ? $lead_type_id->id : '' }}">

                                                        <div class="col">
                                                            <label for="lead_type_name" class="form-label">
                                                                Name</label>
                                                            <input type="text" class="form-control"
                                                                name="lead_type_name" id="lead_type_name"
                                                                placeholder="Enter Lead Type Name"
                                                                value="{{ isset($lead_type_id) && !empty($lead_type_id) ? $lead_type_id->name : '' }}">
                                                        </div>
                                                        <div class="col align-self-end">
                                                            <input type="submit" class="btn btn-success"
                                                                value="Save">
                                                        </div>
                                                    </div>
                                                    {{ Form::close() }}
                                                </div>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Lead Type Name</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @if (!empty($lead_type_all))
                                                            @forelse ($lead_type_all as $lead_list)
                                                                <tr>
                                                                    <td>{{ $lead_list->name }}</td>
                                                                    <td>
                                                                        <a href="{{ route('setting.lead.edit', ['lead_type', $lead_list->id]) }}"
                                                                            class="btn btn-warning btn-sm me-2">Edit</a>
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="2"> Lead Type Not Found! </td>
                                                                </tr>
                                                            @endforelse
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- --------- lead type end ------------ -->
                                </div>
                                <div class="tab-pane {{ request()->has('lead_settings_activities_page') ? 'active show' : '' }}" id="Lead-Activities" role="tabpanel">
                                    <div class="card mb-4">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="card-title mb-0 text-light">Activity History</h5>
                                        </div>
                                        <div class="card-body">
                                            @include('activity._timeline', [
                                                'activities' => $settingsActivityTimeline,
                                                'emptyMessage' => 'No activity found for lead settings.',
                                            ])
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end card-body -->
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection
