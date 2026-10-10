<?php
namespace Modules\Invoice\Services;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\Invoice\Models\Invoice;
use Yajra\DataTables\Facades\DataTables;
class InvoiceDataTableService
{
    public function response(): JsonResponse
    {
        $payments = DB::table('payments')->whereNull('deleted_at')->select('invoice_id')->selectRaw('SUM(amount) AS received_amount')->groupBy('invoice_id');
        $query = Invoice::query()->leftJoin('customers', 'customers.id', '=', 'invoices.customer_id')
            ->leftJoinSub($payments, 'receipts', 'receipts.invoice_id', '=', 'invoices.id')
            ->select('invoices.*', 'customers.name as customer_name')
            ->selectRaw('COALESCE(receipts.received_amount, 0) as received_amount')
            ->selectRaw('invoices.total_amount - COALESCE(receipts.received_amount, 0) as balance_amount');
        return DataTables::eloquent($query)
            ->editColumn('invoice_date', fn (Invoice $invoice) => $invoice->invoice_date->format('Y-m-d'))
            ->editColumn('status', fn (Invoice $invoice) => $invoice->status === 'issued' ? 'Unpaid' : ucfirst($invoice->status))
            ->filterColumn('balance_amount', function ($query, $keyword) {
                $query->whereRaw('CAST(invoices.total_amount - COALESCE(receipts.received_amount, 0) AS CHAR) LIKE ?', ['%'.$keyword.'%']);
            })
            ->orderColumn('balance_amount', 'invoices.total_amount - COALESCE(receipts.received_amount, 0) $1')
            ->orderColumn('received_amount', 'COALESCE(receipts.received_amount, 0) $1')
            ->addColumn('actions', fn (Invoice $invoice) => view('invoice::partials.actions', compact('invoice'))->render())
            ->rawColumns(['actions'])->toJson();
    }
}
