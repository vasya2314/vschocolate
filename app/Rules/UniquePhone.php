<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniquePhone implements ValidationRule
{
    protected ?int $ignoreId;

    public function __construct(
        protected string $table,
        protected string $column,
        int $ignoreId = null
    ) {
        $this->ignoreId = $ignoreId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $phone = makePhoneNormalized($value);

        $query = DB::table($this->table)->where($this->column, $phone);

        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        $exists = $query->exists();

        if ($exists) {
            $fail('Значение ' . $value . ' уже существует в базе');
        }
    }
}
