<?php

namespace Modules\Invoice\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends StoreInvoiceRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->can('invoices.update') ?? false)
            && (!$this->boolean('new_customer') || $this->user()->can('customers.create'));
    }

    public function rules(): array
    {
        $rules = parent::rules();
        foreach (['save_mode', 'auto_invoice_number', 'initial_payment_amount', 'initial_payment_method', 'initial_payment_date', 'initial_payment_reference'] as $field) {
            $rules[$field] = ['exclude'];
        }
        $rules['invoice_number'] = ['required', 'string', 'max:100', Rule::unique('invoices', 'invoice_number')->ignore($this->route('invoice')->id)];
        $rules['bill_title'] = ['required', 'string', 'max:150'];
        return $rules;
    }
}
