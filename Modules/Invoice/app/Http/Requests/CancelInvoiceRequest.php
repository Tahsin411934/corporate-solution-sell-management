<?php
namespace Modules\Invoice\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class CancelInvoiceRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('invoices.cancel') ?? false; }
    public function rules(): array { return ['cancellation_reason' => ['required', 'string', 'max:500']]; }
}
