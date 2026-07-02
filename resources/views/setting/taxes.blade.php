@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Taxes Settings</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Settings</a></li>
                            <li class="breadcrumb-item active">Taxes </li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>


        <div class="row">
            <!-- Tax Management -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0 text-light">Tax Management</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            {{ Form::open(['url' => (isset($tax) && !empty($tax)) ? route('setting.tax.update',['status', $tax->id]) : route('setting.tax.save') , 'method' => 'post', 'class'=>'needs-validation', 'novalidate']) }}
                            <div class="row">
                                <input type="hidden" name="setting" id="setting" value="taxes">
                                {{-- <div class="col">
                                    <label for="taxName" class="form-label">Tax Name</label>
                                    <input type="text" class="form-control" name="name" id="taxName" placeholder="Enter tax Name" value="{{ (isset($tax) && !empty($tax)) ? $tax->name : '' }}">
                                </div> --}}
                                <div class="col">
                                    <label for="taxPercentage" class="form-label">Percentage</label>
                                    <input type="text" class="form-control" id="taxPercentage" name="taxPercentage" value="{{ (isset($tax) && !empty($tax)) ? $tax->rate : '' }}">
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
                                    {{-- <th>Tax Name</th> --}}
                                    <th>Percentage</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            @if(!empty($taxes))

                                @forelse ($taxes as $tax)
                                <tr>
                                    {{-- <td>{{ $tax->name }}</td> --}}
                                    <td>{{ $tax->rate }}</td>
                                    <td>
                                        <a href="{{ route('setting.tax.edit',['taxes', $tax->id ]) }}" class="btn btn-warning btn-sm me-2">Edit</a>
                                        {{-- <a href="{{ route('setting.tax.delete',['taxes', $tax->id ]) }}" class="btn btn-danger btn-sm">Delete</a> --}}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3"> Tax Not Found! </td>
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
                            'emptyMessage' => 'No activity found for tax settings.',
                        ])
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
