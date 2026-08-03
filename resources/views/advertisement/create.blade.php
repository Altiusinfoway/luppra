@extends('layouts.app')

@section('page-css')
<style>
.advertisement-form-suite {
    background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
}

.advertisement-form-suite .hero-shell,
.advertisement-form-suite .form-shell {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
}

.advertisement-form-suite .hero-shell {
    background:
        radial-gradient(circle at top right, rgba(251, 191, 36, 0.18), transparent 30%),
        radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
        linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}

.advertisement-form-suite .hero-eyebrow {
    display: inline-flex;
    align-items: center;
    padding: 7px 12px;
    border-radius: 999px;
    border: 1px solid #fde68a;
    background: rgba(255, 255, 255, 0.86);
    color: #b45309;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.advertisement-form-suite .summary-card {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.86);
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
}

.advertisement-form-suite .summary-card .label {
    display: block;
    margin-bottom: 8px;
    color: #64748b;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.advertisement-form-suite .summary-card h3 {
    margin: 0;
    font-size: 1.7rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: #0f172a;
}

.advertisement-form-suite .section-card {
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: #f8fafc;
    padding: 16px;
}
</style>
@endsection

@section('content')
<div class="page-content advertisement-form-suite">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <span class="hero-eyebrow">Campaign Catalog</span>
                                <h2 class="mt-3 mb-2">Create Advertisement</h2>
                                <p class="text-muted mb-0">Add a new advertisement record with name, amount, and description inside the refreshed campaign form shell.</p>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('advertisements.index') }}">Advertisement</a></li>
                                        <li class="breadcrumb-item active">Create</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Campaign Setup</span>
                        <h3>New Record</h3>
                        <p class="text-muted mb-0 mt-2">Create a marketing spend entry with the same KPI-first setup language used across the refreshed campaign workspace.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Fields</span>
                        <h3>Name + Amount</h3>
                        <p class="text-muted mb-0 mt-2">Keep campaign name, amount, and supporting description grouped into one focused inner form section.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="card form-shell">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title  mb-0">Advertisement Add</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('advertisements.store') }}" method="POST" id="AdvertisementForm">
                            @csrf
                            <div class="section-card">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="name">name <span class="text-danger">*</span></label>
                                                    <input type="text" name="name" class="form-control" id="name" placeholder="Enter name">
                                                    @if($errors->has('name'))
                                                        <div class="error text-danger">{{ $errors->first('name') }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="amount">Amount <span class="text-danger">*</span></label>
                                                    <input type="number" name="amount" class="form-control" id="amount" placeholder="Enter Amount">
                                                    @if($errors->has('amount'))
                                                        <div class="error text-danger">{{ $errors->first('amount') }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3">
                                                <label class="form-label" for="description">Description</label>
                                                <textarea rows="8" name="description" class="form-control" id="description"></textarea>
                                                @if($errors->has('description'))
                                                    <div class="error text-danger">{{ $errors->first('description') }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mb-3">
                                <button type="submit" class="btn btn-primary w-sm">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
