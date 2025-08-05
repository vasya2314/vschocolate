<?php

namespace App\Http\Requests\Order;

use App\Models\Order;
use App\Models\Product;
use App\Rules\InWhitelist;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
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
            'statuses' => [
                'sometimes',
                'array',
                new InWhitelist(Order::getAllStatuses())
            ],
            'products' => [
                'sometimes',
                'array',
                new InWhitelist(Product::all()
                    ->select('id')
                    ->pluck('id')->toArray()
                )
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => 'статус',
            'products' => 'товары',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->replace(
            collect($this->all())
                ->filter(function ($value) {
                    return $value !== null && $value !== '';
                })
                ->toArray()
        );
    }
}
