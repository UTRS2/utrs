<?php

namespace App\Http\Requests\Admin\Bans;

use App\Ban;
use App\Http\Rules\IpOrCidrRule;
use App\Http\Rules\MaxCidrSizeRule;
use Carbon\Carbon;
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
            'expiry' => 'required|date_format:Y-m-d H:i:s',
        ];
    }

    protected function prepareForValidation()
    {
        $unixTimestamp = $this->has('expiry') && $this->treatAsDate($this->input('expiry')) ? strtotime($this->input('expiry')) : 0;

        $this->merge([
            'expiry' => Carbon::createFromTimestamp($unixTimestamp)->format('Y-m-d H:i:s'),
        ]);
    }

    public function withValidator(Validator $validator)
    {
        if ($this->has('ip') && $this->input('ip') == true) {
            $validator->addRules([
                'target' => [
                    new IpOrCidrRule,
                    new MaxCidrSizeRule(16, 16),
                ],
            ]);
        }

        if ($this->user()->can('oversight', Ban::class)) {
            $validator->addRules([
                'is_protected' => 'required|boolean',
            ]);
        }
    }

    private function treatAsDate($string)
    {
        return !empty($string) && $string !== 'indefinite';
    }
}
