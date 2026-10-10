<x-app-layout>
    @include('invoice::partials.styles')
    <div class="p-6 space-y-4">
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('invoices.index') }}" class="text-primary underline">Back to invoices</a>
            @can('invoices.print')<a href="{{ route('invoices.print', $invoice) }}" target="_blank" rel="noopener" class="bg-primary text-white rounded-lg px-4 py-2">Print invoice</a>@endcan
            @if($invoice->status === 'draft')
                @can('invoices.issue')<form action="{{ route('invoices.issue', $invoice) }}" method="POST">@csrf<button class="bg-primary text-white rounded-lg px-4 py-2">Issue invoice</button></form>@endcan
            @endif
        </div>
        @if(session('success'))<p role="status" class="p-3 bg-green-50 text-green-800">{{ session('success') }}</p>@endif
        @if($errors->any())<div role="alert" class="p-3 bg-red-50 text-red-800">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <p class="no-print text-sm text-gray-600"><strong>Referral source:</strong> {{ $invoice->referral_source ?: '—' }}</p>
        <div class="no-print flex flex-wrap gap-4 text-sm text-gray-700">
            <p><strong>Status:</strong> {{ $invoice->status === 'issued' ? 'Unpaid' : ucfirst($invoice->status) }}</p>
            <p><strong>Total:</strong> {{ $invoice->total_amount }} {{ $invoice->currency_code }}</p>
            <p><strong>Paid:</strong> {{ number_format((float) ($invoice->received_amount ?? 0), 2) }}</p>
            <p><strong>Due:</strong> {{ bcsub($invoice->total_amount, (string) ($invoice->received_amount ?? '0'), 2) }}</p>
        </div>
        <div class="invoice-preview">@include('invoice::partials.document')</div>
        @include('invoice::partials.expenses')
        @if($invoice->status !== 'cancelled' && $invoice->payments->isEmpty())
            @can('invoices.cancel')
                <form action="{{ route('invoices.cancel', $invoice) }}" method="POST" class="bg-white border rounded-xl p-5 space-y-3" onsubmit="return confirm('Cancel this invoice? This cannot be undone.');">
                    @csrf
                    <x-form-input label="Cancellation reason" name="cancellation_reason" :value="old('cancellation_reason')" maxlength="500" required />
                    <button class="bg-red-600 text-white rounded-lg px-4 py-2">Cancel invoice</button>
                </form>
            @endcan
        @endif
    </div>
</x-app-layout>
