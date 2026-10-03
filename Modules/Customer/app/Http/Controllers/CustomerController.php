<?php

namespace Modules\Customer\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Customer\Http\Requests\StoreCustomerRequest;
use Modules\Customer\Http\Requests\UpdateCustomerRequest;
use Modules\Customer\Models\Customer;
use Modules\Customer\Services\CustomerDataTableService;
use Modules\Customer\Services\CustomerService;

class CustomerController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
    public function __construct(
        private readonly CustomerService $service,
        private readonly CustomerDataTableService $dataTable
    ) {}

    public function index()
    {
        $this->authorize('viewAny', Customer::class);

        return view('customer::index');
    }

    public function dataTable(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Customer::class);

        return $this->dataTable->response($request);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse|JsonResponse
    {
        $customer = $this->service->create($request->validated());
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Customer created successfully.', 'data' => [
                'id' => $customer->id, 'name' => $customer->name, 'customer_code' => $customer->customer_code,
            ]], 201);
        }
        return redirect()->route('customers.index')->with('success', "Customer {$customer->name} created successfully.");
    }

    public function nextCode(): JsonResponse
    {
        $this->authorize('create', Customer::class);
        return response()->json(['customer_code' => $this->service->nextCode()])->header('Cache-Control', 'no-store');
    }

    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);

        return view('customer::show', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->service->update($customer, $request->validated());
        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('delete', $customer);

        $this->service->delete($customer);
        return redirect()->route('customers.index')->with('success', 'Customer moved to trash successfully.');
    }

    public function restore(int $customer): RedirectResponse
    {
        $model = Customer::withTrashed()->findOrFail($customer);
        $this->authorize('restore', $model);
        $this->service->restore($model);
        return redirect()->route('customers.index')->with('success', 'Customer restored successfully.');
    }

    public function apiIndex(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Customer::class);
        return response()->json($this->service->paginate(
            $request->string('search')->toString(),
            $request->integer('per_page', 15)
        ));
    }
}
