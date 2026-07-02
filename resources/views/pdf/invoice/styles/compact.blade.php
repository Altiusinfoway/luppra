<style>
	    @page { size: A4 portrait; margin: 7mm; }
	    body { margin: 0; padding: 0; color: #111827; font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 9px; line-height: 1.25; }
	    .invoice-document { width: 100%; }
	    .invoice-page { width: 100%; page-break-after: always; }
	    .invoice-page-last { page-break-after: auto; }
	    .invoice-shell, .section-table, .meta-table, .party-table, .items-table, .summary-table, .totals-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
	    .invoice-shell { border: 1px solid #6b7280; }
	    .section-cell { padding: 0; vertical-align: top; }
	    .section-table td { padding: 4px 5px; vertical-align: top; }
	    .header-box, .section-heading, .totals-highlight { background: #eceff3; }
    .header-box { border-bottom: 1px solid #6b7280; }
    .invoice-title { font-size: 13px; font-weight: 700; text-align: center; letter-spacing: 0.25px; }
    .company-name { font-size: 14px; font-weight: 700; }
    .section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; }
    .section-heading { font-weight: 700; border-top: 1px solid #6b7280; border-bottom: 1px solid #6b7280; }
	    .logo-cell { width: 82px; text-align: center; vertical-align: middle; }
	    .logo-image { max-width: 72px; max-height: 72px; }
	    .company-detail-cell { vertical-align: middle; }
	    .party-box { border: 1px solid #6b7280; }
	    .compact-party-cell { padding: 2px 3px !important; }
	    .compact-party-title { margin-bottom: 1px; font-size: 9px; }
	    .compact-party-line { line-height: 1.05; margin-top: 1px; }
	    .items-table th, .items-table td, .summary-table td, .totals-table td { border: 1px solid #6b7280; padding: 3px 4px; vertical-align: top; }
    .items-table th { background: #eceff3; font-weight: 700; }
    .text-right { text-align: right; }
	    .text-center { text-align: center; }
	    .text-muted { color: #4b5563; }
	    .small-text { font-size: 8px; }
	    .terms-list { margin: 0; padding-left: 14px; }
	    .terms-list li { margin-bottom: 2px; }
	    .signature-box { height: 46px; text-align: right; vertical-align: bottom; }
	    .bottom-summary-wrap, .terms-sign-wrap { width: 100%; border-collapse: collapse; table-layout: fixed; }
	    .bottom-summary-left { width: 64%; vertical-align: top; border-right: 1px solid #6b7280; }
	    .bottom-summary-right { width: 36%; vertical-align: top; }
	    .bottom-box-table { margin-top: 0; margin-bottom: 0; }
	    .bottom-box-table td { padding: 1px 2px !important; font-size: 7.8px; line-height: 1.0; }
	    .amount-words-row { height: 20px; }
	    .terms-sign-wrap td { border-top: 1px solid #6b7280; padding: 2px 3px; vertical-align: top; }
	    .terms-sign-left { width: 68%; }
	    .terms-sign-right { width: 32%; text-align: right; vertical-align: bottom; }
	    .signature-fixed-space { height: 12px; }
	    .footer-terms-title { font-size: 9px; font-weight: 700; margin-bottom: 1px; }
	    .footer-terms-list { padding-left: 12px; margin: 0; }
	    .footer-terms-list li { margin-bottom: 0; }
	    .footer-bar-inline { border-top: 1px solid #6b7280; text-align: center; padding: 2px 3px !important; font-size: 7px; line-height: 1.05; color: #374151; }
	    .items-table, .section-table, .party-table, .summary-table, .totals-table, .invoice-shell tr { page-break-inside: avoid; }
	    .blank-item-row td { height: 13px; }
	</style>
