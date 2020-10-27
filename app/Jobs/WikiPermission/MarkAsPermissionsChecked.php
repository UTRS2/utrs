<?php

namespace App\Jobs\WikiPermission;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * This could just as well be a closure when we upgrade to Laravel 7
 * but for now it has to be a separate job class
 *
 * https://github.com/laravel/framework/pull/31488
 */
class MarkAsPermissionsChecked implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->user->update([
            'last_permission_check_at' => now(),
        ]);
    }
}
