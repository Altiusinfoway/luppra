<div class="card mb-3">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">Actual Preview Output</h5>
        <span class="badge bg-info-subtle text-info">Dompdf-Safe Shared Layout</span>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            This preview uses the same table-based structure prepared for the new template PDF, so browser preview and PDF output stay aligned.
        </div>

        @include('invoice_templates.render.browser', [
            'template' => $template,
            'sectionMap' => $sectionMap,
            'previewData' => $previewData,
            'previewTheme' => $previewTheme,
        ])
    </div>
</div>
