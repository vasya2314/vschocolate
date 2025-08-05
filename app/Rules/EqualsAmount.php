<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EqualsAmount implements ValidationRule
{
    public function __construct(
        protected mixed $amount
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if($value > $this->amount)
        {
            $fail('Значение не может быть больше, чем ' . kopToRub($this->amount));
        }
    }
}
