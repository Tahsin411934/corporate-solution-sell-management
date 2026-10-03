<?php
namespace Modules\ReferralSource\Services;

use Illuminate\Http\JsonResponse;
use Modules\ReferralSource\Models\ReferralSource;
use Yajra\DataTables\Facades\DataTables;

class ReferralSourceDataTableService
{
    public function response(): JsonResponse
    {
        return DataTables::of(ReferralSource::query())
            ->addColumn('actions', fn (ReferralSource $entity) => view('referralsource::partials.actions', compact('entity'))->render())
            ->rawColumns(['actions'])->make(true);
    }
}
