<?php
namespace Modules\AuditLog\Services;
use Modules\AuditLog\Models\AuditLog;
use Yajra\DataTables\Facades\DataTables;
class AuditLogDataTableService
{
    public function response()
    {
        return DataTables::of(AuditLog::with('user'))
            ->addColumn('user_name', fn (AuditLog $log) => $log->user?->name ?? 'System / deleted user')
            ->editColumn('created_at', fn (AuditLog $log) => $log->created_at?->format('Y-m-d H:i:s'))
            ->editColumn('old_values', fn (AuditLog $log) => $log->old_values ? json_encode($log->old_values, JSON_UNESCAPED_UNICODE) : '—')
            ->editColumn('new_values', fn (AuditLog $log) => $log->new_values ? json_encode($log->new_values, JSON_UNESCAPED_UNICODE) : '—')
            ->make(true);
    }
}
