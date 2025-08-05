<?php

namespace App\Http\Requests\Client;

use App\Models\FromPlatform;
use App\Rules\InWhitelist;
use Illuminate\Foundation\Http\FormRequest;
use Propaganistas\LaravelPhone\PhoneNumber;

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
            'phone' => 'sometimes|string|max:40|phone:RU',
            'from_platform_id' => [
                'sometimes',
                'array',
                new InWhitelist(FromPlatform::all()
                    ->select('id')
                    ->pluck('id')->toArray()
                )
            ]
        ];
    }

    public function attributes(): array
    {
        return [
            'phone' => 'телефон',
            'from_platform_id' => 'ID платформы',
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
