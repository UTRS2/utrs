<?php

namespace App\Http\Requests\Admin\Bans;

use App\Models\Ban;
use App\Http\Rules\MaxCidrSizeRule;
use App\Models\Wiki;

class CreateBanRequest extends BaseBanModifyRequest
{
    public function authorize()
    {
        $wiki = ($this->has('wiki_id') && !$this->isEmptyString('wiki_id'))
            ? Wiki::findOrFail($this->input('wiki_id'))
            : null;

        return $this->user()->can('create', [Ban::class, $wiki]);
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
            'wiki_id' => 'nullable|exists:wikis,id'
        ];
    }
}
