<section class="no-print bg-white border rounded-xl p-5 space-y-4">
    <h2 class="text-lg font-semibold">Invoice expenses</h2>
    <div class="flex flex-wrap gap-4 text-sm">
        <p><strong>Estimated service cost:</strong> {{ $invoice->total_cost }} {{ $invoice->currency_code }}</p>
        <p><strong>Actual expenses recorded:</strong> {{ $actualExpense }} {{ $invoice->currency_code }}</p>
    </div>
    <p class="text-sm text-gray-600">Unit cost estimates service cost. Enter actual expenses when the cost is incurred. Gross profit uses the service cost estimate; these entries are shown separately and are not deducted again.</p>
    @if($expenses->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead><tr><th class="p-2">Date</th><th class="p-2">Category / description</th><th class="p-2">Method</th><th class="p-2 text-right">Amount ({{ $invoice->currency_code }})</th></tr></thead>
                <tbody>@foreach($expenses as $expense)
                    <tr class="border-t"><td class="p-2">{{ $expense->expense_date->format('d M Y') }}</td><td class="p-2">{{ $expense->category }}@if($expense->description)<br><span class="text-gray-500">{{ $expense->description }}</span>@endif</td><td class="p-2">{{ ucfirst($expense->payment_method) }}</td><td class="p-2 text-right">{{ $expense->amount }}</td></tr>
                @endforeach</tbody>
            </table>
        </div>
    @else
        <p class="text-sm text-gray-500">No actual expenses recorded.</p>
    @endif
    @if(!in_array($invoice->status, ['draft', 'cancelled'], true))
        @can('expenses.create')
            <details @if($errors->any()) open @endif>
                <summary class="cursor-pointer text-primary font-semibold">Record expense</summary>
                <form action="{{ route('invoices.expenses.store', $invoice) }}" method="POST" class="mt-4 space-y-3" onsubmit="this.querySelector('button[type=submit]').disabled = true;">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <x-form-input label="Actual amount" name="amount" id="invoice-expense-amount" type="number" step="0.01" min="0.01" max="999999999999.99" :value="old('amount', $suggestedExpense)" required />
                        <x-form-input label="Expense date" name="expense_date" id="invoice-expense-date" type="date" :value="old('expense_date', today()->toDateString())" required />
                        <x-form-input label="Category" name="category" id="invoice-expense-category" maxlength="100" :value="old('category', 'Service cost')" required />
                        <x-form-select label="Payment method" name="payment_method" id="invoice-expense-method" :searchable="false" :placeholder="null" required>
                            @foreach(['cash', 'bank', 'bkash', 'nagad', 'rocket', 'card', 'other'] as $method)<option value="{{ $method }}" @selected(old('payment_method', 'cash') === $method)>{{ ucfirst($method) }}</option>@endforeach
                        </x-form-select>
                    </div>
                    <p class="text-xs text-gray-500">Suggested amount is estimated cost minus expenses already recorded. Change it to the actual cost in {{ $invoice->currency_code }}.</p>
                    <x-form-input label="Description" name="description" id="invoice-expense-description" maxlength="500" :value="old('description', 'Service cost for '.$invoice->invoice_number)" />
                    <x-form-textarea label="Notes" name="notes" id="invoice-expense-notes" maxlength="5000" :value="old('notes')" />
                    <button type="submit" class="bg-primary text-white rounded-lg px-4 py-2">Save expense</button>
                </form>
            </details>
        @endcan
    @else
        <p class="text-sm text-gray-500">Issue the invoice before recording expenses. Existing expenses remain recorded if an invoice is cancelled.</p>
    @endif
</section>
