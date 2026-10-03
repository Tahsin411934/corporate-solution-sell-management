<x-app-layout>
    <div class="p-4 sm:p-6">
        @php
            $columns = [
                ['data' => 'name'], ['data' => 'guard_name'],
                ['data' => 'actions', 'orderable' => false, 'searchable' => false],
            ];
        @endphp
        <x-entity-crud id="permission" title="Permission" icon="fas fa-lock" create-permission="roles" :columns="['Permission', 'Guard', 'Actions']" :dt-columns="$columns" :ajax-url="route('permissions.data')" :store-url="route('permissions.store')">
            <input type="hidden" name="_method" value="POST">
            <x-form-input label="Permission name" name="name" id="permission_name" placeholder="customers.export" required />
            <p class="text-sm text-gray-500">Use module.action. Assign permissions from the Role Add/Edit drawer.</p>
        </x-entity-crud>
    </div>
    @include('access.scripts', ['entity' => 'permission', 'baseUrl' => url('permissions')])
</x-app-layout>
