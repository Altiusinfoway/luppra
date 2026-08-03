@extends('layouts.app')

@section('page-css')
<style>
    .bulk-message-suite {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
    }

    .bulk-message-suite .hero-shell,
    .bulk-message-suite .shell-card {
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
    }

    .bulk-message-suite .hero-shell {
        background:
            radial-gradient(circle at top right, rgba(14, 165, 233, 0.14), transparent 30%),
            radial-gradient(circle at left center, rgba(16, 185, 129, 0.14), transparent 30%),
            linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }

    .bulk-message-suite .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        padding: 7px 12px;
        border-radius: 999px;
        border: 1px solid #dbeafe;
        background: rgba(255, 255, 255, 0.86);
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .select2-container {
        box-sizing: border-box;
        display: inline-block;
        margin: 0;
        position: relative;
        vertical-align: middle;
    }

    .select2-hidden-accessible {
        border: 0 !important;
        clip: rect(0 0 0 0) !important;
        clip-path: inset(50%) !important;
        height: 1px !important;
        overflow: hidden !important;
        padding: 0 !important;
        position: absolute !important;
        width: 1px !important;
        white-space: nowrap !important;
    }

    .select2-container .select2-selection--single,
    .select2-container .select2-selection--multiple {
        box-sizing: border-box;
        cursor: pointer;
        display: block;
        min-height: 38px;
        user-select: none;
    }

    .select2-container .select2-selection--multiple {
        cursor: text;
    }

    .select2-container .select2-search--inline {
        float: left;
    }

    .select2-container .select2-search--inline .select2-search__field {
        box-sizing: border-box;
        border: none;
        font-size: 100%;
        margin-top: 5px;
        padding: 0;
    }

    .select2-container .select2-search--inline .select2-search__field::-webkit-search-cancel-button {
        appearance: none;
    }

    .select2-dropdown {
        background-color: #fff;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        box-sizing: border-box;
        display: block;
        position: absolute;
        left: -100000px;
        width: 100%;
        z-index: 1056;
    }

    .select2-container--open .select2-dropdown {
        left: 0;
    }

    .select2-container--open .select2-dropdown--above {
        border-bottom: none;
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }

    .select2-container--open .select2-dropdown--below {
        border-top: none;
        border-top-left-radius: 0;
        border-top-right-radius: 0;
    }

    .select2-search--dropdown {
        display: none;
        padding: 0.4rem;
    }

    .select2-search--dropdown .select2-search__field {
        width: 100%;
        padding: 0.38rem 0.55rem;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        outline: 0;
    }

    .select2-results {
        display: block;
    }

    .bulk-customer-select-wrap .select2-container {
        width: 100% !important;
    }

    .bulk-customer-select-wrap .select2-container--default .select2-selection--multiple {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 0.2rem 0.35rem;
        background: #fff;
    }

    .bulk-customer-select-wrap .select2-selection--multiple .select2-selection__rendered {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .bulk-customer-select-wrap .select2-selection--multiple .select2-selection__choice {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin: 0.1rem 0;
        padding: 0.18rem 0.5rem;
        border-radius: 0.25rem;
        background: #0b1736;
        color: #fff;
        border: 0;
        line-height: 1.3;
    }

    .bulk-customer-select-wrap .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff;
        cursor: pointer;
        font-weight: 700;
        border: 0;
        background: transparent;
        padding: 0;
    }

    .select2-results__options {
        margin: 0;
        padding: 0;
        list-style: none;
        max-height: 220px;
        overflow-y: auto;
    }

    .select2-results__option {
        padding: 0.45rem 0.75rem;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background: #0b1736;
        color: #fff;
    }

    .select2-container--default .select2-results__option[aria-selected=true] {
        background: #e9ecef;
        color: #212529;
    }

    .bulk-customer-select-wrap .select2-selection--multiple .select2-search__field {
        min-width: 12rem;
        border: 0;
        outline: 0;
        margin-top: 0.2rem;
    }
</style>
@endsection

@section('content')
<div class="page-content bulk-message-suite">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <span class="hero-eyebrow">Broadcast Messaging</span>
                                <h2 class="mt-3 mb-2">Bulk Message</h2>
                                <p class="text-muted mb-0">Send targeted Whatsapp broadcasts by lead status or direct customer selection from a cleaner messaging workspace.</p>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Bulk Message</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7">
                <div class="card shell-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Send Bulk Message</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('bulk-message.send') }}">
                            @csrf

                            <div class="row g-3">
                                @php
                                    $selectedMode = old('send_mode', 'lead_status');
                                    $selectedCustomers = collect(old('customer_ids', []))->map(fn($id) => (int) $id)->all();
                                @endphp

                                <div class="col-md-6">
                                    <label class="form-label">Send Mode</label>
                                    <select id="bulkSendMode" class="form-select @error('send_mode') is-invalid @enderror" name="send_mode" required>
                                        <option value="lead_status" {{ $selectedMode === 'lead_status' ? 'selected' : '' }}>By Lead Status</option>
                                        <option value="direct_customers" {{ $selectedMode === 'direct_customers' ? 'selected' : '' }}>Direct Customers</option>
                                    </select>
                                    @error('send_mode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 bulk-mode-field" data-mode-field="lead_status">
                                    <label class="form-label">Lead Status</label>
                                    <select id="bulkStageSelect" class="form-select @error('stage_id') is-invalid @enderror" name="stage_id">
                                        <option value="">Select Status</option>
                                        @foreach($leadStages as $stage)
                                            <option value="{{ $stage->id }}" {{ old('stage_id') == $stage->id ? 'selected' : '' }}>
                                                {{ $stage->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('stage_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 bulk-mode-field bulk-customer-select-wrap" data-mode-field="direct_customers">
                                    <label class="form-label">Customers</label>
                                    <select id="bulkCustomerSelect" class="form-select @error('customer_ids') is-invalid @enderror @error('customer_ids.*') is-invalid @enderror" name="customer_ids[]" multiple>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ in_array((int) $customer->id, $selectedCustomers, true) ? 'selected' : '' }}>
                                                {{ $customer->name ?: $customer->company_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_ids')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('customer_ids.*')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Device</label>
                                    <select class="form-select @error('device') is-invalid @enderror" name="device" required>
                                        <option value="">Select Device</option>
                                        @foreach($devices as $device)
                                            <option value="{{ $device->id }}" {{ old('device') == $device->id ? 'selected' : '' }}>
                                                {{ $device->name }} (+{{ $device->phone }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('device')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Message</label>
                                    <div class="mb-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertBulkTag('*', '*')">Bold</button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertBulkTag('_', '_')">Italic</button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertBulkTag('```', '```')">Monospace</button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertBulkTag('~', '~')">Strike</button>
                                    </div>
                                    <textarea id="bulkMessageTextarea" class="form-control @error('message') is-invalid @enderror"
                                        name="message" rows="8" maxlength="1000" required>{{ old('message') }}</textarea>
                                    @error('message')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-success" {{ $devices->isEmpty() || ($leadStages->isEmpty() && $customers->isEmpty()) ? 'disabled' : '' }}>
                                        Send Message
                                    </button>
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
<script src="{{ asset('public/build/assets/js/pages/user/select2.full.min.js') }}"></script>
<script>
    function updateBulkModeFields() {
        const mode = document.getElementById('bulkSendMode').value;
        const stageSelect = document.getElementById('bulkStageSelect');
        const customerSelect = document.getElementById('bulkCustomerSelect');

        document.querySelectorAll('.bulk-mode-field').forEach(function(field) {
            field.style.display = field.dataset.modeField === mode ? '' : 'none';
        });

        stageSelect.required = mode === 'lead_status';
        customerSelect.required = mode === 'direct_customers';
    }

    function insertBulkTag(openTag, closeTag) {
        const textarea = document.getElementById('bulkMessageTextarea');
        const startPos = textarea.selectionStart;
        const endPos = textarea.selectionEnd;
        const selectedText = textarea.value.substring(startPos, endPos);
        const newText = openTag + selectedText + closeTag;

        textarea.value = textarea.value.substring(0, startPos) + newText + textarea.value.substring(endPos);
        textarea.focus();
        textarea.setSelectionRange(endPos + openTag.length + closeTag.length, endPos + openTag.length + closeTag.length);
    }

    $(function() {
        $('#bulkCustomerSelect').next('.select2-container').remove();

        if ($('#bulkCustomerSelect').data('select2')) {
            $('#bulkCustomerSelect').select2('destroy');
        }

        $('#bulkCustomerSelect').select2({
            placeholder: 'Select customers',
            width: '100%',
            closeOnSelect: false
        });

        document.getElementById('bulkSendMode').addEventListener('change', updateBulkModeFields);
        updateBulkModeFields();
    });
</script>
@endsection
