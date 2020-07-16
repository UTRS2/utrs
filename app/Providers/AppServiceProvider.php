<?php

namespace App\Providers;

use App\Services\Acc\Api\AccIntegration;
use App\Services\Acc\Implementation\RealAccIntegration;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            AccIntegration::class,
            RealAccIntegration::class,
        );

        $this->app->alias(AccIntegration::class, 'acc');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        if (Request::has('uselang')) {
            App::setLocale(Request::get('uselang', 'en'));
        }
    }
}
