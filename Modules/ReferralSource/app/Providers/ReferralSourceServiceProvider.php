<?php
namespace Modules\ReferralSource\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ReferralSourceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'referralsource');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        Route::middleware('web')->group(__DIR__.'/../../routes/web.php');
    }
}
