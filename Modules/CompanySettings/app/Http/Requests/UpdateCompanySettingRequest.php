<?php

namespace Modules\CompanySettings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanySettingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'company_name' => ['sometimes', 'required', 'string', 'max:200'],
            'legal_name' => ['sometimes', 'nullable', 'string', 'max:200'],
            'address' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'email' => ['sometimes', 'nullable', 'email', 'max:190'],
            'website' => ['sometimes', 'nullable', 'url', 'max:255'],
            'tin_number' => ['sometimes', 'nullable', 'string', 'max:80'],
            'bin_number' => ['sometimes', 'nullable', 'string', 'max:80'],
            'logo' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'invoice_prefix' => ['sometimes', 'required', 'string', 'max:30', 'regex:/^[A-Za-z0-9_-]+$/'],
            'invoice_next_number' => ['sometimes', 'required', 'integer', 'min:1'],
            'currency_code' => ['sometimes', 'required', 'string', 'size:3', 'alpha', 'uppercase'],
            'currency_symbol' => ['sometimes', 'required', 'string', 'max:10'],
            'bank_details' => ['sometimes', 'nullable', 'array'],
            'invoice_footer' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'authorized_person' => ['sometimes', 'nullable', 'string', 'max:150'],
            'authorized_person_qualifications' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'authorized_person_designation' => ['sometimes', 'nullable', 'string', 'max:150'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
