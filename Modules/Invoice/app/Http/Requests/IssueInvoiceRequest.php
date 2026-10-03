<?php
namespace Modules\Invoice\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class IssueInvoiceRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('invoices.issue') ?? false; }
    public function rules(): array { return []; }
}
