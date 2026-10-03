<?php
namespace Modules\Service\Services;

use Illuminate\Support\Facades\DB;
use Modules\Service\Models\Service;

class ServiceService
{
    public function create(array $data): Service
    {
        return DB::transaction(fn () => Service::create($data));
    }
    public function update(Service $entity, array $data): Service
    {
        return DB::transaction(function () use ($entity, $data) {
            $entity->update($data);
            return $entity->fresh();
        });
    }
    public function delete(Service $entity): bool
    {
        return DB::transaction(fn () => (bool) $entity->delete());
    }
}
