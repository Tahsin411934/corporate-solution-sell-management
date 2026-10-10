<?php
namespace Modules\Expense\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('expenses.update') ?? false; }
    public function rules(): array
    {
        return [
            'expense_date' => ['required', 'date_format:Y-m-d'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:999999999999.99'],
            'payment_method' => ['required', 'in:cash,bank,bkash,nagad,rocket,card,other'],
            'invoice_id' => ['nullable', 'integer', Rule::exists('invoices', 'id')->whereNull('deleted_at')],
            'referral_source' => ['nullable', 'string', 'max:200'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
