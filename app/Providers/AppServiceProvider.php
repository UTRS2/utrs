<?php

namespace App\Providers;

use App\Services\MediaWiki\Api\MediaWikiRepository;
use App\Services\MediaWiki\Implementation\RealMediaWikiRepository;
use App\Services\Version\Api\Version;
use App\Services\Version\Implementation\RealVersion;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
            MediaWikiRepository::class,
            RealMediaWikiRepository::class,
        );

        $this->app->alias(MediaWikiRepository::class, 'mediawiki');

        $this->app->bind(
            Version::class,
            RealVersion::class,
        );

        $this->app->alias(Version::class, 'utrs_version');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFour();

        if (Request::has('uselang')) {
            App::setLocale(Request::get('uselang', 'en'));
        }
    }
}
