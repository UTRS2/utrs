<?php

namespace App\Services\Acc\Api;

use App\Appeal;

/**
 * Interface handling data transfers to ACC
 */
interface AccTransferManager
{
    /**
     * Checks if the specified appeal can be deferred to ACC at all.
     *
     * @param Appeal $appeal appeal to check
     * @return bool true if the specified appeal can be moved to ACC, false otherwise
     */
    public function shouldAllowTransfer(Appeal $appeal): bool;

    /**
     * Checks if the specified appeal is required to be transferred to ACC. This will always be false if
     * {@link shouldAllowTransfer} is false for the same appeal.
     *
     * @param Appeal $appeal appeal to check
     * @return bool true if the specified appeal can't be appealed on the UTRS side and needs to be moved to ACC, false otherwise
     */
    public function shouldRequireTransfer(Appeal $appeal): bool;

}
