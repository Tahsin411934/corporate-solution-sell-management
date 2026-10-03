<?php
namespace Modules\AuditLog\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\AuditLog\Observers\AuditObserver;
class AuditLogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'auditlog');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        Route::middleware('web')->group(__DIR__.'/../../routes/web.php');
        foreach ([
            \App\Models\User::class,
            \Modules\CompanySettings\Models\CompanySetting::class,
            \Modules\Customer\Models\Customer::class,
            \Modules\Service\Models\Service::class,
            \Modules\ReferralSource\Models\ReferralSource::class,
            \Modules\Invoice\Models\Invoice::class,
            \Modules\Invoice\Models\InvoiceItem::class,
            \Modules\Payment\Models\Payment::class,
            \Modules\Expense\Models\Expense::class,
        ] as $model) $model::observe(AuditObserver::class);
    }
}
