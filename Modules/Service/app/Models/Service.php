<?php
namespace Modules\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;
    protected $table = 'services';
    protected $fillable = ['service_code', 'name', 'description', 'default_rate', 'default_cost', 'is_active'];
    protected function casts(): array
    {
        return ['default_rate' => 'decimal:2', 'default_cost' => 'decimal:2', 'is_active' => 'boolean'];
    }
}
