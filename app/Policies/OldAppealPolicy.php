<?php

namespace App\Policies;

use App\Models\Old\Oldappeal;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OldAppealPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Oldappeal $oldappeal)
    {
        return $user->hasAnySpecifiedLocalOrGlobalPerms('enwiki', 'admin') ? true
            : $this->deny('Non-English Wikipedia administrators do not have access to appeals made in UTRS 1.');
    }
}
