<?php

namespace App;

use RuntimeException;
use App\MwApi\MwApiUrls;
use Illuminate\Database\Eloquent\Model;

class Appeal extends Model
{
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

    public function handlingAdminObject()
    {
        return $this->belongsTo(User::class, 'handlingadmin', 'id');
    }

    public function getFormattedBlockReason($linkClass = '')
    {
        if (!$this->blockreason || strlen($this->blockreason) === 0) {
            return '';
        }

        $linkPrefix = MwApiUrls::getWikiProperty($this->wiki, 'url_base') . 'wiki/';
        $reason = htmlspecialchars($this->blockreason);

        preg_match_all('/\[\[([a-zA-Z9-9 _:\-\/]+)(?:\|([a-zA-Z9-9 _:\-\/]+))?\]\]/', $reason, $linkMatches, PREG_SET_ORDER);

        foreach ($linkMatches as $link) {
            $linkText = sizeof($link) === 3 ? $link[2] : $link[1];
            $linkHtml = '<a href="' . $linkPrefix . htmlspecialchars($link[1]) . '" class="' . $linkClass . '">' . htmlspecialchars($linkText) . '</a>';

            $reason = str_replace($link[0], $linkHtml, $reason);
        }

        preg_match_all('/{{([a-zA-Z9-9 _:\-\/]+)(?:\|([a-zA-Z9-9 _:=\-\/\|]+))?}}/', $reason, $templateMatches, PREG_SET_ORDER);

        foreach ($templateMatches as $template) {
            $templateHtml = '{{<a href="' . $linkPrefix . 'Template:' . htmlspecialchars($template[1]) . '" class="' . $linkClass . '">' . htmlspecialchars($template[1]) . '</a>';

            if (sizeof($template) === 3) {
                $templateHtml .= '|' . htmlspecialchars($template[2]);
            }

            $templateHtml .= '}}';

            $reason = str_replace($template[0], $templateHtml, $reason);
        }

        return $reason;
    }
}
