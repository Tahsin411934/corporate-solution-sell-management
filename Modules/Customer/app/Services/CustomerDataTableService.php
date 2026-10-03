<?php

namespace Modules\Customer\Services;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Customer\Models\Customer;
use Yajra\DataTables\Facades\DataTables;

class CustomerDataTableService
{
    public function response(Request $request): JsonResponse
    {
        $query = Customer::query();
        $search = $request->input('search.value');

        if ($search) {
            $query->search($search);
        }

        return DataTables::of($query)
            ->editColumn('phone', function (Customer $customer) {
                return $customer->phone ?: '—';
            })
            ->editColumn('email', function (Customer $customer) {
                return $customer->email ?: '—';
            })
            ->editColumn('opening_balance', function (Customer $customer) {
                return number_format((float) $customer->opening_balance, 2);
            })
            ->addColumn('actions', function (Customer $customer) {
                return view('customer::partials.actions', [
                    'customer' => $customer,
                ])->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
}
