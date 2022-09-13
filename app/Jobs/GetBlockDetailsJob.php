<?php

namespace App\Jobs;

use App\Models\Appeal;
use App\Models\Ban;
use App\Models\LogEntry;
use App\Services\MediaWiki\Api\Data\Block;
use App\Services\MediaWiki\Api\MediaWikiRepository;
use App\Utils\IPUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class GetBlockDetailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $appeal;

    /**
     * Construct the GetBlockDetails Jobs
     * @param Appeal $appeal - the Appeal object of the current appeal
     */
    public function __construct(Appeal $appeal)
    {
        $this->appeal = $appeal;
    }

    /**
     * Utility method to check if block target given by user needs correcting
     * @param string $givenBlockTarget Block target given by the blocked user in the form
     * @param string $actualBlockTarget Block target queried from MediaWiki API
     * @return bool true if block target should be corrected in the database, false otherwise
     */
    private function shouldCorrectBlockTarget(string $givenBlockTarget, string $actualBlockTarget)
    {
        // if it's already correct, no need to do anything
        if (strtolower($givenBlockTarget) === strtolower($actualBlockTarget)) {
            return false;
        }

        // if it's a range and given ip is inside it, no need to do anything
        if (IPUtils::isIpRange($actualBlockTarget) && IPUtils::isIp($givenBlockTarget)
            && IPUtils::isIpInsideRange($actualBlockTarget, $givenBlockTarget)) {
            return false;
        }

        return true;
    }

    /**
     * Handle the data returned from the API calls
     * @param Block $block block details from mediawiki api
     * @return void
     */
    public function handleBlockData(Block $block)
    {
        $status = Appeal::STATUS_OPEN;

        if ($block && $this->shouldCorrectBlockTarget($this->appeal->appealfor, $block->getBlockTarget())) {
            //$this->appeal->appealfor = $block->getBlockTarget();

            $duplicateAppeal = Appeal::where('appealfor', $this->appeal->appealfor)
                ->where('id', '!=', $this->appeal->id) // data should not be saved yet, but just in case
                ->openOrRecent()
                ->first();

            if ($duplicateAppeal) {
                $status = Appeal::STATUS_INVALID;

                LogEntry::create([
                    'user_id' => 0,
                    'model_id' => $this->appeal->id,
                    'model_type' => Appeal::class,
                    'action' => 'closed - duplicate',
                    'reason' => 'this appeal duplicates appeal #' . $duplicateAppeal->id,
                    'ip' => 'DB entry',
                    'ua' => 'DB/1',
                    'protected' => LogEntry::LOG_PROTECTION_NONE,
                ]);
            }

            $banTargets = Ban::getTargetsToCheck($this->appeal->appealfor);
            $ban = Ban::whereIn('target', $banTargets)
                ->wikiIdOrGlobal($this->appeal->wiki_id)
                ->active()
                ->first();

            if ($ban) {
                $status = Appeal::STATUS_INVALID;

                LogEntry::create([
                    'user_id' => 0,
                    'model_id' => $this->appeal->id,
                    'model_type' => Appeal::class,
                    'action' => 'closed - invalidate',
                    'reason' => 'banned from UTRS',
                    'ip' => 'DB entry',
                    'ua' => 'DB/1',
                    'protected' => 0
                ]);
            }
        }

        $this->appeal->update([
            'blockfound' => 1,
            'blockingadmin' => $block->getBlockingUser(),
            'blockreason' => $block->getBlockReason(),
            'status' => $status,
        ]);

        // if not verified and no verify token is set (=not emailed before) on a blocked user, attempt to send an e-mail
        if (!$this->appeal->user_verified && !$this->appeal->verify_token
            && $block && $this->appeal->blocktype !== 0
            && $this->appeal->status !== Appeal::STATUS_INVALID) {
            VerifyBlockJob::dispatch($this->appeal);
        }
    }

    /**
     * This processes the block appeal
     * @param MediaWikiRepository $mediaWikiRepository
     * @return void
     */
    public function handle(MediaWikiRepository $mediaWikiRepository)
    {
        if ($this->appeal->wiki === 'global') {
            $mediaWikiExtras = $mediaWikiRepository->getGlobalApi()->getMediaWikiExtras();

            $block = $mediaWikiExtras->getGlobalBlockInfo($this->appeal->appealfor, $this->appeal->id);

            if (!$block && !empty($this->appeal->hiddenip)) {
                $block = $mediaWikiExtras->getGlobalBlockInfo($this->appeal->hiddenip, $this->appeal->id);
            }
        } else {
            $mediaWikiExtras = $mediaWikiRepository->getApiForTarget($this->appeal->wiki)->getMediaWikiExtras();

            if (Str::startsWith($this->appeal->appealfor, '#') && is_numeric(substr($this->appeal->appealfor, 1))) {
                $block = $mediaWikiExtras->getBlockInfo(substr($this->appeal->appealfor, 1), $this->appeal->id, 'bkids');
            } else {
                $block = $mediaWikiExtras->getBlockInfo($this->appeal->appealfor, $this->appeal->id);
            }

            if (!$block && !empty($this->appeal->hiddenip)) {
                $block = $mediaWikiExtras->getBlockInfo($this->appeal->hiddenip, $this->appeal->id);
            }
        }

        if ($block) {
            $this->handleBlockData($block);
            return;
        }

        $this->appeal->update([
            'status' => Appeal::STATUS_NOTFOUND,
        ]);
    }

    public function displayName(): string
    {
        return get_class($this) . ': appeal #' . $this->appeal->id;
    }
}
