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
    .settings-suite .status-banner {
        border: 1px solid #dce4ee;
        border-radius: 18px;
        padding: 1rem 1.15rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }
    .settings-suite .status-banner.status-danger {
        background: linear-gradient(135deg, #fef2f2 0%, #fff1f2 100%);
        border-color: #fecdd3;
        color: #be123c;
    }
    .settings-suite .info-panel {
        border: 1px solid #bfdbfe;
        border-radius: 18px;
        background: linear-gradient(135deg, #eff6ff 0%, #f8fbff 100%);
        color: #1e3a8a;
        padding: 1rem 1.1rem;
    }
    .settings-suite .warning-panel {
        border: 1px solid #fde68a;
        border-radius: 18px;
        background: linear-gradient(135deg, #fffbeb 0%, #fff7d6 100%);
        color: #92400e;
        padding: 1rem 1.1rem;
    }
    .settings-suite .section-row-card {
        border: 1px solid #dce4ee;
        border-radius: 20px;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.04);
        padding: 1rem;
    }
    .settings-suite .section-row-card .section-row-meta {
        color: #64748b;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .08em;
        font-weight: 700;
    }
    .settings-suite .form-actions {
        border-top: 1px solid #e2e8f0;
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.88) 0%, rgba(255, 255, 255, 0.96) 100%);
        padding: 1rem 1.5rem 1.25rem;
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
                                <span class="hero-eyebrow">Templates</span>
                                <h1 class="hero-title">Edit Invoice Template</h1>
                                <p class="hero-subtitle mb-0">Update document setup and section visibility while keeping the template-management workflow visually aligned with the refreshed admin UI.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end gap-2">
                                    <a href="{{ route('setting.invoice-templates.show', $template->id) }}" class="btn btn-sm btn-outline-primary">Back</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($errors->any())
            <div class="status-banner status-danger mb-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('setting.invoice-templates.update', $template->id) }}">
            @csrf

            <div class="card mb-3 settings-shell">
                <div class="card-header">
                    <h5 class="card-title mb-0">Template Setup</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Template Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $template->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Template Code</label>
                            <input type="text" name="code" class="form-control" value="{{ old('code', $template->code) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Paper Size</label>
                            <input type="text" name="paper_size" class="form-control" value="{{ old('paper_size', $template->paper_size) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Orientation</label>
                            <select name="orientation" class="form-select" required>
                                <option value="portrait" {{ old('orientation', $template->orientation) === 'portrait' ? 'selected' : '' }}>Portrait</option>
                                <option value="landscape" {{ old('orientation', $template->orientation) === 'landscape' ? 'selected' : '' }}>Landscape</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">View Name</label>
                            <input type="text" name="view_name" class="form-control" value="{{ old('view_name', $template->view_name) }}" required>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check mt-4 pt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active Template
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check mt-4 pt-2">
                                <input class="form-check-input" type="checkbox" name="is_default" value="1" id="is_default" {{ old('is_default', $template->is_default) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_default">
                                    Mark As Default
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-panel mb-0">
                                Section keys should stay stable because the new shared PDF renderer uses them for known invoice blocks. New section keys still render as extra generic blocks.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $oldSections = old('sections');
                $formSections = is_array($oldSections)
                    ? collect($oldSections)->values()
                    : $template->sections->map(function ($section) {
                        return [
                            'id' => $section->id,
                            'section_key' => $section->section_key,
                            'section_label' => $section->section_label,
                            'is_visible' => $section->is_visible ? '1' : '0',
                            'sort_order' => $section->sort_order,
                            'settings_json' => json_encode($section->settings_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                        ];
                    })->values();
            @endphp

            <div class="card settings-shell">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Template Sections</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-section-row">Add Section</button>
                </div>
                <div class="card-body">
                    <div class="warning-panel mb-3">
                        Removing a known section hides that invoice block from the new template PDF. Adding a new custom section keeps the PDF safe and shows it in the extra sections area.
                    </div>

                    <div id="section-rows">
                        @foreach($formSections as $index => $section)
                            <div class="section-row-card mb-3 section-row">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div>
                                        <div class="section-row-meta">Section Block</div>
                                        <h6 class="mb-0">Section Row</h6>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-section-row">Remove</button>
                                </div>

                                <input type="hidden" name="sections[{{ $index }}][id]" value="{{ $section['id'] ?? '' }}">

                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Section Key</label>
                                        <input type="text" name="sections[{{ $index }}][section_key]" class="form-control" value="{{ $section['section_key'] ?? '' }}" placeholder="items_table">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Section Label</label>
                                        <input type="text" name="sections[{{ $index }}][section_label]" class="form-control" value="{{ $section['section_label'] ?? '' }}" placeholder="Items Table">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Visibility</label>
                                        <select name="sections[{{ $index }}][is_visible]" class="form-select">
                                            <option value="1" {{ (string) ($section['is_visible'] ?? '1') === '1' ? 'selected' : '' }}>Visible</option>
                                            <option value="0" {{ (string) ($section['is_visible'] ?? '1') === '0' ? 'selected' : '' }}>Hidden</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Sort Order</label>
                                        <input type="number" name="sections[{{ $index }}][sort_order]" class="form-control" value="{{ $section['sort_order'] ?? (($index + 1) * 10) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Section Type</label>
                                        <input type="text" class="form-control" value="{{ in_array($section['section_key'] ?? '', ['header','company_info','invoice_meta','customer_info','items_table','tax_summary','totals','amount_in_words','bank_details','signature','terms_conditions']) ? 'Known Block' : 'Custom Block' }}" readonly>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Settings JSON</label>
                                        <textarea name="sections[{{ $index }}][settings_json]" rows="6" class="form-control font-monospace" placeholder='{"label":"Terms & Conditions"}'>{{ $section['settings_json'] ?? '{}' }}</textarea>
                                        <div class="form-text">
                                            Use valid JSON. Example for custom sections: <code>{"content":"Extra footer message","lines":["Line 1","Line 2"]}</code>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer form-actions d-flex justify-content-end gap-2">
                    <a href="{{ route('setting.invoice-templates.show', $template->id) }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Template</button>
                </div>
            </div>
        </form>
    </div>
</div>

<template id="section-row-template">
    <div class="section-row-card mb-3 section-row">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <div class="section-row-meta">Section Block</div>
                <h6 class="mb-0">Section Row</h6>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger remove-section-row">Remove</button>
        </div>

        <input type="hidden" name="sections[__INDEX__][id]" value="">

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Section Key</label>
                <input type="text" name="sections[__INDEX__][section_key]" class="form-control" placeholder="custom_footer">
            </div>
            <div class="col-md-3">
                <label class="form-label">Section Label</label>
                <input type="text" name="sections[__INDEX__][section_label]" class="form-control" placeholder="Custom Footer">
            </div>
            <div class="col-md-2">
                <label class="form-label">Visibility</label>
                <select name="sections[__INDEX__][is_visible]" class="form-select">
                    <option value="1" selected>Visible</option>
                    <option value="0">Hidden</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sections[__INDEX__][sort_order]" class="form-control" value="120">
            </div>
            <div class="col-md-2">
                <label class="form-label">Section Type</label>
                <input type="text" class="form-control" value="Custom Block" readonly>
            </div>
            <div class="col-12">
                <label class="form-label">Settings JSON</label>
                <textarea name="sections[__INDEX__][settings_json]" rows="6" class="form-control font-monospace" placeholder='{"content":"Extra footer message"}'>{}</textarea>
            </div>
        </div>
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var sectionRows = document.getElementById('section-rows');
        var template = document.getElementById('section-row-template');
        var addButton = document.getElementById('add-section-row');
        var sectionIndex = sectionRows.querySelectorAll('.section-row').length;

        addButton.addEventListener('click', function () {
            var html = template.innerHTML.replaceAll('__INDEX__', String(sectionIndex));
            sectionRows.insertAdjacentHTML('beforeend', html);
            sectionIndex += 1;
        });

        sectionRows.addEventListener('click', function (event) {
            if (!event.target.classList.contains('remove-section-row')) {
                return;
            }

            var row = event.target.closest('.section-row');
            if (row) {
                row.remove();
            }
        });
    });
</script>
@endsection
