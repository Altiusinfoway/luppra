@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Order Settings</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Settings</a></li>
                            <li class="breadcrumb-item active">Order </li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>


        <div class="row">
            <!-- Lead Status -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0 text-light">Status Management</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            {{ Form::open(['url' => (isset($status) && !empty($status)) ? route('setting.order.update',['status', $status->id]) : route('setting.order.save') , 'method' => 'post', 'class'=>'needs-validation', 'novalidate']) }}
                            <div class="row">
                                <input type="hidden" name="setting" id="setting" value="status">
                                <div class="col">
                                    <label for="statusName" class="form-label">Status Name</label>
                                    <input type="text" class="form-control" name="name" id="statusName" placeholder="Enter status Name" value="{{ (isset($status) && !empty($status)) ? $status->name : '' }}">
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
                                    <td>{{ $status->name }} </td>
                                    <td><span class="badge" style="background-color: {{ $status->color }};">&nbsp;</span></td>
                                    <td>
                                        <a href="{{ route('setting.order.edit',['status', $status->id ]) }}" class="btn btn-warning btn-sm me-2">Edit</a>
                                        {{-- <a href="{{ route('setting.order.delete',['status', $status->id ]) }}" class="btn btn-danger btn-sm">Delete</a> --}}
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

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Activity History</h5>
                    </div>
                    <div class="card-body">
                        @include('activity._timeline', [
                            'activities' => $settingsActivityTimeline,
                            'emptyMessage' => 'No activity found for order settings.',
                        ])
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
