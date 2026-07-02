@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Terms & Conditions</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Workspace Settings</a></li>
                            <li class="breadcrumb-item active">Terms & Conditions</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Document Terms</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('setting.terms.save') }}" method="post">
                            @csrf

                            <ul class="nav nav-tabs nav-tabs-custom nav-success mb-3" role="tablist">
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

                            <div class="text-end">
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
