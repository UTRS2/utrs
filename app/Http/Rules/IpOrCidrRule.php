<?php

namespace App\Http\Rules;

use App\Utils\IPUtils;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class IpOrCidrRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return Str::contains($value, '/')
            ? IPUtils::isIpRange($value)
            : IPUtils::isIp($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute should be either an IP address or a CIDR range.';
    }
}
