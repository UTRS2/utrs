<?php

namespace App\Providers;

use App\Models\Wiki;
use App\Models\Appeal;
use App\Models\Ban;
use App\Models\LogEntry;
use App\Policies\WikiPolicy;
use App\Models\Old\Oldappeal;
use App\Models\Sitenotice;
use App\Models\Template;
use App\Models\User;
use App\Policies\AppealPolicy;
use App\Policies\LogEntryPolicy;
use App\Policies\OldAppealPolicy;
use App\Policies\Admin\BanPolicy;
use App\Policies\Admin\SiteNoticePolicy;
use App\Policies\Admin\TemplatePolicy;
use App\Policies\Admin\UserPolicy;
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
        LogEntry::class => LogEntryPolicy::class,
        Oldappeal::class => OldAppealPolicy::class,
        Sitenotice::class => SiteNoticePolicy::class,
        Template::class => TemplatePolicy::class,
        User::class => UserPolicy::class,
        Wiki::class => WikiPolicy::class,
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
