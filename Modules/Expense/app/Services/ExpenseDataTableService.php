<?php
namespace Modules\Expense\Services;
use Illuminate\Http\JsonResponse;
use Modules\Expense\Models\Expense;
use Yajra\DataTables\Facades\DataTables;
class ExpenseDataTableService
{
    public function response(): JsonResponse
    {
        return DataTables::of(Expense::with(['invoice', 'referralSource']))
            ->editColumn('expense_date', fn (Expense $entity) => $entity->expense_date->format('Y-m-d'))
            ->addColumn('invoice_number', fn (Expense $entity) => $entity->invoice?->invoice_number ?? '—')
            ->addColumn('referral_name', fn (Expense $entity) => $entity->referralSource?->name ?? '—')
            ->addColumn('actions', fn (Expense $entity) => view('expense::partials.actions', compact('entity'))->render())
            ->rawColumns(['actions'])->make(true);
    }
}
