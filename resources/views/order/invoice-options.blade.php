<style>
    .invoice-option-shell {
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.94);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .invoice-option-shell .option-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e2e8f0;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }

    .invoice-option-shell .option-header h5 {
        margin: 0;
        color: #0f172a;
        font-weight: 700;
    }

    .invoice-option-shell .option-header p {
        margin: .35rem 0 0;
        color: #64748b;
        font-size: .85rem;
    }

    .invoice-option-shell .preview-frame {
        padding: .9rem;
        background: linear-gradient(180deg, #f8fafc 0%, #eef3f8 100%);
    }

    .invoice-option-shell iframe {
        width: 100%;
        height: 80vh;
        border: 0;
        border-radius: 18px;
        background: #fff;
        box-shadow: inset 0 0 0 1px #dce4ee;
    }
</style>

<div class="invoice-option-shell">
    <div class="option-header">
        <h5>Invoice Preview</h5>
        <p>Preview the selected invoice output before downloading or printing.</p>
    </div>
    <div class="preview-frame">
        <iframe id="invoicePreview" src="{{ route('orders.invoice.preview',[$order->id]) }}?original=1" data-download="{{ route('orders.invoice_new',$order->id) }}?original=1"></iframe>
    </div>
</div>
