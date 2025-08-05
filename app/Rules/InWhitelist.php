<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InWhitelist implements ValidationRule
{
    public function __construct(
        protected array $whitelist
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(!is_array($value))
        {
            $names = explode(',', $value);
        } else {
            $names = $value;
        }

        if(!empty($names))
        {
            foreach($names as $name)
            {
                if(!in_array($name, $this->whitelist))
                {
                    $fail('Значения нет в списке разрешенных');
                }
            }
        }
    }
}
