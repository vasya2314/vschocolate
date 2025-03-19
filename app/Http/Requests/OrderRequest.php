<?php

namespace App\Http\Requests;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderContent;
use App\Rules\EqualsAmount;
use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
            'date_issue' => 'required|date',
            'description' => 'required|string',
            'amount_total' => 'required|string',
            'amount_payed' => [
                'required',
                'string',
                new EqualsAmount($this->input('amount_total') ?? 0),
            ],
            'contents' => 'required|array',
            'contents.*' => 'required|in:' . implode(',', OrderContent::getAllContents()),
        ];

        if($this->route()->named('order.store'))
        {
            if($this->input('client_id') !== null)
            {
                $rules['client_id'] = 'required|exists:clients,id';
            } else {
                $rules = array_merge(
                    $rules,
                    [
                        'client_phone' => 'required|unique:clients,phone|phone:RU',
                        'client_name' => 'required|string',
                        'client_from' => 'required|in:' . implode(',', Client::getAllFromLabels()),
                        'client_comment' => 'nullable|string',
                    ]
                );
            }

            return $rules;
        }

        if($this->route()->named('order.update'))
        {
            $rules['status'] = 'required|in:' . implode(',', Order::getAllStatuses());

            return $rules;
        }

        return [];
    }
}
