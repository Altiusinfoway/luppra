@php
    $invoiceLayout = $invoice_layout ?? \App\Models\Utility::getInvoiceLayout();
    $invoice_terms = $invoice_terms ?? [];
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ \App\Models\Utility::getSetting('website_name') ?? '' }}</title>
    <style>
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: normal;
            src: url("{{ asset('fonts/THSarabunNew.ttf') }}") format('truetype');
        }

        /* Page / Print */
        @page {
            size: A4;
            margin: 6mm;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            color: #000;
            -webkit-print-color-adjust: exact;
        }

        @media screen {
            body {
                background:
                    radial-gradient(circle at top right, rgba(15, 118, 110, 0.08), transparent 24%),
                    radial-gradient(circle at left top, rgba(37, 99, 235, 0.1), transparent 26%),
                    linear-gradient(180deg, #f8fafc 0%, #eef3f8 100%);
            }
        }

        .preview-suite {
            max-width: 1200px;
            margin: 0 auto;
            padding: 28px 20px 40px;
        }

        .preview-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
            padding: 18px 20px;
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 24px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
                linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        }

        .preview-toolbar h1 {
            margin: 0;
            font-size: clamp(1.4rem, 2vw, 2rem);
            line-height: 1.05;
            letter-spacing: -0.04em;
            font-weight: 800;
            color: #0f172a;
        }

        .preview-toolbar p {
            margin: 6px 0 0;
            color: #64748b;
            font-size: .9rem;
        }

        .preview-chip {
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

        .preview-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .preview-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 40px;
            padding: 0 14px;
            border-radius: 12px;
            border: 1px solid #dbeafe;
            background: #fff;
            color: #0f172a;
            text-decoration: none;
            font-weight: 700;
            transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease;
        }

        .preview-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
            color: #0f172a;
        }

        .preview-btn.primary {
            border-color: transparent;
            background: linear-gradient(135deg, #0f766e 0%, #2563eb 100%);
            color: #fff;
        }

        .preview-stage {
            padding: 10px;
            border: 1px solid rgba(255, 255, 255, 0.82);
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.55);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5);
        }

        body.layout_2 .cm-bg {
            background: #f2f4f8;
        }
        body.layout_2 .company-title {
            letter-spacing: 0.5px;
            background: #f2f4f8;
        }
        body.layout_2 .items thead th {
            background: #eef1f5;
        }
        body.layout_3 .cm-bg {
            background: #e7f0ff;
        }
        body.layout_3 .company-title {
            background: #e7f0ff;
            border-color: #2b6cb0;
            color: #1e4d8f;
        }
        body.layout_3 .items thead th {
            background: #dbe9ff;
        }
        body.layout_4 .cm-bg {
            background: #e8f7ef;
        }
        body.layout_4 .company-title {
            background: #e8f7ef;
            border-color: #198754;
            color: #0f5132;
        }
        body.layout_4 .items thead th {
            background: #d8f1e3;
        }

        .cm-bg {
            background: #eaeaea;
        }

        .sheet {
            width: 190mm;
            height: 262mm;
            min-height: 262mm;
            max-height: 262mm;
            margin: 5mm auto 0 auto;
            padding: 0 0 10mm 0;
            border: 0.8pt solid #000;
            box-sizing: border-box;
            font-size: 9px;
            line-height: 1.1;
            overflow: hidden;
            position: relative;
        }

        .sheet.invoice-page-break {
            page-break-after: always;
        }

        /* Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .header-table td {
            vertical-align: top;
            padding: 4px;
        }

        .company-title {
            font-size: 18px;
            font-weight: 700;
            text-align: center;
            border: 0.8pt solid #000;
        }

        .company-addr {
            font-size: 9px;
            margin-top: 2px;
        }

        .cm-border-bottom {
            border-bottom: 0.8pt solid #000;
        }

        .title-box {
            border-top: 0.8pt solid #000;
            border-bottom: 0.8pt solid #000;

            width: 100%;
            margin-top: 6px;
        }

        .title-box td {
            padding: 3px;
            font-weight: 700;
        }

        /* Party / Details */
        .party {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }

        .party td {
            vertical-align: top;
            padding: 5px;
            border-bottom: none;
        }

        .party-left {
            width: 31%;
        }

        .party-left {
            border-right: 1pt solid #000;
            padding: 3px;
            box-sizing: border-box;
        }

        .party-right-wrap {
            width: 38%;
        }

        .party-right {
            padding: 0;
            box-sizing: border-box;
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 4px;
            font-size: 12px;
        }

        /* Items table - tuned widths for DOMPDF */
        .items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
            font-size: 9px;
            border-bottom: 0.8pt solid #000;
        }

        .items thead th {
            border: 0.8pt solid #000;
            padding: 3px;
            background: #f5f5f5;
            font-weight: 700;
        }

        .items tbody td {
            border-left: 0.8pt solid #000;
            border-right: 0.8pt solid #000;
            border-bottom: none;
            padding: 0;
            vertical-align: middle;
        }

        .items tbody tr.product-row td,
        .items tbody tr.blank-product-row td {
            height: 20px;
            line-height: 20px;
        }

        .items tbody tr.product-row td {
            vertical-align: top;
        }

        .items tbody tr.blank-product-row td {
            font-size: 1px;
        }

        .items tbody tr:first-child td {
            border-top: 0.8pt solid #000;
        }

        .items td.center {
            text-align: center;
        }

        .items td.right {
            text-align: right;
        }

        /* Column widths */
        .col-sr {
            width: 6mm;
        }

        .col-name {
            width: 50mm;
        }

        .col-hsn {
            width: 18mm;
        }

        .col-qty {
            width: 12mm;
        }

        .col-unit {
            width: 16mm;
        }

        .col-rate {
            width: 20mm;
        }

        .col-tax {
            width: 14mm;
        }

        .col-amt {
            width: 15mm;
        }

        /* Bottom totals */
        .bottom-wrap {
            width: 100%;
            margin: 0;
            padding: 0%;
        }

        .bottom-left {
            width: 64%;
        }

        .bottom-right {
            width: 36%;
            vertical-align: top;
            padding-left: 0;
        }

        .box {
            border: 1pt solid #000;
            padding: 3px;
            box-sizing: border-box;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 3px;
        }

        .bold {
            font-weight: 700;
        }

        .small {
            font-size: 10.2px;
        }

        /* Terms & Signature */
        .terms {
            margin-top: 0;
            border-top: 0.8pt solid #000;
            border-bottom: 0.8pt solid #000;
            padding: 3px;
            font-size: 9px;
            box-sizing: border-box;
        }

        .terms ol {
            margin-left: 14px;
            margin-top: 6px;
        }

        .signature {
            width: 100%;
            margin-top: 8px;
        }

        .signature td {
            vertical-align: bottom;
            padding: 3px;
        }

        .footer {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 4mm;
            text-align: center;
            margin-top: 0;
            font-size: 8px;
            color: #333;
            line-height: 9px;
            padding-top: 0;
        }

        table.image-table {
            border-collapse: collapse;
            width: 100%;
        }

        .image-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        /* Helpers */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Full layout variants */
        body.layout_2 .sheet {
            border: 0.8pt solid #9aa4b2;
            font-size: 8.6px;
            line-height: 1.05;
        }
        body.layout_2 .title-box,
        body.layout_2 .terms {
            border-color: #9aa4b2;
        }
        body.layout_2 .items thead th {
            border-color: #9aa4b2;
            color: #334155;
            font-size: 8.5px;
        }
        body.layout_2 .items tbody td {
            border-left-color: #b4bfcc;
            border-right-color: #b4bfcc;
            font-size: 8.4px;
        }
        body.layout_2 .box,
        body.layout_2 .party-left {
            border-color: #9aa4b2;
        }
        body.layout_2 .totals-table td {
            padding: 2.5px 3px;
        }

        body.layout_3 .sheet {
            border: 1pt solid #2b6cb0;
            box-shadow: inset 0 0 0 2px #dbe9ff;
        }
        body.layout_3 .title-box,
        body.layout_3 .terms {
            border-color: #2b6cb0;
            background: #f7fbff;
        }
        body.layout_3 .items {
            border-bottom-color: #2b6cb0;
        }
        body.layout_3 .items thead th {
            border-color: #2b6cb0;
            color: #1e3a8a;
        }
        body.layout_3 .items tbody td {
            border-left-color: #9fbce8;
            border-right-color: #9fbce8;
        }
        body.layout_3 .items tbody tr:nth-child(even) td {
            background: #f8fbff;
        }
        body.layout_3 .box,
        body.layout_3 .party-left {
            border-color: #2b6cb0;
        }
        body.layout_3 .totals-table {
            border: 0.8pt solid #2b6cb0;
        }
        body.layout_3 .totals-table td {
            padding: 4px;
        }
        body.layout_3 .signature {
            border-top: 0.8pt dashed #2b6cb0;
            margin-top: 10px;
        }

        body.layout_4 .sheet {
            border: 1pt solid #198754;
            box-shadow: inset 0 0 0 2px #d8f1e3;
        }
        body.layout_4 .title-box,
        body.layout_4 .terms {
            border-color: #198754;
            background: #f6fdf9;
        }
        body.layout_4 .items {
            border-bottom-color: #198754;
        }
        body.layout_4 .items thead th {
            border-color: #198754;
            color: #0f5132;
        }
        body.layout_4 .items tbody td {
            border-left-color: #8ecfa9;
            border-right-color: #8ecfa9;
        }
        body.layout_4 .items tbody tr:nth-child(odd) td {
            background: #fbfefc;
        }
        body.layout_4 .box,
        body.layout_4 .party-left {
            border-color: #198754;
        }
        body.layout_4 .totals-table {
            border: 0.8pt solid #198754;
        }
        body.layout_4 .totals-table td {
            padding: 4px;
        }
        body.layout_4 .signature {
            border-top: 0.8pt dashed #198754;
            margin-top: 10px;
        }

    </style>
</head>

<body class="{{ $invoiceLayout }}">
    <div class="preview-suite">
        <div class="preview-toolbar no-print">
            <div>
                <span class="preview-chip">Invoice Preview</span>
                <h1>{{ $order->bill_number ?? str_replace('ORDER', 'INV', $order->order_number) }}</h1>
                <p>Review the rendered invoice layout before printing or downloading it.</p>
            </div>
            <div class="preview-actions">
                <button type="button" class="preview-btn" onclick="window.print()">Print</button>
            </div>
        </div>
        <div class="preview-stage">
            @include('order.partials.invoice-pdf-pages')
        </div>
    </div>
</body>

</html>
