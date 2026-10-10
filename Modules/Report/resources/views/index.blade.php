<x-app-layout>
    <div class="p-6 space-y-5">
        <h1 class="text-2xl font-semibold">Reports</h1>
        @if($errors->any())<div role="alert" class="bg-red-50 text-red-800 p-3 rounded-lg">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <form method="GET" action="{{ route('reports.index') }}" class="bg-white border rounded-xl p-5 grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <x-form-select label="Report" name="report" :placeholder="false" :searchable="false">
                @foreach($types as $value => $label)<option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>@endforeach
            </x-form-select>
            <x-form-input label="From date" name="from" type="date" :value="$filters['from'] ?? ''" />
            <x-form-input label="To date" name="to" type="date" :value="$filters['to'] ?? ''" />
            <div class="flex gap-3 items-center"><button class="bg-primary text-white rounded-lg px-4 py-2">Apply</button><a href="{{ route('reports.index', ['report' => $type]) }}" class="text-primary underline">Reset</a></div>
        </form>
        @if($type === 'customers')
            <p class="text-sm text-amber-800 bg-amber-50 rounded-lg p-3">All-time receivables from the database view; date filters do not apply. Includes draft and cancelled invoices. This view combines currencies per customer; use invoice dues for currency-specific figures.</p>
        @elseif($type === 'invoices')
            <p class="text-sm text-gray-600">Filtered by invoice date. Due amounts include all non-deleted payments, regardless of payment date. Draft and cancelled invoices are included.</p>
        @elseif($type === 'profit')
            <p class="text-sm text-gray-600">Accrual gross profit = subtotal − discount − service costs, excluding tax, draft and cancelled invoices. Payments are not revenue. Expenses are reported separately: their schema has no currency, so net profit cannot safely be calculated across currencies. Do not count service costs again as expenses.</p>
        @elseif($type === 'expenses')
            <p class="text-sm text-gray-600">Filtered by expense date. Expenses have no currency field; the total is not currency-normalized.</p>
        @else
            <p class="text-sm text-gray-600">Filtered by payment date. Soft-deleted payments are excluded.</p>
        @endif
        @foreach($summary as $group)
            <dl class="bg-white border rounded-xl p-4 flex flex-wrap gap-6">
                @foreach($group as $label => $value)<div><dt class="text-sm text-gray-500">{{ $label }}</dt><dd class="font-semibold">{{ $label === 'Currency' ? $value : number_format((float) $value, 2) }}</dd></div>@endforeach
            </dl>
        @endforeach
        @php
            $dtColumns = collect(array_keys($columns))->map(fn ($column) => ['data' => $column, 'name' => $column, 'defaultContent' => '—'])->all();
        @endphp
        <x-data-table :id="'report-'.$type" :title="$types[$type]" icon="fas fa-chart-line" :buttonId="null"
            :columns="array_values($columns)" :dtColumns="$dtColumns"
            :ajaxUrl="route('reports.data', array_merge($filters, ['report' => $type]))" />
        <p class="text-xs text-gray-500">Table exports contain the currently loaded page, not the complete report.</p>
    </div>
</x-app-layout>
