<?php

namespace Modules\Invoice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->can('invoices.create') ?? false)
            && (!$this->boolean('new_customer') || $this->user()->can('customers.create'));
    }

    public function rules(): array
    {
        $money = ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:999999999999.99'];
        return [
            'new_customer' => ['sometimes', 'boolean'],
            'customer_id' => ['exclude_if:new_customer,1', 'required', Rule::exists('customers', 'id')->whereNull('deleted_at')],
            'new_customer_data.name' => ['exclude_unless:new_customer,1', 'required', 'string', 'max:200'],
            'new_customer_data.bin_number' => ['exclude_unless:new_customer,1', 'nullable', 'string', 'max:80'],
            'new_customer_data.tin_number' => ['exclude_unless:new_customer,1', 'nullable', 'string', 'max:80'],
            'new_customer_data.address' => ['exclude_unless:new_customer,1', 'nullable', 'string', 'max:2000'],
            'referral_source' => ['nullable', 'string', 'max:200'],
            'auto_invoice_number' => ['sometimes', 'boolean'],
            'invoice_number' => ['exclude_if:auto_invoice_number,1', 'required', 'string', 'max:100', Rule::unique('invoices', 'invoice_number')],
            'invoice_date' => ['required', 'date_format:Y-m-d'],
            'due_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:invoice_date'],
            'currency_code' => ['required', 'string', 'regex:/^[A-Z]{3}$/'],
            'discount_type' => ['required', Rule::in(['fixed', 'percentage'])],
            'discount_value' => [...$money, $this->input('discount_type') === 'percentage' ? 'max:100' : 'max:999999999999.99'],
            'tax_rate' => ['required', 'numeric', 'decimal:0,4', 'min:0', 'max:999.9999'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.service_id' => ['nullable', Rule::exists('services', 'id')->whereNull('deleted_at')->where('is_active', true)],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'numeric', 'decimal:0,3', 'min:0.001', 'max:999999999.999'],
            'items.*.unit' => ['required', 'string', 'max:30'],
            'items.*.rate' => $money,
            'items.*.unit_cost' => $money,
        ];
    }
}
