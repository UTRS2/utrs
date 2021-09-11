<?php

namespace App\Http\Rules;

use App\Models\Appeal;
use Illuminate\Contracts\Validation\Rule;

class PermittedStatusChange implements Rule
{
    /** @var Appeal */
    private $appeal;

    /**
     * Create a new rule instance.
     *
     * @param Appeal $appeal
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
    public function passes($attribute, $value): bool
    {
        return $this->appeal->isValidStatusChange($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The new value for :attribute is not permitted for this appeal.';
    }
}