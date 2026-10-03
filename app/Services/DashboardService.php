<?php
namespace App\Services;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Invoice\Models\Invoice;
use Modules\Customer\Models\Customer;
use Modules\Expense\Models\Expense;
class DashboardService
{
    public function overview(User $user): array
    {
        $start = today()->startOfMonth()->toDateString();
        $end = today()->toDateString();
        $result = ['invoiceMetrics' => collect(), 'collections' => collect(), 'recentInvoices' => collect(),
            'overdueInvoices' => collect(), 'customerCount' => null, 'expenses' => null];
        if ($user->can('customers.view')) $result['customerCount'] = Customer::count();
        if ($user->can('expenses.view')) $result['expenses'] = Expense::whereBetween('expense_date', [$start, $end])->sum('amount');
        if ($user->can('invoices.view')) {
            $paid = DB::table('payments')->whereNull('deleted_at')->select('invoice_id')->selectRaw('SUM(amount) AS received')->groupBy('invoice_id');
            $base = Invoice::query()->whereNotIn('invoices.status', ['draft', 'cancelled'])
                ->leftJoinSub($paid, 'receipts', 'receipts.invoice_id', '=', 'invoices.id');
            $result['invoiceMetrics'] = (clone $base)->select('invoices.currency_code')
                ->selectRaw('COUNT(*) AS invoice_count, SUM(invoices.total_amount) AS invoiced, SUM(invoices.total_amount - COALESCE(receipts.received, 0)) AS outstanding')
                ->groupBy('invoices.currency_code')->get();
            $result['recentInvoices'] = Invoice::with('customer:id,name')->latest('id')->limit(6)->get();
            $result['overdueInvoices'] = (clone $base)->where('invoices.due_date', '<', $end)
                ->whereRaw('invoices.total_amount > COALESCE(receipts.received, 0)')->with('customer:id,name')
                ->select('invoices.*')->selectRaw('invoices.total_amount - COALESCE(receipts.received, 0) AS outstanding')
                ->orderBy('invoices.due_date')->limit(5)->get();
        }
        if ($user->can('payments.view')) {
            $result['collections'] = DB::table('payments as p')->whereNull('p.deleted_at')
                ->join('invoices as i', 'i.id', '=', 'p.invoice_id')->whereBetween('p.payment_date', [$start, $end])
                ->select('i.currency_code')->selectRaw('SUM(p.amount) AS amount')->groupBy('i.currency_code')->get();
        }
        return $result;
    }
}
