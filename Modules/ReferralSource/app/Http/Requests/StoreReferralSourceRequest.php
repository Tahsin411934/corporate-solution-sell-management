<?php
namespace Modules\ReferralSource\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReferralSourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('referral-sources.create') ?? false;
    }
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'phone' => ['nullable', 'string', 'max:30'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'commission_type' => ['required', 'in:fixed,percentage,none'],
            'commission_value' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('commission_type') === 'percentage' && (float) $this->input('commission_value') > 100) {
                $validator->errors()->add('commission_value', 'Percentage cannot exceed 100.');
            }
        });
    }
}
