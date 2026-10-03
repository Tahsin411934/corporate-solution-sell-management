<?php
namespace Modules\AuditLog\Models;
use Illuminate\Database\Eloquent\Model;
class AuditLog extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id', 'event', 'auditable_type', 'auditable_id', 'old_values', 'new_values', 'ip_address', 'user_agent', 'created_at'];
    protected function casts(): array { return ['old_values' => 'array', 'new_values' => 'array', 'created_at' => 'datetime']; }
    public function user() { return $this->belongsTo(\App\Models\User::class); }
    public function auditable() { return $this->morphTo()->withTrashed(); }
    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Audit logs cannot be edited.'));
        static::deleting(fn () => throw new \LogicException('Audit logs cannot be deleted.'));
    }
}
