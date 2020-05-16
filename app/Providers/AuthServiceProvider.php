<?php

namespace App\Providers;

use App\User;
use App\Appeal;
use App\Oldappeal;
use App\Policies\AppealPolicy;
use App\Policies\OldAppealPolicy;
use Illuminate\Auth\Access\Response;
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
        Oldappeal::class => OldAppealPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function (User $user) {
            if (!$user->verified) {
                return Response::deny('Your account has not been verified yet.');
            }

            if ($user->globalPermissions && $user->globalPermissions->hasAnyPerms(['developer'])) {
                // allow developers
                return true;
            }
        });
    }
}
