<?php
namespace Modules\ReferralSource\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Modules\ReferralSource\Models\ReferralSource;
use Modules\ReferralSource\Http\Requests\StoreReferralSourceRequest;
use Modules\ReferralSource\Http\Requests\UpdateReferralSourceRequest;
use Modules\ReferralSource\Services\ReferralSourceService;
use Modules\ReferralSource\Services\ReferralSourceDataTableService;

class ReferralSourceController extends Controller
{
    public function __construct(private readonly ReferralSourceService $service, private readonly ReferralSourceDataTableService $dataTable) {}
    public function index() { return view('referralsource::index'); }
    public function data(): JsonResponse { return $this->dataTable->response(); }
    public function store(StoreReferralSourceRequest $request): JsonResponse
    {
        $this->service->create($request->validated());
        return response()->json(['status' => 'success', 'message' => 'Created successfully.']);
    }
    public function update(UpdateReferralSourceRequest $request, ReferralSource $entity): JsonResponse
    {
        $this->service->update($entity, $request->validated());
        return response()->json(['status' => 'success', 'message' => 'Updated successfully.']);
    }
    public function destroy(ReferralSource $entity): JsonResponse
    {
        $this->service->delete($entity);
        return response()->json(['status' => 'success', 'message' => 'Deleted successfully.']);
    }
}
