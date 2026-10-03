<?php
namespace Modules\Service\Services;

use Illuminate\Http\JsonResponse;
use Modules\Service\Models\Service;
use Yajra\DataTables\Facades\DataTables;

class ServiceDataTableService
{
    public function response(): JsonResponse
    {
        return DataTables::of(Service::query())
            ->editColumn('is_active', fn (Service $entity) => $entity->is_active ? 'Active' : 'Inactive')
            ->addColumn('actions', fn (Service $entity) => view('service::partials.actions', compact('entity'))->render())
            ->rawColumns(['actions'])->make(true);
    }
}
