<?php

namespace App\Http\Requests\Admin\Bans;

use App\Ban;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * laravel magic, for ide autocompletion:
 * @property Ban ban
 */
class UpdateBanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', $this->ban);
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

    public function withValidator(Validator $validator)
    {
        if ($this->user()->can('oversight', Ban::class)) {
            $validator->addRules([
                'is_protected' => 'required|boolean',
            ]);
        }
    }

    protected function prepareForValidation()
    {
        $unixTimestamp = $this->has('expiry') && $this->treatAsDate($this->input('expiry')) ? strtotime($this->input('expiry')) : 0;

        $this->merge([
            'expiry' => Carbon::createFromTimestamp($unixTimestamp)->format('Y-m-d H:i:s'),
        ]);
    }

    private function treatAsDate($string)
    {
        return !empty($string) && $string !== 'indefinite';
    }
}
