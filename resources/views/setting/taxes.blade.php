@extends('layouts.app')

@section('page-css')
<style>
    .settings-suite {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
    }
    .settings-suite .hero-shell,
    .settings-suite .settings-shell {
        border: 1px solid rgba(255, 255, 255, 0.78);
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
    }
    .settings-suite .hero-shell {
        border-radius: 28px;
        background:
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
            radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
            linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
        margin-bottom: 1rem;
    }
    .settings-suite .settings-shell {
        border-radius: 22px;
    }
    .settings-suite .hero-eyebrow {
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
    .settings-suite .hero-title {
        font-size: clamp(2rem, 3vw, 2.7rem);
        line-height: 1.05;
        letter-spacing: -0.04em;
        font-weight: 800;
        margin: 1rem 0 .45rem;
        color: #0f172a;
    }
    .settings-suite .hero-subtitle {
        color: #64748b;
    }
</style>
@endsection

@section('content')
<div class="page-content settings-suite">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="hero-shell">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-8">
                                <span class="hero-eyebrow">Configuration</span>
                                <h1 class="hero-title">Tax Settings</h1>
                                <p class="hero-subtitle mb-0">Manage tax percentages and review tax-setting activity inside the same modern admin shell as the rest of the refreshed app.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Settings</a></li>
                                        <li class="breadcrumb-item active">Taxes</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4 settings-shell">
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
                <div class="card mb-4 settings-shell">
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
