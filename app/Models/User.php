<?php

namespace App\Models;

use App\Jobs\WikiPermission\LoadGlobalPermissionsJob;
use App\Jobs\WikiPermission\LoadLocalPermissionsJob;
use App\Services\Facades\MediaWikiRepository;
use App\Utils\Logging\Loggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Bus;

class User extends Authenticatable
{
    use Notifiable;
    use HasFactory;
    use Loggable;

    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
    protected $appends = ['verified_wikis'];
    protected $dates = ['last_permission_check_at'];

    protected $hidden = [
        'remember_token',
    ];

    protected static function boot()
    {
        parent::boot();

        // load user permissions after they have been created
        static::created(function (User $user) {
            $user->queuePermissionChecks();
        });
    }

    /**
     * Queue jobs to load and update permissions of this user on all supported wikis
     */
    public function queuePermissionChecks()
    {
        $localJobs = collect(MediaWikiRepository::getSupportedTargets(false))
            ->map(function ($wiki) {
                return new LoadLocalPermissionsJob($this, $wiki);
            })
            ->toArray();

        Bus::batch(array_merge(
            [new LoadGlobalPermissionsJob($this)],
            $localJobs,
        ))
            ->then(function () {
                $this->update([
                    'last_permission_check_at' => now(),
                ]);
            })
            ->dispatch();
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
     * @param array|string|null $wikis
     * @param array|string $wantedPerms
     * @return bool
     */
    public function hasAnySpecifiedLocalOrGlobalPerms($wikis = [], $wantedPerms = [])
    {
        if (!is_array($wikis)) {
            $wikis = [$wikis];
        }

        if (!is_array($wantedPerms)) {
            // if null is passed in, just make that an empty array
            $wantedPerms = $wantedPerms ? [$wantedPerms] : [];
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
