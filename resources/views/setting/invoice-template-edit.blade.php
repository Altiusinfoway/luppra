@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Edit Invoice Template</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('setting.invoice-templates.show', $template->id) }}" class="btn btn-sm btn-outline-primary">Back</a>
                    </div>
                </div>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('setting.invoice-templates.update', $template->id) }}">
            @csrf

            <div class="card mb-3">
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
                            <div class="alert alert-info mb-0">
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

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Template Sections</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-section-row">Add Section</button>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        Removing a known section hides that invoice block from the new template PDF. Adding a new custom section keeps the PDF safe and shows it in the extra sections area.
                    </div>

                    <div id="section-rows">
                        @foreach($formSections as $index => $section)
                            <div class="border rounded p-3 mb-3 section-row">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h6 class="mb-0">Section Row</h6>
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
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('setting.invoice-templates.show', $template->id) }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Template</button>
                </div>
            </div>
        </form>
    </div>
</div>

<template id="section-row-template">
    <div class="border rounded p-3 mb-3 section-row">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="mb-0">Section Row</h6>
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
