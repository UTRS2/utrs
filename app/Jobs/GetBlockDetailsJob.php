<?php

namespace App\Jobs;

use App\Appeal;
use App\MwApi\MwApiExtras;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetBlockDetailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $appeal;

    public function __construct(Appeal $appeal)
    {
        $this->appeal = $appeal;
    }

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
        if (!$this->appeal->user_verified && !$this->appeal->verify_token && isset($blockData['user'])) {
            VerifyBlockJob::dispatch($this->appeal);
        }
    }

    public function handle()
    {
        if ($this->appeal->wiki === 'global') {
            $blockData = MwApiExtras::getGlobalBlockInfo($this->appeal->appealfor);
        } else {
            $blockData = MwApiExtras::getBlockInfo($this->appeal->wiki, $this->appeal->appealfor);

            if (!$blockData) {
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
