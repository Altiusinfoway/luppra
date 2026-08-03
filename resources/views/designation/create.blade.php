@extends('layouts.app')

@section('page-css')
    <style>
        .hr-form-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .hr-form-suite .hero-shell,
        .hr-form-suite .form-shell {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
        }

        .hr-form-suite .hero-eyebrow {
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

        .hr-form-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.84);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .hr-form-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .hr-form-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
        }

        .hr-form-suite .section-intro {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 16px 18px;
            margin-bottom: 22px;
        }

        .hr-form-suite .section-card {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        }

        .hr-form-suite .form-actions {
            border-top: 1px solid #e2e8f0;
            margin-top: 8px;
            padding-top: 20px;
        }
    </style>
@endsection

@section('content')
<div class="page-content hr-form-suite">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-8">
                                <span class="hero-eyebrow">Organization Setup</span>
                                <h1 class="mb-3">Create Designation</h1>
                                <p class="text-muted mb-0">Add a designation and link it to a department inside the same refreshed administrative form shell.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('designations.index') }}">Designation</a></li>
                                        <li class="breadcrumb-item active">Create</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Setup</span>
                        <h3>Designation</h3>
                        <p class="text-muted mb-0 mt-2">Create job titles and map them to departments in one focused form flow.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Dependency</span>
                        <h3>Department</h3>
                        <p class="text-muted mb-0 mt-2">Each designation should be anchored to the correct organizational group.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card form-shell">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title mb-0">Designation Add</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('designations.store') }}" method="POST" id="designationAddForm">
                            @csrf
                            <div class="section-card p-3 p-lg-4">
                                <div class="section-intro">
                                    <h6 class="mb-1">Designation details</h6>
                                    <p class="text-muted mb-0">Capture the department first, then add the role title so downstream HR records stay organized.</p>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="name">Department <span class="text-danger">*</span></label>
                                                    <select name="department_id" class="form-control">
                                                        <option value="0">Select Department</option>
                                                        @foreach ($department_list as $list)
                                                            <option value="{{ $list['id'] }}">{{ $list['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                    @if($errors->has('department_id'))
                                                        <div class="error text-danger">{{ $errors->first('department_id') }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="name">name <span class="text-danger">*</span></label>
                                                    <input type="text" name="name" class="form-control" id="name" placeholder="Enter name">
                                                    @if($errors->has('name'))
                                                        <div class="error text-danger">{{ $errors->first('name') }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center mb-3 form-actions">
                                        <button type="submit" class="btn btn-primary w-sm" id="DesignationAddBtn">Save Designation</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
document.getElementById('designationAddForm').addEventListener('submit', function () {
    const btn = document.getElementById('DesignationAddBtn');
    btn.disabled = true;
    btn.innerText = 'Saving...';
});
</script>
@endsection
