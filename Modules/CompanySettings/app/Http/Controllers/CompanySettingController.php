<?php

namespace Modules\CompanySettings\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\CompanySettings\Http\Requests\StoreCompanySettingRequest;
use Modules\CompanySettings\Http\Requests\UpdateCompanySettingRequest;
use Modules\CompanySettings\Models\CompanySetting;
use Modules\CompanySettings\Services\CompanySettingService;
use Illuminate\Routing\Controller;

class CompanySettingController extends Controller
{
    public function __construct(private readonly CompanySettingService $service) {}

    public function index()
    {
        return view('companysettings::index', [
            'setting' => $this->service->active(),
        ]);
    }

    public function store(StoreCompanySettingRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $setting = $this->service->save($data, $request->file('logo'));

        return redirect()->route('companysettings.index')
            ->with('success', 'Company settings saved successfully.');
    }

    public function update(UpdateCompanySettingRequest $request, CompanySetting $companySetting): RedirectResponse
    {
        $this->service->save($request->validated(), $request->file('logo'));

        return redirect()->route('companysettings.index')
            ->with('success', 'Company settings updated successfully.');
    }

    public function destroyLogo(CompanySetting $companySetting): RedirectResponse
    {
        $this->service->removeLogo($companySetting);

        return back()->with('success', 'Company logo removed successfully.');
    }

    public function previewInvoiceNumber(): JsonResponse
    {
        $setting = $this->service->active();

        abort_unless($setting, 404, 'Company settings not found.');

        return response()->json([
            'prefix' => $setting->invoice_prefix,
            'next_number' => $setting->invoice_next_number,
            'preview' => sprintf('%s-%06d', $setting->invoice_prefix, $setting->invoice_next_number),
        ]);
    }
}
