<?php

namespace Modules\CompanySettings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanySettingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:200'],
            'legal_name' => ['nullable', 'string', 'max:200'],
            'address' => ['nullable', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:190'],
            'website' => ['nullable', 'url', 'max:255'],
            'tin_number' => ['nullable', 'string', 'max:80'],
            'bin_number' => ['nullable', 'string', 'max:80'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'invoice_prefix' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9_-]+$/'],
            'invoice_next_number' => ['required', 'integer', 'min:1'],
            'currency_code' => ['required', 'string', 'size:3', 'alpha', 'uppercase'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'bank_details' => ['nullable', 'array'],
            'invoice_footer' => ['nullable', 'string', 'max:5000'],
            'authorized_person' => ['nullable', 'string', 'max:150'],
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
