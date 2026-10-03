<?php
namespace Modules\AuditLog\Observers;
use Illuminate\Database\Eloquent\Model;
use Modules\AuditLog\Services\AuditLogService;
class AuditObserver
{
    public function created(Model $model): void
    {
        app(AuditLogService::class)->record($model, 'created', null, $model->getAttributes());
    }
    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        $changes = array_diff_key($changes, array_flip(array_merge($model->getHidden(), ['updated_at'])));
        if (!$changes) return;
        $old = array_intersect_key($model->getRawOriginal(), $changes);
        $event = array_key_exists('deleted_at', $changes) && $changes['deleted_at'] === null ? 'restored' : 'updated';
        app(AuditLogService::class)->record($model, $event, $old, $changes);
    }
    public function deleted(Model $model): void
    {
        app(AuditLogService::class)->record($model, method_exists($model, 'isForceDeleting') && $model->isForceDeleting() ? 'force_deleted' : 'deleted', $model->getRawOriginal(), null);
    }
}
