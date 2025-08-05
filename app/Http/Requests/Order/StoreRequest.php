<?php

namespace App\Http\Requests\Order;

use App\Models\Order;
use App\Models\Product;
use App\Rules\EqualsAmount;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
        $rules = [
            'status' => 'required|in:' . implode(',', Order::getAllStatuses()),
            'date_issue' => 'required|date',
            'description' => 'required|string',
            'amount_total' => 'required|numeric',
            'amount_payed' => [
                'required',
                'numeric',
                new EqualsAmount($this->input('amount_total') ?? 0),
            ],
            'product_ids' => [
                'required',
                'array',
                function (string $attribute, array $value, \Closure $fail) {
                    $res = Product::whereIn('id', $value)->pluck('id')->toArray();

                    $diff = array_diff($value, $res);

                    if(!empty($diff)) $fail('Значения нет в списке разрешенных');
                },
            ]
        ];

        if ($this->input('client.id') !== null) {
            $rules['client.id'] = 'required|exists:clients,id';
        } else {
            $clientRules = (new \App\Http\Requests\Client\StoreRequest())->rules();

            foreach ($clientRules as $key => $value)
            {
                $rules["client.$key"] = $value;
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'date_issue' => 'дата выдачи',
            'description' => 'описание',
            'amount_total' => 'общая сумма',
            'amount_payed' => 'оплаченная сумма',
            'status' => 'статус',
            'product_ids' => 'товары',
        ];
    }

    protected function prepareForValidation(): void
    {
        if($this->has('amount_total'))
        {
            $this->merge(
                [
                    'amount_total' => rubToKop(str_replace(' ', '', $this->input('amount_total'))),
                ]
            );
        }

        if($this->has('amount_payed'))
        {
            $this->merge(
                [
                    'amount_payed' => rubToKop(str_replace(' ', '', $this->input('amount_payed'))),
                ]
            );
        }
    }
}
