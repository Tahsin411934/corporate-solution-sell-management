<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Invoice {{ $invoice->invoice_number }}</title>
@include('invoice::partials.styles')
</head><body>
    <div class="no-print" style="padding:16px;text-align:center"><button type="button" onclick="window.print()">Print / Save as PDF</button></div>
    @include('invoice::partials.document')
</body></html>
