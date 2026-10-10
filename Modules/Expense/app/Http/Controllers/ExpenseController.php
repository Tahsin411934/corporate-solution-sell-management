<?php
namespace Modules\Expense\Http\Controllers;
use Illuminate\Routing\Controller;
use Modules\Expense\Models\Expense;
use Modules\Invoice\Models\Invoice;
use Modules\Expense\Services\ExpenseService;
use Modules\Expense\Services\ExpenseDataTableService;
use Modules\Expense\Http\Requests\StoreExpenseRequest;
use Modules\Expense\Http\Requests\UpdateExpenseRequest;
class ExpenseController extends Controller
{
    public function __construct(private readonly ExpenseService $service, private readonly ExpenseDataTableService $dataTable) {}
    public function index() { return view('expense::index', ['invoices' => Invoice::orderByDesc('id')->get(['id', 'invoice_number'])]); }
    public function data() { return $this->dataTable->response(); }
    public function store(StoreExpenseRequest $request) { $this->service->create($request->validated()); return response()->json(['status' => 'success', 'message' => 'Expense created.']); }
    public function update(UpdateExpenseRequest $request, Expense $entity) { $this->service->update($entity, $request->validated()); return response()->json(['status' => 'success', 'message' => 'Expense updated.']); }
    public function destroy(Expense $entity) { $this->service->delete($entity); return response()->json(['status' => 'success', 'message' => 'Expense deleted.']); }
}
