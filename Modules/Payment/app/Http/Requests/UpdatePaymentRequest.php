<?php
namespace Modules\Payment\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('payments.update') ?? false; }
    public function rules(): array
    {
        return [
            'invoice_id' => ['required', 'integer', Rule::exists('invoices', 'id')->whereNull('deleted_at')],
            'payment_date' => ['required', 'date_format:Y-m-d'],
            'receipt_number' => ['nullable', 'string', 'max:100', Rule::unique('payments', 'receipt_number')->ignore($this->route('entity')?->id)],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:999999999999.99'],
            'payment_method' => ['required', 'in:cash,bank,bkash,nagad,rocket,cheque,card,other'],
            'transaction_number' => ['nullable', 'string', 'max:150'],
            'account_name' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
