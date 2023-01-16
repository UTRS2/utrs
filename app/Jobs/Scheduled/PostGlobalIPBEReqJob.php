<?php

namespace App\Jobs\Scheduled;

use App\Models\Appeal;
use App\Models\LogEntry;
use App\Services\Facades\MediaWikiRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Mediawiki\DataModel\Content;
use Mediawiki\DataModel\EditInfo;
use Mediawiki\DataModel\Revision;

class PostGlobalIPBEReqJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function fetchAppeals()
    {
        return Appeal::where('wiki', 3)
            ->whereNotIn('status', [
                Appeal::STATUS_VERIFY,
                Appeal::STATUS_NOTFOUND,
                Appeal::STATUS_EXPIRE,
                Appeal::STATUS_DECLINE,
                Appeal::STATUS_ACCEPT,
                Appeal::STATUS_INVALID,
            ])
            ->where('blockreason','LIKE', '%open prox%')
            ->where('user_verified',1)
            ->whereNot('handlingAdmin',3823)
            ->get();
    }

    public function createContents(Collection $appeals)
    {
        if ($appeals->isEmpty()) {
            return 'No open UTRS appeals.';
        }
        $data = '';
        foreach ($appeals as $appeal) {
            $data .= '=== Global IP Block Exempt for {{subst:u|'.$appeal->appealfor.'}} ===\n
            {{sr-request
                |status = \n
                |domain = global\n
                |user name = '.$appeal->appealfor.'\n
                }}\n
                Per [utrs-beta.wmflabs.org/appeal/'.$appeal->id.' UTRS #'.$appeal->id.'] --~~~~
            ';
            $currentAppeal = Appeal::id($appeal->id);
            $currentAppeal->handlingAdmin = 3823;
            $currentAppeal->save();
            LogEntry::create([
                'user_id'    => 3823,
                'model_id'   => $appeal->id,
                'model_type' => Appeal::class,
                'reason'     => NULL,
                'action'     => "reserve",
                'ip'         => "127.0.0.1",
                'ua'         => "DB/Laravel/SRGP Script",
                'protected'  => Log::LOG_PROTECTION_NONE,
            ]);
            LogEntry::create([
                'user_id'    => 3823,
                'model_id'   => $appeal->id,
                'model_type' => Appeal::class,
                'reason'     => "posted IPBE request onwiki",
                'action'     => "comment",
                'ip'         => "127.0.0.1",
                'ua'         => "DB/Laravel/SRGP Script",
                'protected'  => Log::LOG_PROTECTION_NONE,
            ]);
        }
        return $data;
    }

    public function handle()
    {
        $page = MediaWikiRepository::getTargetProperty('global', 'SRGP');

        if (!$page) {
            // if a page hasn't been configured for this wiki, do nothing
            return;
        }

        // get appeals and create table
        $appeals = $this->fetchAppeals();
        $text = $this->createContents($appeals);

        // get page information
        $api = MediaWikiRepository::getApiForTarget('global');
        $api->login();

        $services = $api->getAddWikiServices();
        $page = $services->newPageGetter()->getFromTitle($page);

        // prepare edit
        $existing = $page->getRevisions()->getLatest()->getContent()->getData();
        $pos = strpos($existing,'== Requests for 2 Factor Auth tester permissions ==');
        $newtext = substr_replace($existing,$text.'\n',$pos,0);
        $content = new Content($newtext);
        $revision = new Revision($content, $page->getPageIdentifier());
        $editFlags = new EditInfo('Script: Adding UTRS IPBE appeals to SRG - Manual request of AmandaNP', EditInfo::NOTMINOR/*, EditInfo::BOT*/);

        // save it
        $services->newRevisionSaver()->save($revision, $editFlags);
    }
}
