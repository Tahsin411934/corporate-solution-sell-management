<x-app-layout>
    <div class="p-4 sm:p-6">
        @php
            $columns = [
                ['data' => 'created_at'], ['data' => 'user_name', 'name' => 'user.name'],
                ['data' => 'event'], ['data' => 'auditable_type'], ['data' => 'auditable_id'],
                ['data' => 'old_values', 'orderable' => false, 'searchable' => false],
                ['data' => 'new_values', 'orderable' => false, 'searchable' => false],
                ['data' => 'ip_address'], ['data' => 'user_agent'],
            ];
        @endphp
        <x-data-table id="auditLogsTable" title="Audit Logs" icon="fas fa-clock-rotate-left" :button-id="null" :columns="['Date', 'User', 'Event', 'Entity', 'Entity ID', 'Previous Values', 'New Values', 'IP Address', 'User Agent']" :dt-columns="$columns" :ajax-url="route('audit-logs.data')" />
    </div>
</x-app-layout>
