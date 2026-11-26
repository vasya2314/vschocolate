<?php

if (!function_exists('kopToRub'))
{
    function kopToRub(int $amount): float
    {
        return round(($amount / 100), 2);
    }
}

if (!function_exists('rubToKop'))
{
    /**
     * Рубли → целые копейки. Нечисловое значение возвращается как есть, чтобы его отклонила валидация.
     */
    function rubToKop(float|int|string $amount): int|string
    {
        if (!is_numeric($amount)) return $amount;

        return (int) round((float) $amount * 100);
    }
}

if (!function_exists('kopToRubView'))
{
    function kopToRubView(int|string $amount): string
    {
        return number_format(kopToRub($amount), 0, ',', ' ') . ' ₽';
    }
}
