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

    public function checkRead() {
        abort_unless($this->verified, 403,'User is not verified');
        return true;
    }

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
