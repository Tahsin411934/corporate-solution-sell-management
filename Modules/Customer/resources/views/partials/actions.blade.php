<div class="flex justify-center gap-2">
    <a href="{{ route('customers.show', $customer) }}" class="w-8 h-8 rounded-lg text-slate-500 hover:bg-slate-100 inline-flex items-center justify-center" title="View"><i class="fa-solid fa-eye"></i></a>
    <button type="button" class="edit-customer w-8 h-8 rounded-lg text-primary hover:bg-primary-soft inline-flex items-center justify-center" title="Edit" data-customer='@json($customer)'><i class="fa-solid fa-pen"></i></button>
    <form method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('Move this customer to trash?')">
        @csrf @method('DELETE')
        <button class="w-8 h-8 rounded-lg text-red-500 hover:bg-red-50" title="Delete"><i class="fa-solid fa-trash"></i></button>
    </form>
</div>
