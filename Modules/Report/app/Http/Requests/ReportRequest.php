<?php
namespace Modules\Report\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ReportRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('reports.view') ?? false; }
    public function rules(): array
    {
        return [
            'report' => ['sometimes', Rule::in(['customers', 'invoices', 'payments', 'expenses', 'profit'])],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', ...($this->filled('from') ? ['after_or_equal:from'] : [])],
        ];
    }
}
