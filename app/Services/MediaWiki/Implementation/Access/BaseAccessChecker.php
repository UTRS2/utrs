<?php

namespace App\Services\MediaWiki\Implementation\Access;

use App\Services\MediaWiki\Api\MediaWikiApi;
use App\Services\MediaWiki\Api\WikiAccessChecker;
use Mediawiki\DataModel\User;

abstract class BaseAccessChecker implements WikiAccessChecker
{
    protected abstract function getApi(): MediaWikiApi;

    /**
     * {@inheritDoc}
     */
    public function getUserGroups(string $username): array
    {
        $api = $this->getApi();
        $user = $api
            ->getAddWikiServices()
            ->newUserGetter()
            ->getFromUsername($username);

        if ($user->getId() === 0) {
            // user does not exist
            return [];
        }

        if ($this->isBlocked($user, $api)) {
            // blocked users don't get to do anything
            return [];
        }

        $groups = array_intersect(
            $this->fetchGroups($user),
            $this->getGroupsToCheckWithoutUser());

        if (!empty($groups)) {
            // special handling for user, at some point we should just drop this bit
            // grant it to everyone who is in at least one supported group
            $groups[] = 'user';
        }

        return $groups;
    }

    /**
     * {@inheritDoc}
     */
    public final function getGroupsToCheck(): array
    {
        $groups = $this->getGroupsToCheckWithoutUser();
        $groups[] = 'user';
        return $groups;
    }

    /**
     * Check if the user is blocked on this wiki.
     * @param User $user
     * @param MediaWikiApi $api
     * @return bool
     */
    protected abstract function isBlocked(User $user, MediaWikiApi $api): bool;

    /**
     * Return the raw list of groups this user is in, before any filtering.
     * @param User $user
     * @return string[]
     */
    protected abstract function fetchGroups(User $user): array;

    /**
     * This is like {@link getGroupsToCheck()}, but it should not include
     * user, as that's handled in the base job.
     * @return string[]
     */
    protected abstract function getGroupsToCheckWithoutUser(): array;
}