<?php
namespace Modules\Invoice\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Invoice extends Model
{
    use SoftDeletes;
    protected $fillable = ['company_setting_id', 'customer_id', 'referral_source_id', 'created_by', 'invoice_number', 'invoice_date', 'due_date', 'currency_code', 'discount_type', 'discount_value', 'tax_rate', 'payment_terms', 'notes'];
    protected function casts(): array
    {
        return ['invoice_date' => 'date', 'due_date' => 'date', 'printed_at' => 'datetime', 'cancelled_at' => 'datetime', 'subtotal' => 'decimal:2', 'discount_value' => 'decimal:2', 'discount_amount' => 'decimal:2', 'tax_rate' => 'decimal:4', 'tax_amount' => 'decimal:2', 'total_amount' => 'decimal:2', 'total_cost' => 'decimal:2', 'total_profit' => 'decimal:2'];
    }
    public function items() { return $this->hasMany(InvoiceItem::class)->orderBy('sort_order')->orderBy('id'); }
    public function customer() { return $this->belongsTo(\Modules\Customer\Models\Customer::class); }
    public function payments() { return $this->hasMany(\Modules\Payment\Models\Payment::class); }
    public function companySetting() { return $this->belongsTo(\Modules\CompanySettings\Models\CompanySetting::class); }
    public function referralSource() { return $this->belongsTo(\Modules\ReferralSource\Models\ReferralSource::class); }
    public function creator() { return $this->belongsTo(\App\Models\User::class, 'created_by'); }
    public function cancelledBy() { return $this->belongsTo(\App\Models\User::class, 'cancelled_by'); }
}
