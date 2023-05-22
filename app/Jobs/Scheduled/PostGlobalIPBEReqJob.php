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
use DB;

class PostGlobalIPBEReqJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function fetchAppeals()
    {
        $query = Appeal::where('wiki_id', 3)
            ->whereNotIn('status', [
                Appeal::STATUS_VERIFY,
                Appeal::STATUS_NOTFOUND,
                Appeal::STATUS_EXPIRE,
                Appeal::STATUS_DECLINE,
                Appeal::STATUS_ACCEPT,
                Appeal::STATUS_INVALID,
            ])
            ->where('blockreason','RLIKE', '(O|o)pen prox')
            ->where('user_verified',1)
            ->whereNull('handlingAdmin')
            ->leftJoin('log_entries', function ($join) {
                $join->on('log_entries.model_id','=','appeals.id')
                    ->where('log_entries.reason','RLIKE','posted IPBE request onwiki')
                    ->where('log_entries.user_id',3823);
            })
            ->select('appeals.*')
            ->get();
        /*This is the query needing to be ran:
        select * from appeals left join log_entries on (log_entries.model_id = appeals.id and log_entries.reason NOT RLIKE 'posted IPBE request onwiki' and log_entries.user_id = 3823) where wiki_id = 3 and status not in ('EXPIRE','VERIFY','NOTFOUND','DECLINE','ACCEPT','INVALID') and blockreason RLIKE '(O|o)pen prox' and user_verified=1 and handlingAdmin is null;
        */

        return $query;
    }

    public function createContents(Collection $appeals)
    {
        if ($appeals->isEmpty()) {
            return false;
        }
        $data = '';
        foreach ($appeals as $appeal) {
            $data .= '=== Global IP Block Exempt for {{subst:u|'.$appeal->appealfor.'}} ===
{{sr-request
|status = 
|domain = global
|user name = '.$appeal->appealfor.'
}}
Per [https://utrs-beta.wmflabs.org/appeal/'.$appeal->id.' UTRS #'.$appeal->id.'] --~~~~

';
            $currentAppeal = Appeal::findOrFail($appeal->id);
            $gg = MediaWikiRepository::getApiForTarget('global')->getMediaWikiExtras()->getGlobalGroupMembership($appeal->appealfor);
            if (in_array('global-ipblock-exempt', $gg)) {
                $currentAppeal->status = Appeal::STATUS_ACCEPT;
                $currentAppeal->save();
                LogEntry::create([
                    'user_id'    => 3823,
                    'model_id'   => $appeal->id,
                    'model_type' => Appeal::class,
                    'reason'     => "User already has GIPBE, appeal not needed.",
                    'action'     => "comment",
                    'ip'         => "127.0.0.1",
                    'ua'         => "DB/Laravel/SRGP Script",
                    'protected'  => LogEntry::LOG_PROTECTION_NONE,
                ]);
                $this->info($currentAppeal->appealfor . ' already has GIPBE...ignore...');
                continue;
            }
            
            $currentAppeal->save();
            LogEntry::create([
                'user_id'    => 3823,
                'model_id'   => $appeal->id,
                'model_type' => Appeal::class,
                'reason'     => "posted IPBE request onwiki",
                'action'     => "comment",
                'ip'         => "127.0.0.1",
                'ua'         => "DB/Laravel/SRGP Script",
                'protected'  => LogEntry::LOG_PROTECTION_NONE,
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
        if(!$appeals) {return;}
        $text = $this->createContents($appeals);

        // get page information
        $api = MediaWikiRepository::getApiForTarget('global');
        $api->login();

        $services = $api->getAddWikiServices();
        $page = $services->newPageGetter()->getFromTitle($page);

        // prepare edit
        $existing = $page->getRevisions()->getLatest()->getContent()->getData();
        $pos = strpos($existing,'== Requests for 2 Factor Auth tester permissions ==');
        $newtext = substr_replace($existing,$text.'',$pos,0);
        $content = new Content($newtext);
        $revision = new Revision($content, $page->getPageIdentifier());
        $editFlags = new EditInfo('Script: Adding UTRS IPBE appeals to SRGP', EditInfo::NOTMINOR, EditInfo::BOT);

        // save it
        $services->newRevisionSaver()->save($revision, $editFlags);
    }
}
