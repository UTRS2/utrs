<?php

namespace App\Providers;

use App\Http\WikiSocialiteProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $socialite = $this->app->make('Laravel\Socialite\Contracts\Factory');
        $socialite->extend(
            'wiki',
            function ($app) use ($socialite) {
                $config = $app['config']['services.wiki'];
                return $socialite->buildProvider(WikiSocialiteProvider::class, $config);
            }
        );
    }
}
