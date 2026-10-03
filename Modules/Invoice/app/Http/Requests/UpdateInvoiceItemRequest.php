<?php
namespace Modules\Invoice\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateInvoiceItemRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('invoices.update') ?? false; }
    public function rules(): array
    {
        return [
            'service_id' => ['nullable', 'integer', Rule::exists('services', 'id')->whereNull('deleted_at')],
            'sort_order' => ['sometimes', 'integer', 'min:1', 'max:65535'],
            'description' => ['required', 'string', 'max:500'],
            'quantity' => ['required', 'numeric', 'decimal:0,3', 'min:0.001', 'max:999999999.999'],
            'unit' => ['sometimes', 'required', 'string', 'max:30'],
            'rate' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:999999999999.99'],
            'unit_cost' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:999999999999.99'],
        ];
    }
}
