@php
    $protected = in_array($record->name, ['customers.view', 'customers.create', 'customers.update', 'customers.delete', 'customers.restore', 'users.view', 'users.create', 'users.update', 'users.delete', 'roles.view', 'roles.create', 'roles.update', 'roles.delete']);
    $payload = ['id' => $record->id, 'name' => $record->name];
@endphp
@if($protected)
    <span class="text-xs text-gray-500">Application permission</span>
@else
    <div class="flex justify-center gap-2">
        <button type="button" class="access-edit text-primary p-2" data-entity="permission" data-record="{{ json_encode($payload) }}" aria-label="Edit permission"><i class="fas fa-pen"></i></button>
        <button type="button" class="access-delete text-red-600 p-2" data-entity="permission" data-url="{{ route('permissions.destroy', $record) }}" aria-label="Delete permission"><i class="fas fa-trash"></i></button>
    </div>
@endif
