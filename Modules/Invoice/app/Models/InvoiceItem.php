<?php
namespace Modules\Invoice\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class InvoiceItem extends Model
{
    use SoftDeletes;
    protected $fillable = ['service_id', 'sort_order', 'description', 'quantity', 'unit', 'rate', 'unit_cost'];
    protected function casts(): array
    {
        return ['quantity' => 'decimal:3', 'rate' => 'decimal:2', 'amount' => 'decimal:2', 'unit_cost' => 'decimal:2', 'total_cost' => 'decimal:2', 'profit' => 'decimal:2', 'sort_order' => 'integer'];
    }
    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function service() { return $this->belongsTo(\Modules\Service\Models\Service::class); }
}
