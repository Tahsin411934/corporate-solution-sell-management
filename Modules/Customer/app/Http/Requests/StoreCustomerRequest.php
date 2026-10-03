<?php

namespace Modules\Customer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Customer\Models\Customer;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Customer::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'company_name' => ['nullable', 'string', 'max:200'],
            'address' => ['nullable', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:190'],
            'tin_number' => ['nullable', 'string', 'max:80'],
            'bin_number' => ['nullable', 'string', 'max:80'],
            'nid_number' => ['nullable', 'string', 'max:80'],
            'opening_balance' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
