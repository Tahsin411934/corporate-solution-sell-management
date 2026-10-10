@php
    $company = $invoice->companySetting;
    $companyName = $company?->company_name ?: 'Corporate Solution';
    $logo = $company?->logo_path ? Storage::disk('public')->url($company->logo_path) : asset('logo.png');
    $received = (string) ($invoice->received_amount ?? '0.00');
    $balance = bcsub($invoice->total_amount, $received, 2);
    $money = fn ($value) => number_format((float) $value, fmod((float) $value, 1) == 0 ? 0 : 2);
    $banks = $company?->bank_details ?? [];
    if (isset($banks['bank_name'])) { $banks = [$banks]; }
    $banks = array_values(array_filter($banks, 'is_array'));
@endphp
<article class="invoice-document {{ $invoice->items->count() <= 5 ? 'invoice-compact' : '' }}">
    <img class="invoice-pad" src="{{ asset('images/invoice-pad.svg') }}" alt="" aria-hidden="true">
    <img class="invoice-watermark" src="{{ $logo }}" alt="" aria-hidden="true">
    <div class="invoice-content">
        <header class="document-header">
            <div class="invoice-brand"><img src="{{ $logo }}" alt="{{ $companyName }} logo"><h1>{{ $companyName }}</h1></div>
            <div class="invoice-heading"><h2>INVOICE</h2><span></span></div>
        </header>
        <div class="invoice-billing">
            <section class="invoice-panel bill-to"><h3>BILL TO</h3><div class="panel-body">
                <strong>{{ $invoice->customer?->name ?? 'Unavailable customer' }}</strong>
                @if($invoice->customer?->company_name)<p>{{ $invoice->customer->company_name }}</p>@endif
                <p>{{ $invoice->customer?->address }}</p>
                @if($invoice->customer?->bin_number)<p class="invoice-small">BIN: {{ $invoice->customer->bin_number }}</p>@endif
                @if($invoice->customer?->tin_number)<p class="invoice-small">TIN: {{ $invoice->customer->tin_number }}</p>@endif
            </div></section>
            <section class="invoice-panel bill-details"><h3>BILL DETAILS</h3><div class="panel-body">
                <p><strong>Date :</strong> {{ $invoice->invoice_date->format('d F Y') }}</p>
                <p><strong>Bill No. :</strong> <b>{{ $invoice->invoice_number }}</b></p>
                @if($invoice->due_date)<p class="invoice-small"><strong>Due :</strong> {{ $invoice->due_date->format('d F Y') }}</p>@endif
            </div></section>
        </div>
        <section class="invoice-title"><h3>PROFESSIONAL FEE BILL</h3><p>Tax Return Preparation &amp; Submission</p></section>
        <table class="invoice-items">
            <colgroup><col class="item-sl"><col><col class="item-amount"></colgroup>
            <thead><tr><th>SL</th><th>PARTICULARS</th><th>AMOUNT ({{ $invoice->currency_code }})</th></tr></thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr><td class="item-number">{{ $loop->iteration }}</td><td>{{ $item->description }}
                        @if((float) $item->quantity != 1)<small>{{ (float) $item->quantity }} {{ $item->unit }} × {{ $money($item->rate) }}</small>@endif
                    </td><td class="money">{{ $money($item->amount) }}</td></tr>
                @endforeach
                @if(bccomp((string) $invoice->discount_amount, '0', 2) !== 0 || bccomp((string) $invoice->tax_amount, '0', 2) !== 0)
                    <tr class="adjustment"><td colspan="2">Subtotal</td><td class="money">{{ $money($invoice->subtotal) }}</td></tr>
                    @if(bccomp((string) $invoice->discount_amount, '0', 2) !== 0)<tr class="adjustment"><td colspan="2">Discount</td><td class="money">−{{ $money($invoice->discount_amount) }}</td></tr>@endif
                    @if(bccomp((string) $invoice->tax_amount, '0', 2) !== 0)<tr class="adjustment"><td colspan="2">Tax ({{ $money($invoice->tax_rate) }}%)</td><td class="money">{{ $money($invoice->tax_amount) }}</td></tr>@endif
                @endif
                <tr class="invoice-total"><td colspan="2">TOTAL AMOUNT PAYABLE</td><td class="money">{{ $money($invoice->total_amount) }}</td></tr>
                <tr class="adjustment"><td colspan="2">RECEIVED</td><td class="money">{{ $money($received) }}</td></tr>
                <tr class="invoice-total"><td colspan="2">{{ $invoice->status === 'cancelled' ? 'HISTORICAL UNPAID AMOUNT (CANCELLED)' : 'DUE' }}</td><td class="money">{{ $money($balance) }}</td></tr>
            </tbody>
        </table>
        <div class="invoice-payment-request">
            <p><strong>Amount in Words:</strong> {{ \App\Support\InvoiceAmountInWords::format((string) $invoice->total_amount, $invoice->currency_code) }}</p>
            @if($invoice->status !== 'cancelled')<p><strong>Payment Request:</strong> {{ $invoice->payment_terms ?: 'Kindly arrange payment of the above professional fee at your earliest convenience.' }}</p>@endif
        </div>
        @if($invoice->status === 'cancelled')
            <section class="invoice-cancelled"><strong>Cancelled invoice — not payable</strong><p>{{ $invoice->cancellation_reason }}</p><p>{{ $invoice->cancelled_at?->format('d M Y H:i') }} · {{ $invoice->cancelledBy?->name }}</p></section>
        @endif
        @if(count($banks))
            <section class="invoice-panel invoice-banks"><h3>PAYMENT DETAILS</h3><div class="bank-grid">
                @foreach($banks as $bank)<div class="bank-account"><h4>BANK {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</h4><div class="bank-body">
                    @if(!empty($bank['account_name']))<p>{{ $bank['account_name'] }}</p>@endif
                    @if(!empty($bank['bank_name']))<p>{{ $bank['bank_name'] }}</p>@endif
                    @if(!empty($bank['account_number']))<p>A/C No.: {{ $bank['account_number'] }}</p>@endif
                    @if(!empty($bank['branch_name']))<p>{{ $bank['branch_name'] }}</p>@endif
                </div></div>@endforeach
            </div></section>
        @endif
        @if($invoice->notes)<section class="invoice-notes"><strong>Notes:</strong> <p>{{ $invoice->notes }}</p></section>@endif
        <div class="invoice-signoff"><p>Sincerely yours,</p><div class="signature-space"></div>
            @if($company?->authorized_person)<strong>{{ $company->authorized_person }}</strong>@endif
            @if($company?->authorized_person_qualifications)<p class="signatory-qualifications">{{ $company->authorized_person_qualifications }}</p>@endif
            @if($company?->authorized_person_designation)<b class="signatory-designation">{{ $company->authorized_person_designation }}</b>@endif
            <b>{{ $companyName }}</b>
        </div>
        <p class="invoice-thanks">{{ $company?->invoice_footer ?: 'Thank you for choosing '.$companyName.'.' }}</p>
        <footer class="invoice-footer">
            @if($company?->address || $company?->email)<p>@if($company?->address)Address: {{ $company->address }}@endif @if($company?->email) E-mail: {{ $company->email }}@endif</p>@endif
            @if($company?->phone)<strong>{{ $company->phone }}</strong>@endif
            @if($company?->tin_number || $company?->bin_number)<p class="invoice-small">@if($company?->tin_number)TIN: {{ $company->tin_number }}@endif @if($company?->bin_number) BIN: {{ $company->bin_number }}@endif</p>@endif
        </footer>
    </div>
</article>
