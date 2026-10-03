<?php

namespace Modules\CompanySettings\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\CompanySettings\Models\CompanySetting;

class CompanySettingService
{
    public function active(): ?CompanySetting
    {
        return CompanySetting::query()->active()->latest('id')->first();
    }

    public function save(array $data, ?UploadedFile $logo = null): CompanySetting
    {
        return DB::transaction(function () use ($data, $logo): CompanySetting {
            $setting = CompanySetting::query()->withTrashed()->latest('id')->first();

            if ($setting?->trashed()) {
                $setting->restore();
            }

            if (! $setting) {
                $setting = new CompanySetting();
                $setting->invoice_next_number = 1;
            }

            unset($data['logo']);
            $setting->fill($data);

            if ($logo) {
                if ($setting->logo_path) {
                    Storage::disk('public')->delete($setting->logo_path);
                }

                $setting->logo_path = $logo->store('company-logos', 'public');
            }

            $setting->save();

            return $setting->fresh();
        });
    }

    public function nextInvoiceNumber(): string
    {
        return DB::transaction(function (): string {
            $setting = CompanySetting::query()->lockForUpdate()->active()->firstOrFail();
            $number = $setting->invoice_next_number;

            $setting->increment('invoice_next_number');

            return sprintf('%s-%06d', $setting->invoice_prefix, $number);
        });
    }

    public function removeLogo(CompanySetting $setting): CompanySetting
    {
        if ($setting->logo_path) {
            Storage::disk('public')->delete($setting->logo_path);
            $setting->forceFill(['logo_path' => null])->save();
        }

        return $setting->fresh();
    }
}
