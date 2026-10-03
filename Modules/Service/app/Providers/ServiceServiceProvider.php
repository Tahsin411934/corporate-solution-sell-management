<?php
namespace Modules\Service\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ServiceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'service');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        Route::middleware('web')->group(__DIR__.'/../../routes/web.php');
    }
}
