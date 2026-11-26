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
            'date_from' => 'sometimes|date_format:Y-m-d',
            'date_to' => array_filter([
                'sometimes',
                'date_format:Y-m-d',
                $this->filled('date_from') ? 'after_or_equal:date_from' : null,
            ]),
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => 'статус',
            'products' => 'товары',
            'date_from' => 'дата выдачи с',
            'date_to' => 'дата выдачи по',
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
