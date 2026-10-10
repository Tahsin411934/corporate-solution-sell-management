<x-app-layout>
    <div class="p-3 sm:p-6">
        <x-data-table id="invoiceTable" title="Invoices" icon="fas fa-file-invoice-dollar"
            createPermission="invoices" buttonText="Create invoice" :buttonLink="route('invoices.create')"
            :ajaxUrl="route('invoices.data')"
            :stackedRows="true"
            id-column="invoices.id"
            :columns="['Invoice number', 'Customer', 'Date', 'Currency', 'Invoice total', 'Received', 'Due', 'Status', 'Actions']"
            :dtColumns="[
                ['data' => 'invoice_number', 'name' => 'invoices.invoice_number'],
                ['data' => 'customer_name', 'name' => 'customers.name'],
                ['data' => 'invoice_date', 'name' => 'invoices.invoice_date'],
                ['data' => 'currency_code', 'name' => 'invoices.currency_code'],
                ['data' => 'total_amount', 'name' => 'invoices.total_amount'],
                ['data' => 'received_amount', 'searchable' => false],
                ['data' => 'balance_amount'],
                ['data' => 'status', 'name' => 'invoices.status'],
                ['data' => 'actions', 'orderable' => false, 'searchable' => false],
            ]" />
    </div>
</x-app-layout>
