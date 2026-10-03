<div class="flex justify-center gap-2">
@can('referral-sources.update')
<button type="button" class="access-edit text-primary p-2" data-entity="referralsource" data-record="{{ json_encode($entity->only(['id', 'name', 'phone', 'reference_number', 'commission_type', 'commission_value', 'notes'])) }}" aria-label="Edit"><i class="fas fa-pen"></i></button>
@endcan
@can('referral-sources.delete')
<button type="button" class="access-delete text-red-600 p-2" data-entity="referralsource" data-url="{{ route('referral-sources.destroy', $entity) }}" aria-label="Delete"><i class="fas fa-trash"></i></button>
@endcan
</div>
