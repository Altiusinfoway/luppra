<style>
    @page {
        size: A4 portrait;
        margin: 8mm;
    }

    body {
        margin: 0;
        padding: 0;
        color: #111827;
        font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
        font-size: 11px;
        line-height: 1.35;
    }

    .it-document {
        width: 100%;
        margin: 0 auto;
        background: #ffffff;
    }

    .it-sheet {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        border: 1px solid {{ $previewTheme['border'] ?? '#cfd8e3' }};
    }

    .it-sheet td,
    .it-sheet th {
        vertical-align: top;
    }

    .it-header-box {
        background: {{ $previewTheme['accent'] ?? '#eef1f5' }};
        color: {{ $previewTheme['accent_text'] ?? '#1f2937' }};
        border: 1px solid {{ $previewTheme['border'] ?? '#cfd8e3' }};
        padding: 6px 8px;
    }

    .it-company-title {
        font-size: 18px;
        font-weight: 700;
        letter-spacing: 0.4px;
    }

    .it-invoice-title {
        font-size: 17px;
        font-weight: 700;
        text-align: center;
        letter-spacing: 0.5px;
    }

    .it-small {
        font-size: 10px;
    }

    .it-section-box {
        border: 1px solid {{ $previewTheme['border'] ?? '#cfd8e3' }};
        padding: 6px 8px;
    }

    .it-section-title {
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 5px;
        text-transform: uppercase;
    }

    .it-inner-table,
    .it-items-table,
    .it-totals-table,
    .it-tax-table,
    .it-footer-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .it-inner-table td {
        padding: 2px 0;
    }

    .it-items-table th,
    .it-items-table td {
        border: 1px solid {{ $previewTheme['border'] ?? '#cfd8e3' }};
        padding: 5px 6px;
        font-size: 10px;
    }

    .it-items-table th {
        background: {{ $previewTheme['accent'] ?? '#eef1f5' }};
        color: {{ $previewTheme['accent_text'] ?? '#1f2937' }};
        font-weight: 700;
        text-align: left;
    }

    .it-totals-table td,
    .it-tax-table td,
    .it-footer-table td {
        border: 1px solid {{ $previewTheme['border'] ?? '#cfd8e3' }};
        padding: 5px 6px;
        font-size: 10px;
    }

    .it-grand-total {
        background: {{ $previewTheme['accent'] ?? '#eef1f5' }};
        color: {{ $previewTheme['accent_text'] ?? '#1f2937' }};
        font-weight: 700;
    }

    .it-logo-box {
        display: inline-block;
        min-width: 34px;
        padding: 8px 6px;
        border: 1px solid {{ $previewTheme['border'] ?? '#cfd8e3' }};
        background: {{ $previewTheme['accent'] ?? '#eef1f5' }};
        text-align: center;
        font-size: 10px;
        font-weight: 700;
    }

    .it-list {
        margin: 0;
        padding-left: 18px;
    }

    .it-list li {
        margin-bottom: 4px;
    }

    .it-right {
        text-align: right;
    }

    .it-center {
        text-align: center;
    }

    .it-muted {
        color: #4b5563;
    }

    .it-signature-box {
        height: 70px;
    }

    .it-signature-text {
        padding-top: 42px;
        text-align: right;
        font-weight: 700;
    }

    .it-compact .it-company-title,
    .it-compact .it-invoice-title {
        font-size: 15px;
    }

    .it-compact .it-items-table th,
    .it-compact .it-items-table td,
    .it-compact .it-totals-table td,
    .it-compact .it-tax-table td,
    .it-compact .it-footer-table td,
    .it-compact .it-inner-table td {
        font-size: 9px;
        padding: 4px 5px;
    }

    .it-modern .it-header-box,
    .it-modern .it-grand-total {
        border-width: 1.2px;
    }
</style>
