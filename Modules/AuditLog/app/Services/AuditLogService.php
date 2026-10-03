<?php
namespace Modules\AuditLog\Services;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Modules\AuditLog\Models\AuditLog;
class AuditLogService
{
    public function record(Model $model, string $event, ?array $old, ?array $new): AuditLog
    {
        $exclude = array_merge($model->getHidden(), ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'api_token', 'token', 'updated_at']);
        $old = $old === null ? null : Arr::except($old, $exclude);
        $new = $new === null ? null : Arr::except($new, $exclude);
        $request = app()->runningInConsole() ? null : request();
        return AuditLog::create([
            'user_id' => auth()->id(), 'event' => $event,
            'auditable_type' => $model->getMorphClass(), 'auditable_id' => $model->getKey(),
            'old_values' => $old, 'new_values' => $new,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? Str::limit($request->userAgent() ?? '', 500, '') : null,
            'created_at' => now(),
        ]);
    }
}
