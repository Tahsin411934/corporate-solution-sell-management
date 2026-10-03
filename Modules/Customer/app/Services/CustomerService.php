<?php

namespace Modules\Customer\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Models\Customer;

class CustomerService
{
    public function nextCode(): string
    {
        return 'CS-'.str_pad((string) DB::table('customer_code_sequences')->where('id', 1)->value('next_number'), 5, '0', STR_PAD_LEFT);
    }
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $perPage = min(max($perPage, 1), 100);

        return Customer::query()
            ->search($search)
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data, ?int $userId = null): Customer
    {
        return DB::transaction(function () use ($data, $userId): Customer {
            $sequence = DB::table('customer_code_sequences')->where('id', 1)->lockForUpdate()->first();
            $number = $sequence->next_number;
            do {
                $code = 'CS-'.str_pad((string) $number++, 5, '0', STR_PAD_LEFT);
            } while (Customer::withTrashed()->where('customer_code', $code)->exists());
            DB::table('customer_code_sequences')->where('id', 1)->update(['next_number' => $number]);
            $data['customer_code'] = $code;
            $data['created_by'] = $userId ?? auth()->id();
            $data['opening_balance'] = $data['opening_balance'] ?? 0;

            return Customer::create($data);
        });
    }

    public function update(Customer $customer, array $data): Customer
    {
        return DB::transaction(function () use ($customer, $data): Customer {
            unset($data['customer_code']);
            $customer->update($data);

            return $customer->fresh();
        });
    }

    public function delete(Customer $customer): bool
    {
        return DB::transaction(fn (): bool => (bool) $customer->delete());
    }

    public function restore(int|Customer $customer): Customer
    {
        return DB::transaction(function () use ($customer): Customer {
            $model = $customer instanceof Customer
                ? Customer::withTrashed()->findOrFail($customer->getKey())
                : Customer::withTrashed()->findOrFail($customer);

            $model->restore();

            return $model->fresh();
        });
    }

    public function find(int $id): Customer
    {
        return Customer::query()->findOrFail($id);
    }
}
