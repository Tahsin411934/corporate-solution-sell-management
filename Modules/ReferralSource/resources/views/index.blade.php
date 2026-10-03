<x-app-layout>
    <div class="p-4 sm:p-6">
        @php
            $columns = [['data' => 'name'], ['data' => 'phone'], ['data' => 'reference_number'], ['data' => 'commission_type'], ['data' => 'commission_value'], ['data' => 'actions', 'orderable' => false, 'searchable' => false]];
        @endphp
        <x-entity-crud id="referralsource" title="Referral Source" create-permission="referral-sources" :columns="['name', 'phone', 'reference number', 'commission type', 'commission value', 'Actions']" :dt-columns="$columns" :ajax-url="route('referral-sources.data')" :store-url="route('referral-sources.store')">
            <input type="hidden" name="_method" value="POST">
            <x-form-input label="name" name="name" id="referralsource_name"  required />
            <x-form-input label="phone" name="phone" id="referralsource_phone"   />
            <x-form-input label="reference number" name="reference_number" id="referralsource_reference_number"   />
            <x-form-select label="Commission type" name="commission_type" id="referralsource_commission_type" :searchable="false" :placeholder="null"><option value="none">None</option><option value="fixed">Fixed</option><option value="percentage">Percentage</option></x-form-select>
            <x-form-input label="commission value" name="commission_value" id="referralsource_commission_value" type="number" min="0" step="0.01" value="0" required />
            <x-form-textarea label="notes" name="notes" id="referralsource_notes" />
        </x-entity-crud>
    </div>
    @include('access.scripts', ['entity' => 'referralsource', 'baseUrl' => url('referral-sources')])
</x-app-layout>
