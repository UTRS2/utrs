<?php

namespace App\Jobs\WikiPermission;

use App\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use App\MediawikiIntegration\WikiApiUrls;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LoadLocalPermissionsJob extends BaseWikiPermissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $wiki;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param string $wiki
     */
    public function __construct(User $user, string $wiki)
    {
        $this->user = $user;
        $this->wiki = $wiki;
    }

    public function getWikiUrl()
    {
        return WikiApiUrls::getWikiUrl($this->wiki);
    }

    public function getWikiId()
    {
        return $this->wiki;
    }

    public function getPermissionsToCheck()
    {
        return [
            'user',
            'sysop',
            'checkuser',
            'oversight',
        ];
    }

    protected function updateDoesExist(bool $exists)
    {
        $wikis = explode(',', $this->user->wikis ?? '');
        $wikiId = $this->getWikiId();

        if ($exists) {
            $wikis = array_push($wikis, $wikiId);
        } else {
            // according to stackoverflow this is the best way to remove an element from an array
            $wikis = array_filter($wikis, function($value) use ($wikiId) { return $value !== $wikiId; });
        }

        $this->user->wikis = implode(',', $wikis);
        $this->user->save();
    }
}
