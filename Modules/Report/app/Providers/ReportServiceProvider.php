<?php
namespace Modules\Report\Providers;
use Illuminate\Support\ServiceProvider;
class ReportServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'report');
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
