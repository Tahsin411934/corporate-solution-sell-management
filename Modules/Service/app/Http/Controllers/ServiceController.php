<?php
namespace Modules\Service\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Service\Models\Service;
use Modules\Service\Http\Requests\StoreServiceRequest;
use Modules\Service\Http\Requests\UpdateServiceRequest;
use Modules\Service\Services\ServiceService;
use Modules\Service\Services\ServiceDataTableService;

class ServiceController extends Controller
{
    public function __construct(private readonly ServiceService $service, private readonly ServiceDataTableService $dataTable) {}
    public function index() { return view('service::index'); }
    public function data(): JsonResponse { return $this->dataTable->response(); }
    public function store(StoreServiceRequest $request): JsonResponse
    {
        $this->service->create($request->validated());
        return response()->json(['status' => 'success', 'message' => 'Created successfully.']);
    }
    public function update(UpdateServiceRequest $request, Service $entity): JsonResponse
    {
        $this->service->update($entity, $request->validated());
        return response()->json(['status' => 'success', 'message' => 'Updated successfully.']);
    }
    public function destroy(Service $entity): JsonResponse
    {
        $this->service->delete($entity);
        return response()->json(['status' => 'success', 'message' => 'Deleted successfully.']);
    }
}
