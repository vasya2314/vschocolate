<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Propaganistas\LaravelPhone\PhoneNumber;

class UpdateRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:50',
            'phone' => [
                'sometimes',
                'required',
                'string',
                'max:40',
                'phone:RU',
                Rule::unique('clients', 'phone')->ignore($this->client->id),
            ],
            'from_platform_id' => 'sometimes|required|integer|exists:from_platforms,id',
            'comment' => 'nullable|string|max:1000',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'название',
            'phone' => 'телефон',
            'from_platform_id' => 'ID платформы',
            'comment' => 'комментарий',
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
