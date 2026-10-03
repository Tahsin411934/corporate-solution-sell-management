<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Customer Details</h2>
                <p class="mt-1 text-sm text-gray-500">View customer profile and opening balance.</p>
            </div>
            <a href="{{ route('customers.index') }}" class="btn-primary inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold text-white">
                Back to Customers
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6 py-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Customer Code</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ $customer->customer_code }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Customer Name</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ $customer->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Company</p>
                    <p class="mt-1 text-gray-900">{{ $customer->company_name ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Phone</p>
                    <p class="mt-1 text-gray-900">{{ $customer->phone ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Email</p>
                    <p class="mt-1 text-gray-900">{{ $customer->email ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Opening Balance</p>
                    <p class="mt-1 text-gray-900">{{ number_format((float) $customer->opening_balance, 2) }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Address</p>
                    <p class="mt-1 whitespace-pre-line text-gray-900">{{ $customer->address ?: '—' }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Notes</p>
                    <p class="mt-1 whitespace-pre-line text-gray-900">{{ $customer->notes ?: '—' }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
