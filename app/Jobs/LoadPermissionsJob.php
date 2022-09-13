<?php

namespace App\Jobs;

use App\Models\Permission;
use App\Models\User;
use App\Services\MediaWiki\Api\MediaWikiRepository;
use App\Utils\Logging\SystemLogContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Update permissions of a user on all wikis
 */
class LoadPermissionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var User */
    private $user;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user->withoutRelations();
    }

    /**
     * Execute the job.
     *
     * @param MediaWikiRepository $repository
     * @return void
     */
    public function handle(MediaWikiRepository $repository)
    {
        $changes = [];
        foreach ($repository->getSupportedTargets() as $wiki) {
            $checker = $repository->getWikiAccessChecker($wiki);
            $groups = $checker->getUserGroups($this->user->username);

            $permObject = Permission::firstOrNew([
                'user_id' => $this->user->id,
                'wiki' => $wiki,
            ]);

            $changedGroups = [];

            if (!$permObject->exists && empty($groups)) {
                // no need to create a permission object; no rights previously and will have none in the future
                continue;
            }

            foreach ($checker->getGroupsToCheck() as $group) {
                $oldValue = $permObject->exists && $permObject->getAttribute($group);
                $newValue = in_array($group, $groups);
                $permObject->setAttribute($group, $newValue);

                if ($oldValue != $newValue) {
                    $changedGroups[] = ($newValue ? '+' : '-') . $group;
                }
            }

            $permObject->save();

            if (!empty($changedGroups)) {
                $changes[] = $wiki . ': ' .implode(', ', $changedGroups);
            }
        }

        if (!empty($changes)) {
            $this->user->addLog(
                new SystemLogContext(),
                'modified user - ' . implode(', ', $changes),
                'automatic permission check'
            );
        }

        $this->user->update([
            'last_permission_check_at' => now(),
        ]);
    }

    public function displayName(): string
    {
        return get_class($this) . ': user ' . $this->user->username . ' (#' . $this->user->id . ')';
    }
}
