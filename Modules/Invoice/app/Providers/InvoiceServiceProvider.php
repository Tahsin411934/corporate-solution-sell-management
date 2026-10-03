<?php
namespace Modules\Invoice\Providers;
use Illuminate\Support\ServiceProvider;
class InvoiceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'invoice');
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
    }
}
