<x-app-layout>
    <div class="p-4 sm:p-6">
        @php
            $columns = [
                ['data' => 'payment_date'], ['data' => 'receipt_number'],
                ['data' => 'invoice_number', 'name' => 'invoice.invoice_number'],
                ['data' => 'customer_name', 'name' => 'customer.name'],
                ['data' => 'amount'], ['data' => 'payment_method'],
                ['data' => 'actions', 'orderable' => false, 'searchable' => false],
            ];
        @endphp
        <x-entity-crud id="payment" title="Payment" create-permission="payments" :columns="['Date', 'Receipt', 'Invoice', 'Customer', 'Amount', 'Method', 'Actions']" :dt-columns="$columns" :ajax-url="route('payments.data')" :store-url="route('payments.store')">
            <input type="hidden" name="_method" value="POST">
            <x-form-select label="Invoice" name="invoice_id" id="payment_invoice_id" required>
                @foreach($invoices as $invoice)<option value="{{ $invoice->id }}">{{ $invoice->invoice_number }}</option>@endforeach
            </x-form-select>
            <p class="text-xs text-gray-500">Customer is assigned automatically from the invoice.</p>
            <x-form-input label="Payment date" name="payment_date" id="payment_date" type="date" :value="today()->format('Y-m-d')" required />
            <x-form-input label="Receipt number" name="receipt_number" id="payment_receipt" />
            <x-form-input label="Amount" name="amount" id="payment_amount" type="number" step="0.01" min="0.01" required />
            <x-form-select label="Payment method" name="payment_method" id="payment_method" :searchable="false" :placeholder="null" required>
                @foreach(['cash', 'bank', 'bkash', 'nagad', 'rocket', 'cheque', 'card', 'other'] as $method)<option value="{{ $method }}">{{ ucfirst($method) }}</option>@endforeach
            </x-form-select>
            <x-form-input label="Transaction number" name="transaction_number" id="payment_transaction" />
            <x-form-input label="Account name" name="account_name" id="payment_account" />
            <x-form-textarea label="Notes" name="notes" id="payment_notes" />
        </x-entity-crud>
    </div>
    @include('access.scripts', ['entity' => 'payment', 'baseUrl' => url('payments')])
</x-app-layout>
