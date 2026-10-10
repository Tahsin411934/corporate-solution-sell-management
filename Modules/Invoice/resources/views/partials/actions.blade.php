<div class="flex flex-wrap items-center gap-3">
<a href="{{ route('invoices.show', $invoice) }}" class="text-primary underline">Details</a>
@can('invoices.print')
    <a href="{{ route('invoices.print', $invoice) }}" target="_blank" rel="noopener" class="text-primary underline">Print</a>
@endcan
</div>
