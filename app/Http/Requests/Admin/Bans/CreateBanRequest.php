<?php

namespace App\Http\Requests\Admin\Bans;

use App\Ban;
use App\Http\Rules\IpOrCidrRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreateBanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', Ban::class);
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
            'expiry' => 'required|date',
        ];
    }

    public function withValidator(Validator $validator)
    {
        if ($this->has('ip') && $this->input('ip') == true) {
            $validator->addRules([
                'target' => [new IpOrCidrRule],
            ]);
        }

        if ($this->user()->can('oversight', Ban::class)) {
            $validator->addRules([
                'is_protected' => 'required|boolean',
            ]);
        }
    }

}
