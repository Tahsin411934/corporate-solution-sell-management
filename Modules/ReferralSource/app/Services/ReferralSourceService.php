<?php
namespace Modules\ReferralSource\Services;

use Illuminate\Support\Facades\DB;
use Modules\ReferralSource\Models\ReferralSource;

class ReferralSourceService
{
    public function create(array $data): ReferralSource
    {
        return DB::transaction(fn () => ReferralSource::create($data));
    }
    public function update(ReferralSource $entity, array $data): ReferralSource
    {
        return DB::transaction(function () use ($entity, $data) {
            $entity->update($data);
            return $entity->fresh();
        });
    }
    public function delete(ReferralSource $entity): bool
    {
        return DB::transaction(fn () => (bool) $entity->delete());
    }
}
