<?php
namespace Modules\Expense\Services;
use Illuminate\Support\Facades\DB;
use Modules\Expense\Models\Expense;
class ExpenseService
{
    public function create(array $data, ?int $userId = null): Expense
    {
        return DB::transaction(function () use ($data, $userId) {
            $expense = new Expense($data);
            $expense->created_by = $userId ?? auth()->id();
            $expense->save();
            return $expense;
        });
    }
    public function update(Expense $entity, array $data): Expense
    {
        return DB::transaction(function () use ($entity, $data) {
            $entity->fill($data)->save();
            return $entity->fresh();
        });
    }
    public function delete(Expense $entity): bool
    {
        return DB::transaction(fn () => (bool) $entity->delete());
    }
}
