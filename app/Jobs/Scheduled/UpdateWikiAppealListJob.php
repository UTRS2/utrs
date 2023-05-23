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
            . "\n! Block Type"
            . "\n! Appellant"
            . "\n! Filed on"
            . "\n! Status"
            . "\n! Verified?\n";

        $data .= $appeals->map(function (Appeal $appeal) {
            switch ($appeal->user_verified) {
                case 1:
                    $image = 'Oxygen480-status-security-high.svg';
                    break;
                case 0:
                    $image = 'Oxygen480-status-security-medium.svg';
                    break;
                case -1:
                    $image = 'Oxygen480-status-security-low.svg';
                    break;
                
                default:
                    $image = 'File:Oxygen480-status-dialog-information.svg';
                    break;
            }
            if ($appeal->blocktype == 2) {
                $blocktype = "IP block underneath an account";
            }
            if ($appeal->blocktype == 1) {
                $blocktype = "Account";
            }
            if ($appeal->blocktype == 0) {
                $blocktype = "IP address";
            }
            return  "|-\n"
                . "| [" . url(route('appeal.view', $appeal)) . ' #' . $appeal->id . "] \n| "
                . "| ". $blocktype . " \n| "
                . (str_starts_with($appeal->appealfor, '#')
                    ? '[{{fullurl:Special:BlockList|wpTarget=' . urlencode($appeal->appealfor) . '}} Block ID ' . $appeal->appealfor . ']'
                    : '[[User talk:' . $appeal->appealfor . '|]]'
                )
                . "\n| " . $appeal->submitted
                . "\n| " . $appeal->status
                . "\n| [[File:" . $image . "|20px]]";
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
