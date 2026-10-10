<?php
namespace Modules\Expense\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Expense extends Model
{
    use SoftDeletes;
    protected $fillable = ['expense_date', 'category', 'description', 'amount', 'payment_method', 'invoice_id', 'referral_source', 'notes'];
    protected function casts(): array { return ['amount' => 'decimal:2', 'expense_date' => 'date']; }
    public function invoice() { return $this->belongsTo(\Modules\Invoice\Models\Invoice::class); }
    public function creator() { return $this->belongsTo(\App\Models\User::class, 'created_by'); }
}
