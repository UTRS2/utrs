<?php

namespace App\Providers;

use App\Ban;
use App\Log;
use App\Policies\LogPolicy;
use App\User;
use App\Appeal;
use App\Template;
use App\Oldappeal;
use App\Sitenotice;
use App\Policies\AppealPolicy;
use App\Policies\OldAppealPolicy;
use App\Policies\Admin\BanPolicy;
use App\Policies\Admin\UserPolicy;
use Illuminate\Auth\Access\Response;
use App\Policies\Admin\TemplatePolicy;
use App\Policies\Admin\SiteNoticePolicy;
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
        Log::class => LogPolicy::class,
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
    }
}
