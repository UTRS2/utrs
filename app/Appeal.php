<?php

namespace App;

use RuntimeException;
use Illuminate\Database\Eloquent\Model;

class Appeal extends Model
{
    const STATUS_OPEN = 'OPEN';
    const STATUS_VERIFY = 'VERIFY'; // appeals that are waiting to be checked from MediaWiki API

    // statuses that are waiting for a specific person/group of them
    const STATUS_PRIVACY = 'PRIVACY'; // waiting for privacy review
    const STATUS_ADMIN = 'ADMIN'; // tooladmin? idk
    const STATUS_CHECKUSER = 'CHECKUSER'; // waiting for a CheckUser check

    // closed statuses
    const STATUS_INVALID = 'INVALID'; // duplicates etc, only visible to devs
    const STATUS_NOTFOUND = 'NOTFOUND'; // for users that are not blocked
    const STATUS_ACCEPT = 'ACCEPT';
    const STATUS_DECLINE = 'DECLINE';
    const STATUS_EXPIRE = 'EXPIRE'; // appeal went too long without any changes

    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $attributes = [
        'privacylevel' => 0,
        'blockfound' => 0
    ];

    protected $casts = [
        'user_verified' => 'boolean',
    ];

    public function comments()
    {
        return $this->hasMany('App\Log', 'referenceobject','id')
            ->where('objecttype', 'appeal');
    }

    public function getWikiEmailUsername()
    {
        // based on source code 0 seems to mean ip, 1 = direct user block, and 2 = ip below account
        // we can't send e-mail to IPs
        if ($this->blocktype === 0) {
            throw new RuntimeException('Can not send an e-mail to an IP address');
        }

        return $this->appealfor;
    }

    public function handlingAdminObject()
    {
        return $this->belongsTo(User::class, 'handlingadmin', 'id');
    }
}
