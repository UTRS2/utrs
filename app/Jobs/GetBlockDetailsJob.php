<?php

namespace App\Jobs;

use App\Appeal;
use App\MwApi\MwApiExtras;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
     * Handle the data returned from the API calls
     * @param  [type] $blockData [description]
     * @return void
     */
    public function handleBlockData($blockData)
    {
        $status = $this->appeal->privacylevel === $this->appeal->privacyreview ? 'OPEN' : 'PRIVACY';

        $this->appeal->update([
            'blockfound' => 1,
            'blockingadmin' => $blockData['by'],
            'blockreason' => $blockData['reason'],
            'status' => $status,
        ]);

        // if not verified and no verify token is set (=not emailed before) on a blocked user, attempt to send an e-mail
        if (!$this->appeal->user_verified && !$this->appeal->verify_token && isset($blockData['user']) && $this->appeal->blocktype !== 0) {
            VerifyBlockJob::dispatch($this->appeal);
        }
    }
    
    /**
     * This processes the block appeal
     * @return void
     */
    public function handle()
    {
        if ($this->appeal->wiki === 'global') {
            $blockData = MwApiExtras::getGlobalBlockInfo($this->appeal->appealfor);

            if (!$blockData) {
                $blockData = MwApiExtras::getGlobalBlockInfo($this->appeal->hiddenip);
            }
        } else {
            if (Str::startsWith($this->appeal->appealfor, '#') && is_numeric(substr($this->appeal->appealfor, 1))) {
                $blockData = MwApiExtras::getBlockInfo($this->appeal->wiki, substr($this->appeal->appealfor, 1), 'bkids');
            } else {
                $blockData = MwApiExtras::getBlockInfo($this->appeal->wiki, $this->appeal->appealfor);
            }

            if (!$blockData && !empty($this->appeal->hiddenip)) {
                $blockData = MwApiExtras::getBlockInfo($this->appeal->wiki, $this->appeal->hiddenip);
            }
        }

        if ($blockData) {
            $this->handleBlockData($blockData);
            return;
        }

        $this->appeal->update([
            'status' => 'NOTFOUND',
        ]);
    }
}
