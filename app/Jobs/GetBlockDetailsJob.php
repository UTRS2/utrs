<?php

namespace App\Jobs;

use App\Ban;
use App\Log;
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
        $status = 'OPEN';

        if (isset($blockData['user']) && !empty($blockData['user']) && $this->appeal->appealfor !== $blockData['user']) {
            $this->appeal->appealfor = $blockData['user'];

            $ban = Ban::where('ip', '=', 0)
                ->where('target', $this->appeal->appealfor)
                ->active()
                ->first();

            if ($ban) {
                $status = 'INVALID';
            }

            Log::create([
                'user' => 0,
                'referenceobject' => $this->appeal->id,
                'objecttype' => 'appeal',
                'action' => 'closed - invalidate',
                'reason' => 'account banned from UTRS',
                'ip' => '127.0.0.1',
                'ua' => 'Laravel',
                'protected' => 0
            ]);
        }

        $this->appeal->update([
            'blockfound' => 1,
            'blockingadmin' => $blockData['by'],
            'blockreason' => $blockData['reason'],
            'status' => $status,
        ]);

        // if not verified and no verify token is set (=not emailed before) on a blocked user, attempt to send an e-mail
        if (!$this->appeal->user_verified && !$this->appeal->verify_token
            && isset($blockData['user']) && $this->appeal->blocktype !== 0 && $this->appeal->status !== 'INVALID') {
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

            if (!$blockData && !empty($this->appeal->hiddenip)) {
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
