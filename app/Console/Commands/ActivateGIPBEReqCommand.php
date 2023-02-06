<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\Scheduled\PostGlobalIPBEReqJob;

class ActivateGIPBEReqCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'utrs-jobs:activateGIPBEReq';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Posts Global IP Block Exemption requests to meta';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        PostGlobalIPBEReqJob::dispatchNow();
        return 0;
    }
}
