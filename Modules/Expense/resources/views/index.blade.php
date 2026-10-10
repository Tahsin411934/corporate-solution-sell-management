<x-app-layout>
    <div class="p-4 sm:p-6">
        @php
            $columns = [
                ['data' => 'expense_date'], ['data' => 'category'], ['data' => 'description'],
                ['data' => 'amount'], ['data' => 'payment_method'],
                ['data' => 'invoice_number', 'name' => 'invoice.invoice_number'],
                ['data' => 'referral_source', 'name' => 'expenses.referral_source'],
                ['data' => 'actions', 'orderable' => false, 'searchable' => false],
            ];
        @endphp
        <x-entity-crud id="expense" title="Expense" create-permission="expenses" :columns="['Date', 'Category', 'Description', 'Amount', 'Method', 'Invoice', 'Referral', 'Actions']" :dt-columns="$columns" :ajax-url="route('expenses.data')" :store-url="route('expenses.store')">
            <input type="hidden" name="_method" value="POST">
            <x-form-input label="Expense date" name="expense_date" id="expense_date" type="date" :value="today()->format('Y-m-d')" required />
            <x-form-input label="Category" name="category" id="expense_category" required />
            <x-form-input label="Description" name="description" id="expense_description" />
            <x-form-input label="Amount" name="amount" id="expense_amount" type="number" step="0.01" min="0.01" required />
            <x-form-select label="Payment method" name="payment_method" id="expense_method" :searchable="false" :placeholder="null" required>
                @foreach(['cash', 'bank', 'bkash', 'nagad', 'rocket', 'card', 'other'] as $method)<option value="{{ $method }}">{{ ucfirst($method) }}</option>@endforeach
            </x-form-select>
            <x-form-select label="Invoice (optional)" name="invoice_id" id="expense_invoice" placeholder="No invoice">
                @foreach($invoices as $invoice)<option value="{{ $invoice->id }}">{{ $invoice->invoice_number }}</option>@endforeach
            </x-form-select>
            <x-form-input label="Referral source (optional)" name="referral_source" id="expense_referral" maxlength="200" placeholder="Type referral source" />
            <x-form-textarea label="Notes" name="notes" id="expense_notes" />
        </x-entity-crud>
    </div>
    @include('access.scripts', ['entity' => 'expense', 'baseUrl' => url('expenses')])
</x-app-layout>
