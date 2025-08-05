<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Propaganistas\LaravelPhone\PhoneNumber;

class FindRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => 'required|string|max:40|phone:RU',
        ];
    }

    public function attributes(): array
    {
        return [
            'phone' => 'телефон',
        ];
    }

    protected function prepareForValidation(): void
    {
        if($this->has('phone'))
        {
            $this->merge(
                [
                    'phone' => (string) new PhoneNumber($this->input('phone'), 'RU')
                ]
            );
        }
    }
}
