<?php

namespace App\Http\Requests\Admin\Bans;

use App\Http\Rules\FailedRule;
use App\Models\Ban;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class BaseBanModifyRequest extends FormRequest
{
    /**
     * Prepare the form data to be ready for validation.
     */
    protected function prepareForValidation()
    {
        $unixTimestamp = $this->has('expiry') && $this->treatAsDate($this->input('expiry'))
            ? strtotime($this->input('expiry'))
            : 0;

        $this->merge([
            'expiry' => Carbon::createFromTimestamp($unixTimestamp)->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Modify the validator rules dynamically based on the form data.
     *
     * @param Validator $validator
     */
    public function withValidator(Validator $validator)
    {
        $permCheckTarget = $this->ban ? $this->ban : [Ban::class, $this->input('wiki_id')];

        if ($this->user()->can('oversight', $permCheckTarget)) {
            $validator->addRules([
                'is_protected' => 'required|boolean',
            ]);
        } elseif ($this->input('is_protected')) {
            $validator->addRules([
                'is_protected' => [
                    new FailedRule("You can't oversight this ban.")
                ]
            ]);
        }
    }

    private function treatAsDate($string)
    {
        return !empty($string) && $string !== 'indefinite';
    }
}
