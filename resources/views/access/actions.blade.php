@php
    $group = $entity === 'user' ? 'users' : 'roles';
    $protected = $entity === 'user' ? $record->hasRole('admin') && !auth()->user()->hasRole('admin') : $record->name === 'admin' && !auth()->user()->hasRole('admin');
    $payload = $entity === 'user'
        ? ['id' => $record->id, 'name' => $record->name, 'email' => $record->email, 'roles' => $record->getRoleNames()->all()]
        : ['id' => $record->id, 'name' => $record->name, 'permissions' => $record->permissions->pluck('name')->all()];
@endphp
<div class="flex justify-center gap-2">
    @if(!$protected)
        @can($group.'.update')
            <button type="button" class="access-edit text-primary p-2 rounded-lg hover:bg-primary-soft" data-entity="{{ $entity }}" data-record="{{ json_encode($payload) }}" aria-label="Edit {{ $entity }}"><i class="fas fa-pen"></i> {{ $entity === 'role' ? 'Assign permissions / Edit' : 'Edit' }}</button>
        @endcan
    @endif
    @if($entity === 'user' ? $record->id !== auth()->id() && !$record->hasRole('admin') : $record->name !== 'admin' && $record->users_count === 0)
        @can($group.'.delete')
            <button type="button" class="access-delete text-red-600 p-2 rounded-lg hover:bg-red-50" data-url="{{ route($group.'.destroy', $record) }}" data-entity="{{ $entity }}" aria-label="Delete {{ $entity }}"><i class="fas fa-trash"></i></button>
        @endcan
    @endif
</div>
