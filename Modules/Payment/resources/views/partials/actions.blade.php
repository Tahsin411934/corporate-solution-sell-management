@php
    $payload = $entity->only(['id', 'invoice_id', 'receipt_number', 'amount', 'payment_method', 'transaction_number', 'account_name', 'notes']);
    $payload['payment_date'] = $entity->payment_date->format('Y-m-d');
@endphp
<div class="flex justify-center gap-2">
@can('payments.update')<button type="button" class="access-edit text-primary p-2" data-entity="payment" data-record="{{ json_encode($payload) }}" aria-label="Edit payment"><i class="fas fa-pen"></i></button>@endcan
@can('payments.delete')<button type="button" class="access-delete text-red-600 p-2" data-entity="payment" data-url="{{ route('payments.destroy', $entity) }}" aria-label="Delete payment"><i class="fas fa-trash"></i></button>@endcan
</div>
