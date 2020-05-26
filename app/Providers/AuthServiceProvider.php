<?php

namespace App\Providers;

use App\Ban;
use App\User;
use App\Appeal;
use App\Template;
use App\Oldappeal;
use App\Sitenotice;
use App\Policies\AppealPolicy;
use App\Policies\OldAppealPolicy;
use App\Policies\Admin\BanPolicy;
use App\Policies\Admin\UserPolicy;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Access\Response;
use App\Policies\Admin\TemplatePolicy;
use App\Policies\Admin\SiteNoticePolicy;
use App\OAuth\WikiSocialiteServer;
use App\OAuth\WikiSocialiteProvider;
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
        Appeal::class => AppealPolicy::class,
        Ban::class => BanPolicy::class,
        Oldappeal::class => OldAppealPolicy::class,
        Sitenotice::class => SiteNoticePolicy::class,
        Template::class => TemplatePolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function (User $user, $ability) {
            if (!$user->verified) {
                return Response::deny('Your account has not been verified yet.');
            }

            // unless this permission is designed with developers in mind...
            $doNotOverrideDev = [
                'updatePermission', // UserPolicy
            ];

            if ($user->hasAnySpecifiedLocalOrGlobalPerms('*', 'developer')
                && !in_array($ability, $doNotOverrideDev)) {

                // allow developers to do everything they'd ever want
                return true;
            }
        });

        $socialite = $this->app->make('Laravel\Socialite\Contracts\Factory');
        $socialite->extend(
            'wiki',
            function ($app) use ($socialite) {
                $config = $app['config']['services.wiki'];
                return new WikiSocialiteProvider(
                    $this->app['request'], new WikiSocialiteServer($config),
                );
            }
        );
    }
}
