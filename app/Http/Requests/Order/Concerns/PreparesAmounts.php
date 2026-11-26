<?php

namespace App\Http\Requests\Order\Concerns;

/**
 * Суммы из формы приходят в рублях строкой с пробелами («12 345»).
 * Перед валидацией переводим их в целые копейки. Если значение не целое число рублей,
 * оставляем как есть — его отклонит правило integer.
 */
trait PreparesAmounts
{
    protected function prepareAmounts(string ...$fields): void
    {
        foreach ($fields as $field) {
            if (!$this->has($field)) continue;

            $raw = str_replace(' ', '', (string) $this->input($field));
            $rub = filter_var($raw, FILTER_VALIDATE_INT);

            $this->merge([$field => $rub === false ? $raw : rubToKop($rub)]);
        }
    }
}
