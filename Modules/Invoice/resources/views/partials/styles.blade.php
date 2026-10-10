<style>
    .invoice-document,.invoice-document * {box-sizing:border-box;}
    .invoice-document {position:relative;isolation:isolate;width:210mm;min-height:296mm;max-width:none;margin:0 auto;background:#fff;color:#171717;font:10.5pt/1.3 Arial,Helvetica,sans-serif;box-shadow:0 4px 24px #0f172a18;}
    .invoice-document h1,.invoice-document h2,.invoice-document h3,.invoice-document h4,.invoice-document p {margin:0;}
    .invoice-pad {position:absolute;inset:0;width:100%;height:100%;object-fit:fill;z-index:-2;pointer-events:none;}
    .invoice-watermark {position:absolute;top:48%;left:36%;width:34%;height:auto;opacity:.085;z-index:-1;pointer-events:none;}
    .invoice-content {position:relative;display:flex;flex-direction:column;min-height:296mm;padding:8mm 9mm 5mm;}
    .invoice-document p {white-space:pre-line;overflow-wrap:anywhere;}
    .document-header {display:flex;align-items:center;justify-content:space-between;min-height:35mm;margin-bottom:7mm;}
    .invoice-brand {width:47%;text-align:left;}
    .invoice-brand img {display:block;width:27mm;height:25mm;object-fit:contain;margin-left:16mm;}
    .invoice-brand h1 {font:700 21pt/1.1 Georgia,'Times New Roman',serif;letter-spacing:-.9px;color:#151515;}
    .invoice-heading {width:43%;text-align:center;color:#062d63;}
    .invoice-heading h2 {font-size:36pt;font-weight:900;letter-spacing:1px;line-height:1;}
    .invoice-heading span {display:block;width:31mm;height:.4mm;background:#075493;margin:3mm auto 0;}
    .invoice-billing {display:grid;grid-template-columns:1.7fr 1fr;gap:2mm;}
    .invoice-panel {border:.25mm solid #62b3e5;break-inside:avoid;}
    .invoice-panel h3,.invoice-items th {background:linear-gradient(110deg,#0578b8,#09518e);color:#fff;font-size:11.5pt;font-weight:700;padding:2mm 3mm;}
    .panel-body {min-height:20mm;padding:3mm;}
    .bill-to strong {font-size:12pt;}
    .bill-details p + p {margin-top:1.3mm;}
    .bill-details {font-size:10pt;}
    .invoice-title {text-align:center;margin:4mm auto 3.5mm;}
    .invoice-title h3 {font-size:20pt;line-height:1.15;font-weight:800;color:#071f50;}
    .invoice-title p {display:inline-block;font-size:14pt;font-weight:700;color:#007ac0;border-bottom:.4mm solid #1679b8;padding:1mm 4mm 1.5mm;}
    .invoice-items {width:100%;border-collapse:collapse;table-layout:fixed;margin:0;}
    .invoice-items .item-sl {width:9%;}.invoice-items .item-amount {width:23%;}
    .invoice-items th,.invoice-items td {border:.25mm solid #66b8e8;}
    .invoice-items th {text-align:center;padding:2mm 1mm;font-size:11pt;}
    .invoice-items td {padding:2mm 3mm;overflow-wrap:anywhere;vertical-align:top;}
    .invoice-items tr:nth-child(even) td {background:#eaf6fc;}
    .invoice-items .item-number {text-align:center;}
    .invoice-items small {display:block;font-size:8pt;color:#566274;}
    .invoice-items .money {text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap;}
    .invoice-items .adjustment td {font-size:9pt;padding:1mm 3mm;text-align:right;}
    .invoice-items .invoice-total td {background:#bfe5f8;color:#081f4d;font-weight:800;text-align:right;}
    .invoice-payment-request {margin:4mm 0;}
    .invoice-payment-request p + p {margin-top:3mm;}
    .invoice-payment-request strong,.invoice-notes strong {color:#071f50;}
    .invoice-banks h3 {text-align:center;}
    .bank-grid {display:grid;grid-template-columns:repeat(2,minmax(0,1fr));}
    .bank-account + .bank-account {border-left:.25mm solid #cbe5f6;}
    .bank-account h4 {background:#e4f3fc;color:#07386e;padding:1.5mm 5mm;font-size:11pt;}
    .bank-body {padding:1.5mm 5mm 3mm;}
    .bank-body p + p {margin-top:1mm;}
    .invoice-balance {font-size:8pt;color:#526071;margin-top:2mm!important;}
    .invoice-receipts,.invoice-notes,.invoice-cancelled {margin-top:2mm;font-size:9pt;break-inside:avoid;}
    .invoice-cancelled {border:1px solid #b91c1c;color:#b91c1c;padding:2mm;}
    .invoice-signoff {align-self:flex-end;min-width:43mm;text-align:right;margin:3mm 0 2mm;break-inside:avoid;font-size:10pt;}
    .signature-space {height:8mm;border-bottom:.4mm dotted #333;margin-bottom:1mm;}
    .invoice-signoff strong,.invoice-signoff b {display:block;color:#072652;}
    .invoice-thanks {text-align:center;font-size:11pt;font-style:italic;margin:1mm 0 3mm!important;}
    .invoice-footer {margin-top:auto;text-align:center;font-size:9pt;padding-top:3mm;break-inside:avoid;}
    .invoice-footer strong {font-size:12pt;color:#001c35;}.invoice-small {font-size:8pt;}
    .invoice-compact .invoice-items td {padding-top:1.7mm;padding-bottom:1.7mm;}
    @media screen and (max-width:850px) {.invoice-document {margin-left:0;}.invoice-preview {overflow-x:auto;}}
    @media print {
        @page {size:A4;margin:0;}
        html,body {margin:0!important;padding:0!important;background:#fff!important;}
        .no-print {display:none!important;}
        .invoice-document {-webkit-print-color-adjust:exact;print-color-adjust:exact;box-shadow:none;margin:0;width:210mm;}
        .invoice-document thead {display:table-header-group;}
        .invoice-document tr {break-inside:avoid;}
        .invoice-document .invoice-content {padding-bottom:5mm;}
    }
</style>
