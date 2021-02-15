<?php

namespace App\Jobs\Scheduled;

use App\Models\Appeal;
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

class UpdateWikiAppealListJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var string */
    private $wiki;

    public function __construct(string $wiki)
    {
        $this->wiki = $wiki;
    }

    public function fetchAppeals()
    {
        return Appeal::where('wiki', $this->wiki)
            ->whereNotIn('status', [
                Appeal::STATUS_VERIFY,
                Appeal::STATUS_NOTFOUND,
                Appeal::STATUS_EXPIRE,
                Appeal::STATUS_DECLINE,
                Appeal::STATUS_ACCEPT,
                Appeal::STATUS_INVALID,
            ])
            ->get();
    }

    public function createContents(Collection $appeals)
    {
        if ($appeals->isEmpty()) {
            return 'No open UTRS appeals.';
        }

        $data = '{| align="center" class="wikitable sortable" style="align: center; float:center; font-size: 90%; text-align:center" cellspacing="0" cellpadding="1" valign="middle"'
            . "\n! Appeal Number"
            . "\n! Appealant"
            . "\n! Filed on"
            . "\n! Status\n";

        $data .= $appeals->map(function (Appeal $appeal) {
            return  "|-\n"
                . "| [" . url(route('appeal.view', $appeal)) . ' #' . $appeal->id . "] \n| "
                . (str_starts_with($appeal->appealfor, '#')
                    ? '[{{fullurl:Special:BlockList|wpTarget=' . urlencode($appeal->appealfor) . '}} Block ID ' . $appeal->appealfor . ']'
                    : '[[User talk:' . $appeal->appealfor . '|]]'
                )
                . "\n| " . $appeal->submitted
                . "\n| " . $appeal->status;
        })
            ->join("\n");

        $data .= "\n|}";
        return $data;
    }

    public function handle()
    {
        $page = MediaWikiRepository::getTargetProperty($this->wiki, 'appeal_list_page');

        if (!$page) {
            // if a page hasn't been configured for this wiki, do nothing
            return;
        }

        // get appeals and create table
        $appeals = $this->fetchAppeals();
        $text = $this->createContents($appeals);

        // get page information
        $api = MediaWikiRepository::getApiForTarget($this->wiki);
        $api->login();

        $services = $api->getAddWikiServices();
        $page = $services->newPageGetter()->getFromTitle($page);

        // prepare edit
        $content = new Content($text);
        $revision = new Revision($content, $page->getPageIdentifier());
        $editFlags = new EditInfo('Bot: Updating UTRS appeal list', EditInfo::NOTMINOR, EditInfo::BOT);

        // save it
        $services->newRevisionSaver()->save($revision, $editFlags);
    }
}
