<x-app-layout>
    <div class="invoice-builder max-w-7xl mx-auto p-4 md:p-6 space-y-5">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <div><p class="text-xs font-semibold text-primary uppercase tracking-wide mb-1">Billing / New invoice</p><h1 class="text-2xl font-semibold text-gray-900">Create invoice</h1><p class="text-sm text-gray-500 mt-1">Choose a customer, add services, then save your draft.</p></div>
            @can('invoices.view')<a href="{{ route('invoices.index') }}" class="border rounded-lg px-4 py-2 bg-white text-sm text-gray-600">Back to invoices</a>@endcan
        </div>
        @if(session('success'))<div role="status" class="p-4 rounded-lg bg-green-50 text-green-800">{{ session('success') }}</div>@endif
        @if($errors->any())
            <div role="alert" class="p-4 rounded-lg bg-red-50 text-red-800"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form action="{{ route('invoices.store') }}" method="POST" id="invoice-create">
            @csrf
            <div class="invoice-workspace">
            <div class="space-y-5 min-w-0">
            <section class="bg-white rounded-xl border p-5">
                <h2 class="font-semibold text-gray-800 mb-4">1. Customer & invoice details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-select label="Customer" name="customer_id" required>
                    <x-slot name="labelAction">
                        @can('customers.create')<button type="button" id="quick-customer-open" class="text-primary text-xs font-semibold border border-primary rounded px-2 py-1" aria-haspopup="dialog">+ Add</button>@endcan
                    </x-slot>
                    @foreach($customers as $customer)<option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->name }}</option>@endforeach
                </x-form-select>
                @if($customers->isEmpty())<p id="customer-empty-hint" class="text-sm text-amber-700">Add a customer to start your invoice.</p>@endif
                <x-form-select label="Referral source" name="referral_source_id" placeholder="No referral">
                    @foreach($referrals as $referral)<option value="{{ $referral->id }}" @selected(old('referral_source_id') == $referral->id)>{{ $referral->name }}</option>@endforeach
                </x-form-select>
                <x-form-input label="Invoice number" name="invoice_number" :value="old('invoice_number', $invoiceNumber)" maxlength="100" required />
                <x-form-input label="Invoice date" name="invoice_date" type="date" :value="old('invoice_date', now()->toDateString())" required />
                <x-form-input label="Due date" name="due_date" type="date" :value="old('due_date')" />
                <x-form-input label="Currency code" name="currency_code" :value="old('currency_code', 'BDT')" pattern="[A-Z]{3}" maxlength="3" required />
                </div>
            </section>
            <section class="bg-white rounded-xl border p-5 space-y-4">
                <div class="flex justify-between items-center gap-3"><div><h2 class="font-semibold">2. Services & items</h2><p class="text-xs text-gray-500 mt-1">Select a service to fill its description and pricing, or enter a custom item.</p></div><span class="text-xs text-gray-500 whitespace-nowrap" id="item-count">1 item</span></div>
                <div id="invoice-items" class="space-y-4"></div>
                <button type="button" id="add-item" class="w-full border border-dashed border-primary text-primary px-4 py-3 rounded-lg text-sm font-semibold"><i class="fas fa-plus mr-2" aria-hidden="true"></i>Add another item</button>
            </section>
            <details class="bg-white rounded-xl border p-5" @if(old('payment_terms') || old('notes')) open @endif>
                <summary class="cursor-pointer font-semibold text-sm text-gray-700">Notes & payment terms <span class="font-normal text-gray-400">(optional)</span></summary>
                <div class="space-y-4 mt-4"><x-form-input label="Payment terms" name="payment_terms" :value="old('payment_terms')" maxlength="255" placeholder="e.g. Payment due within 15 days" />
                <x-form-textarea label="Notes" name="notes" :value="old('notes', '')" maxlength="10000" placeholder="Add any information for your customer" /></div>
            </details>
            </div>
            <aside class="invoice-summary space-y-4">
            <div class="bg-white rounded-xl border p-5 space-y-4">
                <h2 class="font-semibold">3. Review & save</h2>
                <p class="text-xs text-gray-500">Discount applies before tax.</p>
                <x-form-select label="Discount type" name="discount_type" :placeholder="false" :searchable="false">
                    <option value="fixed" @selected(old('discount_type', 'fixed') === 'fixed')>Fixed</option>
                    <option value="percentage" @selected(old('discount_type') === 'percentage')>Percentage</option>
                </x-form-select>
                <x-form-input label="Discount value" name="discount_value" type="number" :value="old('discount_value', 0)" min="0" step="0.01" required />
                <x-form-input label="Tax rate (%)" name="tax_rate" type="number" :value="old('tax_rate', 0)" min="0" max="999.9999" step="0.0001" required />
            <dl class="invoice-total-list border-t pt-4" aria-live="polite" aria-atomic="true">
                @foreach(['subtotal' => 'Subtotal', 'discount' => 'Discount', 'tax' => 'Tax'] as $key => $label)
                    <div><dt>{{ $label }}</dt><dd data-total="{{ $key }}">0.00</dd></div>
                @endforeach
                <div class="invoice-grand-total"><dt>Total <span id="summary-currency">BDT</span></dt><dd data-total="total">0.00</dd></div>
            </dl>
            <details class="text-xs text-gray-500"><summary class="cursor-pointer">Internal cost & profit</summary><dl class="invoice-total-list mt-2"><div><dt>Service cost</dt><dd data-total="cost">0.00</dd></div><div><dt>Profit (excluding tax)</dt><dd data-total="profit">0.00</dd></div></dl></details>
            <p class="text-xs text-gray-500">Preview only. Final amounts are recalculated on save. Saving a draft does not issue the invoice.</p>
            <button type="submit" id="save-invoice" class="bg-primary text-white w-full px-5 py-3 rounded-lg font-semibold disabled:opacity-50" @disabled($customers->isEmpty())><i class="fas fa-check mr-2" aria-hidden="true"></i><span>Save draft invoice</span></button>
            <p id="save-status" role="status" class="text-xs text-gray-500"></p>
            </div>
            </aside>
            </div>
        </form>
    </div>
    @can('customers.create')
        <x-drawer id="quick-customer-drawer" overlayId="quick-customer-overlay" title="Add customer" submitBtnId="quick-customer-save" submitBtnText="Save & select customer" submitOnClick="document.getElementById('quick-customer-form').requestSubmit()">
            <form id="quick-customer-form" class="space-y-4">
                @csrf
                <p class="text-xs text-gray-500">Save and select this customer without leaving your invoice.</p>
                <div id="quick-customer-errors" role="alert" class="hidden text-sm text-red-700 bg-red-50 p-3 rounded"></div>
                <x-form-input label="Customer code" name="customer_code" id="quick-customer-code" readonly placeholder="Assigned automatically" />
                <p class="text-xs text-gray-500">Next serial preview. Final code is assigned when saved.</p>
                <x-form-input label="Customer name" name="name" id="quick-customer-name" required maxlength="200" />
                <x-form-input label="Company name" name="company_name" id="quick-customer-company" maxlength="200" />
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3"><x-form-input label="Phone" name="phone" id="quick-customer-phone" maxlength="30" /><x-form-input label="Email" name="email" id="quick-customer-email" type="email" maxlength="190" /></div>
                <x-form-textarea label="Address" name="address" id="quick-customer-address" maxlength="2000" />
            </form>
        </x-drawer>
    @endcan
    <template id="invoice-item-template">
        <div class="invoice-item border rounded-lg p-4 space-y-3 bg-gray-50/50">
            <div class="flex justify-between items-center"><span class="item-number text-xs font-semibold text-gray-500">Item 1</span><div class="flex gap-3"><button type="button" class="duplicate-item text-xs text-primary">Duplicate</button><button type="button" class="remove-item text-xs text-red-600 disabled:opacity-30">Remove</button></div></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <x-form-select label="Service" name="service_id" :searchable="false" placeholder="Custom item / choose a service">
                @foreach($services as $service)<option value="{{ $service->id }}">{{ $service->name }}</option>@endforeach
            </x-form-select>
            <x-form-input label="Description" name="description" maxlength="500" placeholder="What are you billing for?" required />
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <x-form-input label="Quantity" name="quantity" type="number" value="1" min="0.001" max="999999999.999" step="0.001" required />
            <x-form-input label="Unit" name="unit" value="item" maxlength="30" required />
            <x-form-input label="Rate" name="rate" type="number" value="0" min="0" max="999999999999.99" step="0.01" required />
            <x-form-input label="Unit cost" name="unit_cost" type="number" value="0" min="0" max="999999999999.99" step="0.01" required />
            </div>
            <div class="text-right text-sm text-gray-600">Line total <output class="line-amount font-semibold text-gray-900 ml-2">0.00</output></div>
        </div>
    </template>
    <style>
        .invoice-workspace {display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:24px;align-items:start;}
        .invoice-summary {position:sticky;top:20px;}
        .invoice-total-list > div {display:flex;justify-content:space-between;gap:12px;padding:6px 0;font-size:13px;color:#64748b;}
        .invoice-total-list dd {font-weight:600;color:#1f2937;font-variant-numeric:tabular-nums;}
        .invoice-total-list .invoice-grand-total {margin-top:12px;padding:16px 0;border-top:1px solid #e5e7eb;font-size:18px;font-weight:700;color:#0f172a;}
        .invoice-item:focus-within {border-color:var(--primary-color);}
        @media(max-width:1100px) {.invoice-workspace {grid-template-columns:1fr;}.invoice-summary {position:static;}}
    </style>
    @php
        $initialItems = old('items', [['quantity' => '1', 'unit' => 'item', 'rate' => '0', 'unit_cost' => '0']]);
        $serviceData = $services->keyBy('id');
    @endphp
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const services = {{ Illuminate\Support\Js::from($serviceData) }};
        const initial = {{ Illuminate\Support\Js::from($initialItems) }};
        const form = document.getElementById('invoice-create');
        const container = document.getElementById('invoice-items');
        const customerDialog = document.getElementById('quick-customer-drawer');
        if (customerDialog) {
            const customerForm = document.getElementById('quick-customer-form');
            const saveCustomer = document.getElementById('quick-customer-save');
            const errors = document.getElementById('quick-customer-errors');
            document.getElementById('quick-customer-open').addEventListener('click', async () => {
                openGlobalDrawer('quick-customer-drawer', 'quick-customer-overlay');
                try {
                    const response = await fetch(@json(route('customers.next-code')), {headers: {Accept: 'application/json'}, cache: 'no-store'});
                    if (!response.ok) throw new Error();
                    document.getElementById('quick-customer-code').value = (await response.json()).customer_code;
                } catch { document.getElementById('quick-customer-code').value = ''; }
                document.getElementById('quick-customer-name').focus();
            });
            customerForm.addEventListener('submit', async event => {
                event.preventDefault();
                if (saveCustomer.disabled) return;
                saveCustomer.disabled = true;
                const saveLabel = saveCustomer.querySelector('span');
                saveLabel.textContent = 'Saving…';
                errors.textContent = ''; errors.classList.add('hidden');
                try {
                    const response = await fetch(@json(route('customers.store')), {
                        method: 'POST', credentials: 'same-origin', body: new FormData(customerForm),
                        headers: {Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
                    });
                    const result = await response.json();
                    if (!response.ok) {
                        errors.textContent = result.errors ? Object.values(result.errors).flat().join(' ') : (result.message || 'Unable to save customer.');
                        errors.classList.remove('hidden');
                        return;
                    }
                    const select = form.elements.customer_id;
                    select.add(new Option(result.data.name, result.data.id, true, true));
                    $(select).trigger('change');
                    document.getElementById('save-invoice').disabled = false;
                    document.getElementById('customer-empty-hint')?.remove();
                    document.getElementById('save-status').textContent = 'Customer added and selected.';
                    customerForm.reset(); closeGlobalDrawer('quick-customer-drawer', 'quick-customer-overlay');
                } catch {
                    errors.textContent = 'Unable to confirm the save. Check your connection and retry with the same customer code.';
                    errors.classList.remove('hidden');
                } finally {
                    saveCustomer.disabled = false;
                    saveLabel.textContent = 'Save & select customer';
                }
            });
        }
        const round = value => Math.round((value + Number.EPSILON) * 100) / 100;
        const number = input => Number(input.value) || 0;
        function calculate() {
            let subtotal = 0, cost = 0;
            container.querySelectorAll('.invoice-item').forEach(row => {
                const field = key => row.querySelector('[data-field="' + key + '"]');
                const amount = round(number(field('quantity')) * number(field('rate')));
                subtotal += amount;
                cost += round(number(field('quantity')) * number(field('unit_cost')));
                row.querySelector('.line-amount').textContent = amount.toFixed(2);
            });
            const percentage = form.elements.discount_type.value === 'percentage';
            form.elements.discount_value.max = percentage ? '100' : '999999999999.99';
            const discount = Math.min(subtotal, percentage ? round(subtotal * number(form.elements.discount_value) / 100) : number(form.elements.discount_value));
            const net = round(subtotal - discount);
            const tax = round(net * number(form.elements.tax_rate) / 100);
            const totals = {subtotal, discount, tax, total: round(net + tax), cost, profit: round(net - cost)};
            document.getElementById('summary-currency').textContent = form.elements.currency_code.value.toUpperCase();
            for (const [key, value] of Object.entries(totals)) form.querySelector('[data-total="' + key + '"]').textContent = value.toFixed(2);
        }
        function reindex() {
            container.querySelectorAll('.invoice-item').forEach((row, index) => {
                row.querySelectorAll('[data-field]').forEach(input => {
                    input.name = `items[${index}][${input.dataset.field}]`;
                    input.id = `item-${index}-${input.dataset.field}`;
                    input.parentElement.querySelector('label')?.setAttribute('for', input.id);
                });
                row.querySelector('.remove-item').disabled = container.children.length === 1;
                row.querySelector('.item-number').textContent = 'Item ' + (index + 1);
                row.querySelector('.duplicate-item').disabled = container.children.length >= 100;
            });
            document.getElementById('item-count').textContent = container.children.length + (container.children.length === 1 ? ' item' : ' items');
            document.getElementById('add-item').disabled = container.children.length >= 100;
            calculate();
        }
        function addItem(data = {}) {
            if (container.children.length >= 100) return;
            const row = document.getElementById('invoice-item-template').content.firstElementChild.cloneNode(true);
            row.querySelectorAll('input, select').forEach(input => {
                input.dataset.field = input.name;
                if (data[input.name] != null) input.value = data[input.name];
            });
            row.querySelector('select').addEventListener('change', event => {
                const service = services[event.target.value];
                if (service) {
                    for (const [field, value] of Object.entries({description: service.description || service.name, rate: service.default_rate, unit_cost: service.default_cost})) row.querySelector('[data-field="' + field + '"]').value = value;
                }
                calculate();
            });
            row.querySelector('.remove-item').addEventListener('click', () => { if (container.children.length > 1) { row.remove(); reindex(); } });
            row.querySelector('.duplicate-item').addEventListener('click', () => {
                const values = {};
                row.querySelectorAll('[data-field]').forEach(input => { values[input.dataset.field] = input.value; });
                addItem(values);
            });
            container.appendChild(row);
            reindex();
        }
        Object.values(initial).forEach(addItem);
        if (!container.children.length) addItem();
        document.getElementById('add-item').addEventListener('click', () => {
            addItem();
            container.lastElementChild.querySelector('select').focus();
        });
        form.addEventListener('input', calculate);
        form.addEventListener('change', calculate);
        const invoiceDate = form.elements.invoice_date, dueDate = form.elements.due_date;
        const syncDates = () => { dueDate.min = invoiceDate.value; };
        invoiceDate.addEventListener('change', syncDates);
        syncDates();
        form.addEventListener('submit', () => {
            const button = document.getElementById('save-invoice');
            button.disabled = true;
            button.querySelector('span').textContent = 'Saving…';
            document.getElementById('save-status').textContent = 'Please wait while your draft is saved.';
        });
        window.addEventListener('pageshow', () => {
            const button = document.getElementById('save-invoice');
            button.disabled = form.elements.customer_id.options.length <= 1;
            button.querySelector('span').textContent = 'Save draft invoice';
            document.getElementById('save-status').textContent = '';
        });
    });
    </script>
    @endpush
</x-app-layout>
