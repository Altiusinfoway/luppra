<style>
    .template-preview-shell {
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .template-preview-shell .shell-header {
        padding: 1.25rem 1.5rem 0;
    }

    .template-preview-shell .shell-body {
        padding: 1.25rem 1.5rem 1.5rem;
    }

    .template-preview-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .template-preview-intro {
        border: 1px solid #dbeafe;
        border-radius: 18px;
        background: linear-gradient(135deg, #f8fbff 0%, #eef6ff 100%);
        padding: 1rem 1.1rem;
        color: #475569;
    }

    .template-preview-intro strong {
        color: #0f172a;
    }

    .template-preview-browser-frame {
        border: 1px solid #dce4ee;
        border-radius: 22px;
        background: linear-gradient(180deg, #f8fafc 0%, #eef3f8 100%);
        padding: 1rem;
        overflow: hidden;
    }

    .template-preview-browser-frame > *:last-child {
        margin-bottom: 0;
    }
</style>

<div class="template-preview-shell mb-3">
    <div class="shell-header">
        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
            <div>
                <span class="template-preview-kicker">Live Preview</span>
                <h5 class="mb-1 mt-3">Actual Preview Output</h5>
                <p class="text-muted mb-0">Browser preview and PDF layout stay aligned through the same Dompdf-safe rendering structure.</p>
            </div>
            <span class="badge bg-info-subtle text-info align-self-start">Dompdf-Safe Shared Layout</span>
        </div>
    </div>
    <div class="shell-body">
        <div class="template-preview-intro mb-3">
            <strong>Preview note:</strong> This sample invoice uses the shared table-based output prepared for your template PDF, which helps the screen preview match the generated document much more closely.
        </div>

        <div class="template-preview-browser-frame">
            @include('invoice_templates.render.browser', [
                'template' => $template,
                'sectionMap' => $sectionMap,
                'previewData' => $previewData,
                'previewTheme' => $previewTheme,
            ])
        </div>
    </div>
</div>
