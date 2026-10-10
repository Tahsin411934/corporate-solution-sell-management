<?php

namespace Modules\Invoice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->can('invoices.create') ?? false)
            && (!$this->boolean('new_customer') || $this->user()->can('customers.create'))
            && ($this->input('save_mode', 'draft') !== 'issue' || $this->user()->can('invoices.issue'))
            && ((float) $this->input('initial_payment_amount', 0) <= 0 || ($this->user()->can('payments.create') && $this->user()->can('invoices.issue')));
    }

    public function attributes(): array
    {
        return [
            'customer_id' => 'customer', 'new_customer_data.name' => 'customer name',
            'new_customer_data.bin_number' => 'BIN', 'new_customer_data.tin_number' => 'TIN',
            'new_customer_data.address' => 'customer address', 'invoice_number' => 'invoice number',
            'invoice_date' => 'invoice date', 'due_date' => 'due date', 'currency_code' => 'currency code',
            'initial_payment_amount' => 'received now', 'initial_payment_method' => 'payment method',
            'initial_payment_date' => 'payment date', 'initial_payment_reference' => 'transaction reference',
            'items.*.description' => 'item :position description', 'items.*.quantity' => 'item :position quantity',
            'items.*.rate' => 'item :position rate', 'items.*.unit' => 'item :position unit',
            'items.*.unit_cost' => 'item :position unit cost', 'items.*.service_id' => 'item :position service',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Please select a customer or choose New customer.',
            'customer_id.exists' => 'The selected customer is no longer available. Please select another customer.',
            'new_customer_data.name.required' => 'Please enter the customer name.',
            'invoice_number.unique' => 'This invoice number is already in use.',
            'items.required' => 'Please add at least one invoice item.',
            'items.min' => 'Please add at least one invoice item.',
            'initial_payment_method.required' => 'Please select a payment method for Received now.',
            'initial_payment_date.required' => 'Please enter the payment date for Received now.',
        ];
    }

    public function rules(): array
    {
        $money = ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:999999999999.99'];
        return [
            'save_mode' => ['sometimes', Rule::in(['draft', 'issue'])],
            'initial_payment_amount' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:999999999999.99',
                function ($attribute, $value, $fail) {
                    if ($this->input('save_mode', 'draft') === 'draft' && (float) $value > 0) {
                        $fail('Use Save invoice to record a payment. Draft invoices cannot receive payments.');
                    }
                }],
            'initial_payment_method' => [Rule::requiredIf(fn () => (float) $this->input('initial_payment_amount', 0) > 0), 'nullable', Rule::in(['cash', 'bank', 'bkash', 'nagad', 'rocket', 'cheque', 'card', 'other'])],
            'initial_payment_date' => [Rule::requiredIf(fn () => (float) $this->input('initial_payment_amount', 0) > 0), 'nullable', 'date_format:Y-m-d'],
            'initial_payment_reference' => ['nullable', 'string', 'max:150'],
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
