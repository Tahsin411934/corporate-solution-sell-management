<?php
namespace Modules\Report\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Modules\Report\Http\Requests\ReportRequest;
use Modules\Report\Services\ReportService;
class ReportController extends Controller
{
    public function index(ReportRequest $request, ReportService $service): View
    {
        $filters = $request->validated();
        $type = $filters['report'] ?? 'customers';
        return view('report::index', ['types' => ReportService::TYPES, 'type' => $type, 'filters' => $filters,
            'columns' => $service->columns($type), 'summary' => $service->summary($type, $filters)]);
    }
    public function data(ReportRequest $request, ReportService $service): JsonResponse
    {
        $filters = $request->validated();
        return $service->data($filters['report'] ?? 'customers', $filters);
    }
}
