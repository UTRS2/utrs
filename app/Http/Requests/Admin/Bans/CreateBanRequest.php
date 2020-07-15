<?php

namespace App\Http\Requests\Admin\Bans;

use App\Ban;
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
            'ip' => 'required|boolean',
            'target' => 'required|max:128',
            'reason' => 'required|max:128',
            'expiry' => 'required|date_format:Y-m-d H:i:s',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function withValidator(Validator $validator)
    {
        parent::withValidator($validator);

        if ($this->has('ip') && $this->input('ip') == true) {
            $validator->addRules([
                'target' => [
                    new IpOrCidrRule,
                    new MaxCidrSizeRule(16, 16),
                ],
            ]);
        }
    }
}
