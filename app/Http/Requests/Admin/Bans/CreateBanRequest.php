<?php

namespace App\Http\Requests\Admin\Bans;

use App\Models\Ban;
use App\Http\Rules\IpOrCidrRule;
use App\Http\Rules\MaxCidrSizeRule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreateBanRequest extends BaseBanModifyRequest
{
    /**
     * {@inheritDoc}
     */
    protected function getPermissionCheckTarget()
    {
        return Ban::class;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'target' => [
                'required',
                'max:128',
                new MaxCidrSizeRule(16, 16),
            ],
            'reason' => 'required|max:128',
            'expiry' => 'required|date_format:Y-m-d H:i:s',
        ];
    }
}
