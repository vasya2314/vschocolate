<?php

namespace App\Http\Requests\TgSettings;

use App\Constants\TgStatus;
use Illuminate\Foundation\Http\FormRequest;

class TgSettingsRequest extends FormRequest
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
            'token' => 'sometimes|required|string',
            'chat_id' => 'sometimes|required|string',
            'tg_status' => 'sometimes|required|string|in:' . implode(',', TgStatus::values()),
        ];
    }

    public function attributes(): array
    {
        return [
            'token' => 'токен',
            'chat_id' => 'ID чата'
        ];
    }
}
