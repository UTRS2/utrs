<?php

namespace App\Jobs;

use App\Appeal;
use App\MwApi\MwApiGetter;
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

    public function handle()
    {
        if ($this->appeal->wiki === 'global') {
            // global b(lock)
            throw new \RuntimeException('not implemented yet');
        } else {
            $details = MwApiExtras::getBlockInfo($this->appeal->wiki, $this->appeal->appealfor);

            if (!$details) {
                $this->appeal->update([
                    'status' => 'NOTFOUND',
                ]);

                return;
            }

            $status = $this->appeal->privacylevel === $this->appeal->privacyreview ? 'OPEN' : 'PRIVACY';

            $this->appeal->update([
                'blockfound' => 1,
                'blockingadmin' => $details['by'],
                'blockreason' => $details['reason'],
                'status' => $status,
            ]);

            // if not verified and no verify token is set on a blocked user, attempt to send an e-mail
            if (!$this->appeal->user_verified && !$this->appeal->verify_token && isset($details['user'])) {
                VerifyBlockJob::dispatch($this->appeal);
            }
        }
    }
}
