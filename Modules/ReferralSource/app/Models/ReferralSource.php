<?php
namespace Modules\ReferralSource\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferralSource extends Model
{
    use SoftDeletes;
    protected $table = 'referral_sources';
    protected $fillable = ['name', 'phone', 'reference_number', 'commission_type', 'commission_value', 'notes'];
    protected function casts(): array
    {
        return ['commission_value' => 'decimal:2'];
    }
}
