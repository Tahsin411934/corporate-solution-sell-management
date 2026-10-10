@php
    $payload = $entity->only(['id', 'category', 'description', 'amount', 'payment_method', 'invoice_id', 'referral_source', 'notes']);
    $payload['expense_date'] = $entity->expense_date->format('Y-m-d');
@endphp
<div class="flex justify-center gap-2">
@can('expenses.update')<button type="button" class="access-edit text-primary p-2" data-entity="expense" data-record="{{ json_encode($payload) }}" aria-label="Edit expense"><i class="fas fa-pen"></i></button>@endcan
@can('expenses.delete')<button type="button" class="access-delete text-red-600 p-2" data-entity="expense" data-url="{{ route('expenses.destroy', $entity) }}" aria-label="Delete expense"><i class="fas fa-trash"></i></button>@endcan
</div>
