<style>
	    @page { size: A4 portrait; margin: 7mm; }
	    body { margin: 0; padding: 0; color: #000000; font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 10px; line-height: 1.35; }
	    .invoice-document { width: 100%; }
	    .invoice-page { width: 100%; page-break-after: always; }
	    .invoice-page-last { page-break-after: auto; }
	    .invoice-shell, .section-table, .meta-table, .party-table, .items-table, .summary-table, .totals-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
	    .invoice-shell { border: 1px solid #000000; }
	    .section-cell { padding: 0; vertical-align: top; }
	    .section-table td { padding: 5px 6px; vertical-align: top; }
	    .header-box, .section-heading, .totals-highlight { background: #f2f2f2; }
    .header-box { border-bottom: 1px solid #000000; }
    .invoice-title { font-size: 14px; font-weight: 700; text-align: center; letter-spacing: 0.3px; }
    .company-name { font-size: 16px; font-weight: 700; }
    .section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .section-heading { font-weight: 700; border-top: 1px solid #000000; border-bottom: 1px solid #000000; }
	    .logo-cell { width: 92px; text-align: center; vertical-align: middle; }
	    .logo-image { max-width: 82px; max-height: 82px; }
	    .company-detail-cell { vertical-align: middle; }
	    .party-box { border: 1px solid #000000; }
	    .compact-party-cell { padding: 3px 4px !important; }
	    .compact-party-title { margin-bottom: 2px; font-size: 10px; }
	    .compact-party-line { line-height: 1.1; margin-top: 1px; }
	    .items-table th, .items-table td, .summary-table td, .totals-table td { border: 1px solid #000000; padding: 4px 5px; vertical-align: top; }
    .items-table th { background: #f2f2f2; font-weight: 700; }
    .text-right { text-align: right; }
	    .text-center { text-align: center; }
	    .text-muted { color: #444444; }
	    .small-text { font-size: 9px; }
	    .terms-list { margin: 0; padding-left: 15px; }
	    .terms-list li { margin-bottom: 3px; }
	    .signature-box { height: 54px; text-align: right; vertical-align: bottom; }
	    .bottom-summary-wrap, .terms-sign-wrap { width: 100%; border-collapse: collapse; table-layout: fixed; }
	    .bottom-summary-left { width: 64%; vertical-align: top; border-right: 1px solid #000000; }
	    .bottom-summary-right { width: 36%; vertical-align: top; }
	    .bottom-box-table { margin-top: 0; margin-bottom: 0; }
	    .bottom-box-table td { padding: 2px 3px !important; font-size: 8.3px; line-height: 1.05; }
	    .amount-words-row { height: 22px; }
	    .terms-sign-wrap td { border-top: 1px solid #000000; padding: 2px 4px; vertical-align: top; }
	    .terms-sign-left { width: 68%; }
	    .terms-sign-right { width: 32%; text-align: right; vertical-align: bottom; }
	    .signature-fixed-space { height: 16px; }
	    .footer-terms-title { font-size: 10px; font-weight: 700; margin-bottom: 2px; }
	    .footer-terms-list { padding-left: 14px; margin: 0; }
	    .footer-terms-list li { margin-bottom: 1px; }
	    .footer-bar-inline { border-top: 1px solid #000000; text-align: center; padding: 2px 4px !important; font-size: 7.4px; line-height: 1.1; color: #333333; }
	    .items-table, .section-table, .party-table, .summary-table, .totals-table, .invoice-shell tr { page-break-inside: avoid; }
	    .blank-item-row td { height: 15px; }
	</style>
