<?php

namespace App\Http\Rules;

use App\Appeal;
use App\Services\Facades\AccIntegration;
use Illuminate\Contracts\Validation\Rule;

class CheckAccTransferStatusRule implements Rule
{
    /** @var Appeal */
    private $appeal;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Appeal $appeal)
    {
        $this->appeal = $appeal;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $value !== Appeal::STATUS_REFER_ACC
            || AccIntegration::getTransferManager()->shouldAllowTransfer($this->appeal);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'This appeal can\'t be transferred to ACC.';
    }
}
