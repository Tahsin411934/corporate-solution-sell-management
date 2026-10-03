@php
    $company = $invoice->companySetting;
    $received = (string) ($invoice->received_amount ?? '0.00');
    $balance = bcsub($invoice->total_amount, $received, 2);
@endphp
<article class="invoice-document">
    <header class="document-header">
        <div>
            <h1>{{ $company?->company_name ?? 'Invoice' }}</h1>
            @if($company)<p>{{ $company->address }}</p><p>{{ $company->phone }} {{ $company->email }}</p>
                @if($company->tin_number)<p>TIN: {{ $company->tin_number }}</p>@endif
                @if($company->bin_number)<p>BIN: {{ $company->bin_number }}</p>@endif
            @endif
        </div>
        <div><h2>INVOICE</h2><p>{{ $invoice->invoice_number }}</p><p>Date: {{ $invoice->invoice_date->format('d M Y') }}</p>
            <p>Due: {{ $invoice->due_date?->format('d M Y') ?? '—' }}</p><p>Status: {{ ucfirst($invoice->status) }}</p></div>
    </header>
    <section><h3>Bill to</h3><p>{{ $invoice->customer?->name ?? 'Unavailable customer' }}</p><p>{{ $invoice->customer?->company_name }}</p>
        <p>{{ $invoice->customer?->address }}</p><p>{{ $invoice->customer?->phone }} {{ $invoice->customer?->email }}</p></section>
    <table><thead><tr><th>#</th><th>Description</th><th>Quantity</th><th>Unit</th><th>Rate</th><th>Amount</th></tr></thead>
        <tbody>@foreach($invoice->items as $item)<tr><td>{{ $loop->iteration }}</td><td>{{ $item->description }}</td><td>{{ $item->quantity }}</td><td>{{ $item->unit }}</td><td>{{ $item->rate }}</td><td>{{ $item->amount }}</td></tr>@endforeach</tbody>
    </table>
    <dl class="document-totals">
        <div><dt>Currency</dt><dd>{{ $invoice->currency_code }}</dd></div>
        <div><dt>Subtotal</dt><dd>{{ $invoice->subtotal }}</dd></div>
        <div><dt>Discount</dt><dd>{{ $invoice->discount_amount }}</dd></div>
        <div><dt>Tax ({{ $invoice->tax_rate }}%)</dt><dd>{{ $invoice->tax_amount }}</dd></div>
        <div><dt>Total</dt><dd>{{ $invoice->total_amount }}</dd></div>
        <div><dt>Received</dt><dd>{{ $received }}</dd></div>
        <div><dt>{{ $invoice->status === 'cancelled' ? 'Historical unpaid amount (cancelled)' : 'Outstanding balance' }}</dt><dd>{{ $balance }}</dd></div>
    </dl>
    @if($invoice->status === 'cancelled')<section><h3>Cancelled invoice — not payable</h3><p>{{ $invoice->cancellation_reason }}</p><p>{{ $invoice->cancelled_at?->format('d M Y H:i') }} · {{ $invoice->cancelledBy?->name }}</p></section>@endif
    <section><h3>Payments</h3>
        <table><thead><tr><th>Date</th><th>Receipt</th><th>Method</th><th>Amount</th></tr></thead><tbody>
            @forelse($invoice->payments as $payment)<tr><td>{{ $payment->payment_date->format('d M Y') }}</td><td>{{ $payment->receipt_number ?? '—' }}</td><td>{{ ucfirst($payment->payment_method) }}</td><td>{{ $payment->amount }}</td></tr>
            @empty<tr><td colspan="4">No payments received.</td></tr>@endforelse
        </tbody></table>
    </section>
    @if($invoice->payment_terms)<section><h3>Payment terms</h3><p>{{ $invoice->payment_terms }}</p></section>@endif
    @if($invoice->notes)<section><h3>Notes</h3><p>{{ $invoice->notes }}</p></section>@endif
    @if($company?->bank_details)<section><h3>Bank details</h3>@foreach($company->bank_details as $key => $value)<p>{{ is_string($key) ? ucfirst(str_replace('_', ' ', $key)).': ' : '' }}{{ is_scalar($value) ? $value : json_encode($value) }}</p>@endforeach</section>@endif
    <footer>{{ $company?->invoice_footer }} @if($company?->authorized_person)<p>Authorized by: {{ $company->authorized_person }}</p>@endif</footer>
</article>
