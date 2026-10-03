<?php

namespace Modules\Customer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('customer')) ?? false;
    }

    public function rules(): array
    {
        $customer = $this->route('customer');
        $customerId = is_object($customer) ? $customer->getKey() : $customer;

        return [
            'customer_code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('customers', 'customer_code')->ignore($customerId),
            ],
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
