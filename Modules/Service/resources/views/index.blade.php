<x-app-layout>
    <div class="p-4 sm:p-6">
        @php
            $columns = [['data' => 'service_code'], ['data' => 'name'], ['data' => 'default_rate'], ['data' => 'default_cost'], ['data' => 'is_active'], ['data' => 'actions', 'orderable' => false, 'searchable' => false]];
        @endphp
        <x-entity-crud id="service" title="Service" create-permission="services" :columns="['service code', 'name', 'default rate', 'default cost', 'is active', 'Actions']" :dt-columns="$columns" :ajax-url="route('services.data')" :store-url="route('services.store')">
            <input type="hidden" name="_method" value="POST">
            <x-form-input label="service code" name="service_code" id="service_service_code"   />
            <x-form-input label="name" name="name" id="service_name"  required />
            <x-form-textarea label="description" name="description" id="service_description" />
            <x-form-input label="default rate" name="default_rate" id="service_default_rate" type="number" min="0" step="0.01" value="0" required />
            <x-form-input label="default cost" name="default_cost" id="service_default_cost" type="number" min="0" step="0.01" value="0" required />
            <x-form-select label="Status" name="is_active" id="service_is_active" :searchable="false" :placeholder="null"><option value="1">Active</option><option value="0">Inactive</option></x-form-select>
        </x-entity-crud>
    </div>
    @include('access.scripts', ['entity' => 'service', 'baseUrl' => url('services')])
</x-app-layout>
