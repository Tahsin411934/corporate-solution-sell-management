<?php
namespace Modules\Payment\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Payment extends Model
{
    use SoftDeletes;
    protected $fillable = ['payment_date', 'receipt_number', 'amount', 'payment_method', 'transaction_number', 'account_name', 'notes'];
    protected function casts(): array { return ['amount' => 'decimal:2', 'payment_date' => 'date']; }
    public function invoice() { return $this->belongsTo(\Modules\Invoice\Models\Invoice::class); }
    public function customer() { return $this->belongsTo(\Modules\Customer\Models\Customer::class); }
    public function receiver() { return $this->belongsTo(\App\Models\User::class, 'received_by'); }
}
