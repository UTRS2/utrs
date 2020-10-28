<?php

namespace App\Http\Requests\Admin\Bans;

use App\Models\Ban;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * laravel magic, for ide autocompletion:
 * @property Ban ban
 */
class UpdateBanRequest extends BaseBanModifyRequest
{
    /**
     * {@inheritDoc}
     */
    protected function getPermissionCheckTarget()
    {
        return $this->ban;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'reason'    => 'nullable|max:128',
            'expiry'    => 'nullable|date_format:Y-m-d H:i:s',
            'is_active' => 'nullable|boolean',
        ];
    }
}
