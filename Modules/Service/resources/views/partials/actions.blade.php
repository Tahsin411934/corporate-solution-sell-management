<div class="flex justify-center gap-2">
@can('services.update')
<button type="button" class="access-edit text-primary p-2" data-entity="service" data-record="{{ json_encode($entity->only(['id', 'service_code', 'name', 'description', 'default_rate', 'default_cost', 'is_active'])) }}" aria-label="Edit"><i class="fas fa-pen"></i></button>
@endcan
@can('services.delete')
<button type="button" class="access-delete text-red-600 p-2" data-entity="service" data-url="{{ route('services.destroy', $entity) }}" aria-label="Delete"><i class="fas fa-trash"></i></button>
@endcan
</div>
