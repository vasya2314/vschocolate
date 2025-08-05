<?php

namespace App\Http\Requests\FromPlatform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'name' => [
                'sometimes',
                'string',
                'max:30',
                Rule::unique('from_platforms', 'name')->ignore($this->fromPlatform->id),
            ]
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'название',
        ];
    }
}
