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
    .settings-suite .summary-card {
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.84);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
    }
    .settings-suite .summary-card .label {
        display: block;
        margin-bottom: 8px;
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .settings-suite .summary-card h3 {
        margin: 0;
        font-size: 1.7rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #0f172a;
    }
    .settings-suite .tab-shell {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #f8fafc;
        padding: 8px;
        gap: 8px;
    }
    .settings-suite .tab-shell .nav-link {
        border: 0;
        border-radius: 14px;
        color: #475569;
        font-weight: 700;
        padding: 10px 16px;
    }
    .settings-suite .tab-shell .nav-link.active {
        background: #ffffff;
        color: #0f172a;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    }
    .settings-suite .section-intro {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #f8fafc;
        padding: 16px 18px;
        margin-bottom: 22px;
    }
    .settings-suite .form-actions {
        border-top: 1px solid #e2e8f0;
        margin-top: 8px;
        padding-top: 20px;
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
                                <h1 class="hero-title">Terms & Conditions</h1>
                                <p class="hero-subtitle mb-0">Manage reusable invoice and quotation document terms in the same modern settings shell as the rest of the refreshed CRM.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript:void(0);">Workspace Settings</a></li>
                                        <li class="breadcrumb-item active">Terms & Conditions</li>
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
                        <span class="label">Documents</span>
                        <h3>Invoice + Quote</h3>
                        <p class="text-muted mb-0 mt-2">Keep reusable legal text blocks organized for both core sales documents.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Editing</span>
                        <h3>Tabbed</h3>
                        <p class="text-muted mb-0 mt-2">Switch between invoice and quotation wording from a cleaner segmented control.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card settings-shell">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Document Terms</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('setting.terms.save') }}" method="post">
                            @csrf
                            <div class="section-intro">
                                <h6 class="mb-1">Document terms</h6>
                                <p class="text-muted mb-0">Update the standard wording customers should see on invoices and quotations without leaving the settings workflow.</p>
                            </div>

                            <ul class="nav nav-tabs nav-tabs-custom nav-success mb-3 tab-shell" role="tablist">
                                {{-- <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab"
                                        data-bs-target="#general-pane" type="button" role="tab"
                                        aria-controls="general-pane" aria-selected="true">
                                        General
                                    </button>
                                </li> --}}
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="invoice-tab" data-bs-toggle="tab"
                                        data-bs-target="#invoice-pane" type="button" role="tab"
                                        aria-controls="invoice-pane" aria-selected="true">
                                        Invoice
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="quotation-tab" data-bs-toggle="tab"
                                        data-bs-target="#quotation-pane" type="button" role="tab"
                                        aria-controls="quotation-pane" aria-selected="false">
                                        Quotation
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content">
                                {{-- <div class="tab-pane fade show active" id="general-pane" role="tabpanel"
                                    aria-labelledby="general-tab">
                                    <div class="mb-3">
                                        <label for="general" class="form-label">General Terms</label>
                                        <textarea name="general" id="general" rows="10" class="form-control"
                                            placeholder="Enter general terms and conditions">{{ old('general', $terms['general'] ?? '') }}</textarea>
                                    </div>
                                </div> --}}

                                <div class="tab-pane fade show active" id="invoice-pane" role="tabpanel"
                                    aria-labelledby="invoice-tab">
                                    <div class="mb-3">
                                        <label for="invoice" class="form-label">Invoice Terms</label>
                                        <textarea name="invoice" id="invoice" rows="10" class="form-control"
                                            placeholder="Enter invoice terms and conditions">{{ old('invoice', $terms['invoice'] ?? '') }}</textarea>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="quotation-pane" role="tabpanel"
                                    aria-labelledby="quotation-tab">
                                    <div class="mb-3">
                                        <label for="quotation" class="form-label">Quotation Terms</label>
                                        <textarea name="quotation" id="quotation" rows="10" class="form-control"
                                            placeholder="Enter quotation terms and conditions">{{ old('quotation', $terms['quotation'] ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end form-actions">
                                <button type="submit" class="btn btn-success">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
