<a href="{{ route('invoices.show', $invoice) }}" class="text-primary underline">Details</a>
@can('invoices.print')
    <a href="{{ route('invoices.print', $invoice) }}" target="_blank" rel="noopener" class="text-primary underline ml-3">Print</a>
@endcan
