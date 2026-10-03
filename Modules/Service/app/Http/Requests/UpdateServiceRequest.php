<?php
namespace Modules\Service\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('services.update') ?? false;
    }
    public function rules(): array
    {
        return [
            'service_code' => ['nullable', 'string', 'max:50', Rule::unique('services', 'service_code')->ignore($this->route('entity')?->id)],
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'default_rate' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'default_cost' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
