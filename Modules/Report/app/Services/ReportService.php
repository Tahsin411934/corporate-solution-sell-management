<?php
namespace Modules\Report\Services;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
class ReportService
{
    public const TYPES = ['customers' => 'Customer receivables', 'invoices' => 'Invoice dues', 'payments' => 'Payments', 'expenses' => 'Expenses', 'profit' => 'Profit'];

    public function columns(string $type): array
    {
        return match ($type) {
            'customers' => ['customer_code' => 'Code', 'name' => 'Customer', 'opening_balance' => 'Opening receivable', 'total_invoiced' => 'Invoice total', 'total_received' => 'Received', 'outstanding_balance' => 'Due'],
            'invoices' => ['invoice_number' => 'Invoice', 'customer_name' => 'Customer', 'invoice_date' => 'Date', 'currency_code' => 'Currency', 'total_amount' => 'Invoice total', 'received_amount' => 'Received', 'balance_amount' => 'Due', 'status' => 'Status'],
            'payments' => ['payment_date' => 'Date', 'receipt_number' => 'Receipt', 'invoice_number' => 'Invoice', 'customer_name' => 'Customer', 'currency_code' => 'Currency', 'payment_method' => 'Method', 'amount' => 'Amount', 'transaction_number' => 'Transaction'],
            'expenses' => ['expense_date' => 'Date', 'category' => 'Category', 'description' => 'Description', 'amount' => 'Amount', 'payment_method' => 'Method', 'invoice_number' => 'Invoice'],
            'profit' => ['invoice_number' => 'Invoice', 'customer_name' => 'Customer', 'invoice_date' => 'Date', 'currency_code' => 'Currency', 'subtotal' => 'Subtotal', 'discount_amount' => 'Discount', 'total_cost' => 'Service cost', 'total_profit' => 'Gross profit (excl. tax)', 'status' => 'Status'],
        };
    }

    private function dates(Builder $query, string $column, array $filters): Builder
    {
        return $query->when($filters['from'] ?? null, fn ($q, $date) => $q->where($column, '>=', $date))
            ->when($filters['to'] ?? null, fn ($q, $date) => $q->where($column, '<=', $date));
    }

    public function query(string $type, array $filters): Builder
    {
        if ($type === 'customers') return DB::table('v_customer_balances');
        $query = match ($type) {
            'invoices' => DB::table('v_invoice_balances as v')->join('invoices as i', 'i.id', '=', 'v.id')
                ->leftJoin('customers as c', 'c.id', '=', 'v.customer_id')->select('v.*', 'c.name as customer_name', 'i.currency_code'),
            'payments' => DB::table('payments as p')->whereNull('p.deleted_at')
                ->join('invoices as i', 'i.id', '=', 'p.invoice_id')->leftJoin('customers as c', 'c.id', '=', 'p.customer_id')
                ->select('p.payment_date', 'p.receipt_number', 'p.amount', 'p.payment_method', 'p.transaction_number', 'i.invoice_number', 'i.currency_code', 'c.name as customer_name'),
            'expenses' => DB::table('expenses as e')->whereNull('e.deleted_at')->leftJoin('invoices as i', 'i.id', '=', 'e.invoice_id')
                ->select('e.expense_date', 'e.category', 'e.description', 'e.amount', 'e.payment_method', 'i.invoice_number'),
            'profit' => DB::table('invoices as i')->whereNull('i.deleted_at')->whereNotIn('i.status', ['draft', 'cancelled'])
                ->leftJoin('customers as c', 'c.id', '=', 'i.customer_id')
                ->select('i.invoice_number', 'i.invoice_date', 'i.currency_code', 'i.subtotal', 'i.discount_amount', 'i.total_cost', 'i.total_profit', 'i.status', 'c.name as customer_name'),
        };
        $date = match ($type) { 'payments' => 'p.payment_date', 'expenses' => 'e.expense_date', default => 'i.invoice_date' };
        // Wrap joined queries so DataTables can safely search/sort projected aliases.
        return DB::query()->fromSub($this->dates($query, $date, $filters), 'report_rows');
    }

    public function data(string $type, array $filters): JsonResponse
    {
        return DataTables::of($this->query($type, $filters))->toJson();
    }

    public function summary(string $type, array $filters): array
    {
        if ($type === 'customers') return []; // Customer view has no currency dimension; do not sum mixed balances.
        if ($type === 'expenses') return [['Currency' => 'Unspecified (schema has no currency)', 'Expenses' => $this->query($type, $filters)->sum('amount')]];
        $fields = match ($type) {
            'invoices' => ['total_amount' => 'Invoice total', 'received_amount' => 'Received', 'balance_amount' => 'Due'],
            'payments' => ['amount' => 'Received'],
            'profit' => ['subtotal' => 'Subtotal', 'discount_amount' => 'Discount', 'total_cost' => 'Service cost', 'total_profit' => 'Gross profit (excluding tax)'],
        };
        $query = $this->query($type, $filters)->select('currency_code')->groupBy('currency_code');
        foreach ($fields as $field => $label) $query->selectRaw('COALESCE(SUM('.$field.'), 0) as '.$field);
        return $query->get()->map(function ($row) use ($fields) {
            $result = ['Currency' => $row->currency_code];
            foreach ($fields as $field => $label) $result[$label] = $row->$field;
            return $result;
        })->all();
    }
}
