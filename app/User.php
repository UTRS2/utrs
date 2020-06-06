<?php

namespace App;

use App\Jobs\WikiPermission\LoadLocalPermissionsJob;
use App\Jobs\WikiPermission\LoadGlobalPermissionsJob;
use App\MwApi\MwApiUrls;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $appends = ['verified_wikis'];

    protected static function boot()
    {
        parent::boot();

        // load user permissions after they have been created
        static::created(function (User $user) {
            $user->queuePermissionChecks();
        });
    }

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

    public function logs()
    {
        return $this->morphMany(Log::class, 'object', 'objecttype', 'referenceobject');
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

    /**
     * Queue jobs to load and update permissions of this user on all supported wikis
     */
    public function queuePermissionChecks()
    {
        LoadGlobalPermissionsJob::dispatch($this);

        foreach (MwApiUrls::getSupportedWikis() as $wiki) {
            LoadLocalPermissionsJob::dispatch($this, $wiki);
        }
    }
}
