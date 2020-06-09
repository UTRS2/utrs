<?php

namespace App;

use RuntimeException;
use App\MwApi\MwApiUrls;
use Illuminate\Database\Eloquent\Model;

class Appeal extends Model
{
    const REPLY_STATUS_CHANGE_OPTIONS = [
        self::STATUS_OPEN           => self::STATUS_OPEN,
        self::STATUS_AWAITING_REPLY => self::STATUS_AWAITING_REPLY,
        self::STATUS_ACCEPT         => self::STATUS_ACCEPT,
        self::STATUS_DECLINE        => self::STATUS_DECLINE,
        self::STATUS_EXPIRE         => self::STATUS_EXPIRE,
    ];

    const REGULAR_NO_VIEW_STATUS = [
        self::STATUS_INVALID        => self::STATUS_INVALID,
        self::STATUS_NOTFOUND       => self::STATUS_NOTFOUND,
        self::STATUS_VERIFY         => self::STATUS_VERIFY,
    ];

    const STATUS_OPEN = 'OPEN';
    const STATUS_VERIFY = 'VERIFY'; // appeals that are waiting to be checked from MediaWiki API
    const STATUS_AWAITING_REPLY = 'AWAITING_REPLY';

    // statuses that are waiting for a specific person/group of them
    const STATUS_ADMIN = 'ADMIN'; // tooladmin? idk
    const STATUS_CHECKUSER = 'CHECKUSER'; // waiting for a CheckUser check
    // hidden statuses
    const STATUS_NOTFOUND = 'NOTFOUND'; // for users where the block is not found

    // closed statuses
    const STATUS_INVALID = 'INVALID'; // duplicates etc, only visible to devs
    const STATUS_ACCEPT = 'ACCEPT';
    const STATUS_DECLINE = 'DECLINE';
    const STATUS_EXPIRE = 'EXPIRE'; // appeal went too long without any changes

    public $timestamps = false;
    public $guarded = ['id'];

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

    // ideally this would be named handlingAdmin and the field would be named handling_admin_id per laravel norms
    public function handlingAdminObject()
    {
        return $this->belongsTo(User::class, 'handlingadmin', 'id');
    }

    public function getFormattedBlockReason($linkExtra = '')
    {
        if (!$this->blockreason || strlen($this->blockreason) === 0) {
            return '';
        }

        $linkPrefix = MwApiUrls::getWikiProperty($this->wiki, 'url_base') . 'wiki/';
        $reason = htmlspecialchars($this->blockreason);

        preg_match_all('/\[\[([a-zA-Z9-9 _:\-\/]+)(?:\|([a-zA-Z9-9 _:\-\/]+))?\]\]/', $reason, $linkMatches, PREG_SET_ORDER);

        foreach ($linkMatches as $link) {
            $linkText = sizeof($link) === 3 ? $link[2] : $link[1];
            $linkHtml = '<a href="' . $linkPrefix . htmlspecialchars($link[1]) . '" ' . $linkExtra . '>' . htmlspecialchars($linkText) . '</a>';

            $reason = str_replace($link[0], $linkHtml, $reason);
        }

        preg_match_all('/{{([a-zA-Z9-9 _:\-\/]+)(?:\|([a-zA-Z9-9 _:=\-\/\|]+))?}}/', $reason, $templateMatches, PREG_SET_ORDER);

        foreach ($templateMatches as $template) {
            $templateHtml = '{{<a href="' . $linkPrefix . 'Template:' . htmlspecialchars($template[1]) . '" ' . $linkExtra . '>' . htmlspecialchars($template[1]) . '</a>';

            if (sizeof($template) === 3) {
                $templateHtml .= '|' . htmlspecialchars($template[2]);
            }

            $templateHtml .= '}}';

            $reason = str_replace($template[0], $templateHtml, $reason);
        }

        return $reason;
    }
}
