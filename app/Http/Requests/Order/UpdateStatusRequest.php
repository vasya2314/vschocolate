<?php

namespace App\Http\Requests\Order;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:' . implode(',', Order::getAllStatuses()),
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => 'статус',
        ];
    }
}
