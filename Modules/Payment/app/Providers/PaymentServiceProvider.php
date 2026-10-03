<?php
namespace Modules\Payment\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
class PaymentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'payment');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        Route::middleware('web')->group(__DIR__.'/../../routes/web.php');
    }
}
