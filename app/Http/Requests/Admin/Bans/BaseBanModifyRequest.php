<?php

namespace App\Http\Requests\Admin\Bans;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class BaseBanModifyRequest extends FormRequest
{
    /**
     * Get the target to apply permission checks for.
     * @return mixed
     */
    protected abstract function getPermissionCheckTarget();

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', $this->getPermissionCheckTarget());
    }

    /**
     * Prepare the form data to be ready for validation.
     */
    protected function prepareForValidation()
    {
        $unixTimestamp = $this->has('expiry') && $this->treatAsDate($this->input('expiry')) ? strtotime($this->input('expiry')) : 0;

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
        if ($this->user()->can('oversight', $this->ban)) {
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
