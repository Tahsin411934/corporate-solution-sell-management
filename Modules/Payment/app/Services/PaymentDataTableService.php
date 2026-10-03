<?php
namespace Modules\Payment\Services;
use Illuminate\Http\JsonResponse;
use Modules\Payment\Models\Payment;
use Yajra\DataTables\Facades\DataTables;
class PaymentDataTableService
{
    public function response(): JsonResponse
    {
        return DataTables::of(Payment::with(['invoice', 'customer']))
            ->addColumn('invoice_number', fn (Payment $entity) => $entity->invoice?->invoice_number ?? '—')
            ->addColumn('customer_name', fn (Payment $entity) => $entity->customer?->name ?? '—')
            ->editColumn('payment_date', fn (Payment $entity) => $entity->payment_date->format('Y-m-d'))
            ->addColumn('actions', fn (Payment $entity) => view('payment::partials.actions', compact('entity'))->render())
            ->rawColumns(['actions'])->make(true);
    }
}
