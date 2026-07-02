@php
    $invoiceLayout = $invoice_layout ?? \App\Models\Utility::getInvoiceLayout();
    $whatsappAttachmentUrl = $whatsapp_attachment_url ?? ($order->order_invoice ? asset('storage/uploads/order_pdf/' . $order->order_invoice) : '');
    $whatsappAttachmentName = $whatsapp_attachment_name ?? '';
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ \App\Models\Utility::getSetting('website_name') ?? '' }}</title>

    <!-- Bootstrap Css -->
    <link href="{{ asset('public/build/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
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

        @media print {
            .page-break {
                page-break-before: always;
            }

            .no-print {
                display: none !important;
            }
        }

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            color: #000;
            -webkit-print-color-adjust: exact;
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
            /* A4 minus margins */
            margin: 5mm auto;
            padding: ;
            border: 0.8pt solid #000;
            box-sizing: border-box;
            font-size: 9px;
            line-height: 1.1;
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
            width: 16mm;
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
            width: 20mm;
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
            text-align: center;
            margin-top: 0;
            font-size: 8px;
            color: #333;
            line-height: 9px;
            /* border-top: 1px solid #ddd; */
            padding-top: 0px;
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
     @include('chat.comman_chat_file_model')
<div  style="position: relative; width: 100%; height: 100%; overflow: hidden; padding-bottom: 60px">
    <div style="position: relative; width: 100%; height: 100%;" >
        {!! $invoices !!}
    </div>


    <div style="position: fixed; bottom: 0; z-index: 1; width: 100%;display: flex; justify-content:space-between; background: #fff; padding: 10px;">
        <div class="text-end no-print">
                    <!-- Inline Checkbox -->
            <div class="form-check form-check-inline">
                <input class="form-check-input print_selector" type="checkbox" id="original" name="original" value="original" {{ isset($print_options['original']) ? 'checked' : '' }}>
                <label class="form-check-label" for="original">Original</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input print_selector" type="checkbox" id="duplicate" name="duplicate" value="duplicate" {{ isset($print_options['duplicate']) ? 'checked' : '' }}>
                <label class="form-check-label" for="duplicate">Duplicate</label>
            </div>
            {{-- <div class="form-check form-check-inline">
                <input class="form-check-input print_selector" type="checkbox" id="transport" name="transport" value="transport" {{ isset($print_options['transport']) ? 'checked' : '' }}>
                <label class="form-check-label" for="transport">Transport</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input print_selector" type="checkbox" id="office" name="office" value="office" {{ isset($print_options['office']) ? 'checked' : '' }}>
                <label class="form-check-label" for="office">Office</label>
            </div> --}}
        </div>
        {{-- <div class="text-end mb-2 no-print">
            <a href="javascript:window.print()" class="btn btn-success btn-sm"><i class="ri-printer-line align-middle me-1"></i> Print</a>
            <a href="javascript:void(0)" class="btn btn-success btn-sm" id="generate-invoice-btn" data-download="true"><i class="ri-download-2-fill align-middle me-1"></i> Download</a>
            <button class="btn btn-success btn-sm" disabled><i class="ri-whatsapp-line align-middle me-1"></i> What's App</button>
        </div> --}}

                    <div class="text-end mb-2 no-print d-flex align-items-center justify-content-end gap-2">

                <a href="javascript:window.print()" class="btn btn-success btn-sm">
                    <i class="ri-printer-line align-middle me-1"></i> Print
                </a>

                <a href="javascript:void(0)" class="btn btn-success btn-sm" id="generate-invoice-btn" data-download="true">
                    <i class="ri-download-2-fill align-middle me-1"></i> Download
                </a>

                @if (!empty($whatsapp_msg) && !empty($device) && $device->status ==1 && !empty($whatsappAttachmentUrl))
                    <a href="javascript:void(0)" class="btn btn-success btn-sm open-whatsappFile-modal"
                        data-customer_id="{{ $order->customer_id }}"
                        data-phone="{{ $whatsapp_msg }}"
                        data-file="{{ $whatsappAttachmentUrl }}"
                        data-filename="{{ $whatsappAttachmentName }}">
                        <i class="ri-whatsapp-line align-middle me-1"></i> WhatsApp
                    </a>
                @else
                    <button class="btn btn-success btn-sm" disabled>
                        <i class="ri-whatsapp-line align-middle me-1"></i> WhatsApp
                        <span class="badge bg-danger bg-sm text-white ms-2">Device Not Active</span>
                    </button>

                @endif

            </div>

    </div>
</div>

    <script src='//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
    <script src='//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js'></script>

    <!-- JAVASCRIPT -->
    <script src="{{ asset('public/build/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <script>
    function printButtonEventHandler(){
        var currentURL = window.location.href;

        var checkboxes = {
            "transport": "&transport=1",
            "office": "&office=1",
            "duplicate": "&duplicate=1",
            "original": "&original=1"
        };

        $('.print_selector').click(function() {

            $('.print_selector').each(function() {

                var checkboxName = $(this).attr("name");
                var isChecked = $(this).prop("checked");
                if (isChecked) {
                    if (!currentURL.includes(checkboxes[checkboxName])) {
                        currentURL += checkboxes[checkboxName];
                    }

                }
                else {
                    if (currentURL.includes(checkboxes[checkboxName])) {
                        currentURL = currentURL.replace(checkboxes[checkboxName], '');
                    }
                }
            });

            window.location.href = currentURL;
        });

        $(document).on('change', '.print_selector', function () {

            // if nothing is checked, re-check "original"
            if ($('.print_selector:checked').length === 0) {
                $('#original').prop('checked', true);
            }
        });
    }

    function downloadButtonEventHandler(){

        var parentIframe = window.frameElement;
        var downloadURL = parentIframe.getAttribute('data-download');

        var checkboxes = {
            "transport": "&transport=1",
            "office": "&office=1",
            "duplicate": "&duplicate=1",
            "original": "&original=1"
        };

        $('#generate-invoice-btn').click(function() {

            $('.print_selector').each(function() {

                var checkboxName = $(this).attr("name");
                var isChecked = $(this).prop("checked");
                if (isChecked) {

                    if (!downloadURL.includes(checkboxes[checkboxName])) {
                        downloadURL += checkboxes[checkboxName];
                    }
                }
                else {

                    if (downloadURL.includes(checkboxes[checkboxName])) {
                        downloadURL = downloadURL.replace(checkboxes[checkboxName], '');
                    }
                }
            });

            console.log("downloadURL=",downloadURL);

            window.location.href = downloadURL;
        });

    }

    printButtonEventHandler();
    downloadButtonEventHandler();
    </script>
</body>

</html>
