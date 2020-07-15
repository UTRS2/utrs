<?php

namespace App\Services\MediaWiki\Api;

use App\Services\MediaWiki\Api\Data\Block;

/**
 * Provides extra functions that addwiki/mediawiki-api does not provide itself
 */
interface MediaWikiExtras
{
    /**
     * Checks if the specific user can be emailed
     *
     * @param  string $username username to be searched
     * @return boolean true if the user can be emailed
     */
    public function canEmail(string $username): bool;

    /**
     * Sends an email to the specifid wiki user
     *
     * @param  string $username username to send email to
     * @param  string $title subject line for the email
     * @param  string $content content of the email
     * @return boolean if email was sent, false otherwise
     */
    public function sendEmail(string $username, string $title, string $content): bool;

    /**
     * Retrieves information for blocks for the given user
     *
     * @param  string      $target Username to search blocks for
     * @param  int|null    $appealId ID of the appeal being queried (for logging)
     * @param  string|null $searchKey set the key to search blocks from mediawiki api (only 3 really exist: bkusers, bkip, bkids)
     * @return Block|null the block information that comes up, null if user is not blocked
     */
    public function getBlockInfo(string $target, int $appealId = -1, string $searchKey = null): ?Block;

    /**
     * Retrieves information for global (b)locks for the given user. May malfunction if current wiki is not the global wiki.
     *
     * @param  string      $target Username to search blocks for
     * @param  int|null    $appealId ID of the appeal being queried (for logging)
     * @return Block|null the block information that comes up, null if user is not (b)locked
     */
    public function getGlobalBlockInfo(string $target, int $appealId = -1): ?Block;
}
