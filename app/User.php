<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $attributes = ['verified_wikis'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function checkRead()
    {
        abort_unless($this->verified, 403, 'Your account has not been verified yet.');
        return true;
    }

    public function getVerifiedWikisAttribute()
    {
        return explode(',', $this->wikis ?? '');
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'userid', 'id');
    }

    /**
     * check if this user has any of the specified permissions on any wikis or globally
     * @param array|string $wantedPerms
     * @return bool
     */
    public function hasAnySpecifiedPermsOnAnyWiki($wantedPerms = [])
    {
        if (!is_array($wantedPerms)) {
            $wantedPerms = [$wantedPerms];
        }

        return $this->permissions
            ->contains(function (Permission $permission) use ($wantedPerms) {
                return $permission->hasAnySpecifiedPerms($wantedPerms);
            });
    }

    /**
     * check if this user has any of the specified permissions on any of the specified wikis or globally
     * @param array|string $wikis
     * @param array|string $wantedPerms
     * @return bool
     */
    public function hasAnySpecifiedLocalOrGlobalPerms($wikis = [], $wantedPerms = [])
    {
        if (!is_array($wikis)) {
            $wikis = [$wikis];
        }

        if (!is_array($wantedPerms)) {
            $wantedPerms = [$wantedPerms];
        }

        if (!in_array('*', $wikis)) {
            $wikis[] = '*';
        }

        return $this->permissions
            ->whereIn('wiki', $wikis)
            ->contains(function (Permission $permission) use ($wantedPerms) {
                return $permission->hasAnySpecifiedPerms($wantedPerms);
            });
    }
}
