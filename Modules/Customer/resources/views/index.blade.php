<x-app-layout>
    @php
        $customerTableColumns = [
            ['data' => 'customer_code'], ['data' => 'name'], ['data' => 'phone'],
            ['data' => 'email'], ['data' => 'opening_balance', 'className' => 'text-right'],
            ['data' => 'actions', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
        ];
    @endphp

    <x-entity-crud
        id="customer"
        title="Customer"
        icon="fa-solid fa-users"
        :columns="['Code', 'Customer', 'Phone', 'Email', 'Opening receivable', 'Actions']"
        :dt-columns="$customerTableColumns"
        ajax-url="{{ route('customers.data') }}"
        store-url="{{ route('customers.store') }}"
        update-url="{{ route('customers.update', ':id') }}"
        show-url="{{ route('customers.show', ':id') }}"
        destroy-url="{{ route('customers.destroy', ':id') }}"
        drawer-title="Customer"
        id-field="customer_id"
        :order="[[0, 'desc']]"
    >
        <input type="hidden" name="_method" id="customer-method" value="POST">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-form-input label="Customer code" name="customer_code" id="customer_code" readonly placeholder="Assigned automatically" />
            <x-form-input label="Customer name" name="name" id="customer_name" required />
            <x-form-input label="Company name" name="company_name" id="customer_company_name" />
            <x-form-input label="Phone" name="phone" id="customer_phone" />
            <x-form-input label="Email" name="email" id="customer_email" type="email" />
            <x-form-input label="Opening receivable" name="opening_balance" id="customer_opening_balance" type="number" step="0.01" min="0" value="0" />
            <x-form-input label="TIN number" name="tin_number" id="customer_tin" />
            <x-form-input label="BIN number" name="bin_number" id="customer_bin" />
            <x-form-input label="Address" name="address" id="customer_address" />
            <x-form-input label="Notes" name="notes" id="customer_notes" />
        </div>
    </x-entity-crud>

    @push('scripts')
        <script>
            document.getElementById('customerAddButton')?.addEventListener('click', async () => {
                try {
                    const response = await fetch(@json(route('customers.next-code')), {headers: {Accept: 'application/json'}, cache: 'no-store'});
                    if (response.ok && !document.getElementById('customer-id').value) document.getElementById('customer_code').value = (await response.json()).customer_code;
                } catch {}
            });
            Crud.register('customer', 'fill', function (customer) {
                const fields = {
                    customer_code: customer.customer_code, customer_name: customer.name,
                    customer_company_name: customer.company_name, customer_phone: customer.phone,
                    customer_email: customer.email, customer_opening_balance: customer.opening_balance,
                    customer_tin: customer.tin_number, customer_bin: customer.bin_number,
                    customer_address: customer.address, customer_notes: customer.notes
                };
                Object.entries(fields).forEach(([id, value]) => {
                    const field = document.getElementById(id);
                    if (field) field.value = value ?? '';
                });
            });

            $(document).on('click', '.edit-customer', function () {
                const customer = JSON.parse(this.dataset.customer);
                Crud.get('customer', 'fill')(customer);
                $('#customer-id').val(customer.id);
                $('#customer-form').attr('action', `{{ url('customers') }}/${customer.id}`);
                $('#customer-method').val('PUT');
                $('#drawerTitle').text('Edit Customer');
                openGlobalDrawer('customer-drawer', 'customer-overlay');
            });
        </script>
    @endpush
</x-app-layout>
