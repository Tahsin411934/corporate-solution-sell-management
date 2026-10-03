<style>
    .invoice-document {color:#1f2937;background:white;padding:32px;font:14px/1.6 Arial,sans-serif;max-width:1000px;margin:auto;}
    .invoice-document h1 {font-size:24px;font-weight:700;} .invoice-document h2 {font-size:22px;font-weight:700;} .invoice-document h3 {font-weight:700;}
    .document-header {display:flex;justify-content:space-between;gap:24px;border-bottom:2px solid #2563eb;padding-bottom:20px;}
    .invoice-document section {margin:20px 0;} .invoice-document p {white-space:pre-line;overflow-wrap:anywhere;}
    .invoice-document table {width:100%;border-collapse:collapse;margin:20px 0;}
    .invoice-document th,.invoice-document td {border-bottom:1px solid #e5e7eb;padding:10px;text-align:left;overflow-wrap:anywhere;}
    .invoice-document th {background:#f3f4f6;} .document-totals {max-width:400px;margin:20px 0 20px auto;}
    .document-totals div {display:flex;justify-content:space-between;gap:20px;padding:5px 0;} .document-totals dd {font-weight:600;}
    .invoice-document footer {border-top:1px solid #e5e7eb;padding-top:16px;margin-top:24px;white-space:pre-line;}
    @media screen and (max-width:639px) {
        .invoice-document {padding:16px;font-size:12px;overflow-x:auto;}
        .document-header {flex-direction:column;gap:12px;}
        .invoice-document h1 {font-size:20px;}
        .invoice-document table {min-width:480px;}
        .invoice-document th,.invoice-document td {padding:8px;}
        .document-totals {max-width:none;}
    }
    @media print {@page {size:A4;margin:12mm;} .no-print {display:none!important;} body {margin:0;background:white;} .invoice-document {padding:0;max-width:none;} thead {display:table-header-group;} tr {break-inside:avoid;} }
</style>
