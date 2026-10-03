<?php
namespace Modules\AuditLog\Http\Controllers;
use Illuminate\Routing\Controller;
use Modules\AuditLog\Services\AuditLogDataTableService;
class AuditLogController extends Controller
{
    public function index() { return view('auditlog::index'); }
    public function data(AuditLogDataTableService $service) { return $service->response(); }
}
