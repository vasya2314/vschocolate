<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => 'required|string|current_password',
            'password' => ['required', 'string', 'confirmed', 'different:current_password', Password::min(8)],
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => 'текущий пароль',
            'password' => 'новый пароль',
        ];
    }
}
