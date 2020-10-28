<?php

namespace App\Http\Rules;

use Illuminate\Contracts\Validation\Rule;

class SecretEqualsRule implements Rule
{
    private $correct;

    /**
     * Create a new rule instance.
     *
     * @param string $correct
     */
    public function __construct(string $correct)
    {
        $this->correct = $correct;
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
        return $value === $this->correct;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute was wrong.';
    }
}
