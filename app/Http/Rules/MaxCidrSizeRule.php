<?php

namespace App\Http\Rules;

use App\Utils\IPUtils;
use Illuminate\Contracts\Validation\Rule;

class MaxCidrSizeRule implements Rule
{
    private $maxSizeIpv4;
    private $maxSizeIpv6;

    /**
     * Create a new rule instance.
     *
     * @param int $maxSizeIpv4
     * @param int $maxSizeIpv6
     */
    public function __construct(int $maxSizeIpv4, int $maxSizeIpv6)
    {
        $this->maxSizeIpv4 = $maxSizeIpv4;
        $this->maxSizeIpv6 = $maxSizeIpv6;
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
        if (!IPUtils::isIpRange($value)) {
            return true;
        }

        $size = (int) IPUtils::getRangeCidrSize($value);
        $maxSize = IPUtils::isIPv6($value) ? $this->maxSizeIpv6 : $this->maxSizeIpv4;
        return $size >= $maxSize;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'IP range :attribute is too large. Maximum allowed sizes are /' . $this->maxSizeIpv4
            . ' for IPv4 and /' . $this->maxSizeIpv6 . ' for IPv6.';
    }
}
