<?php
namespace Modules\Payment\Http\Controllers;
use Illuminate\Routing\Controller;
use Modules\Payment\Models\Payment;
use Modules\Invoice\Models\Invoice;
use Modules\Payment\Services\PaymentService;
use Modules\Payment\Services\PaymentDataTableService;
use Modules\Payment\Http\Requests\StorePaymentRequest;
use Modules\Payment\Http\Requests\UpdatePaymentRequest;
class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $service, private readonly PaymentDataTableService $dataTable) {}
    public function index() { return view('payment::index', ['invoices' => Invoice::whereNotIn('status', ['draft', 'cancelled'])->orderByDesc('id')->get(['id', 'invoice_number'])]); }
    public function data() { return $this->dataTable->response(); }
    public function store(StorePaymentRequest $request) { $this->service->create($request->validated()); return response()->json(['status' => 'success']); }
    public function update(UpdatePaymentRequest $request, Payment $entity) { $this->service->update($entity, $request->validated()); return response()->json(['status' => 'success']); }
    public function destroy(Payment $entity) { $this->service->delete($entity); return response()->json(['status' => 'success']); }
}
