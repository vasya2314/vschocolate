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
    function rubToKop(float $amount): int|float
    {
        return $amount * 100;
    }
}

if (!function_exists('kopToRubView'))
{
    function kopToRubView(int|string $amount): string
    {
        return number_format(kopToRub($amount), 0, ',', ' ') . ' ₽';
    }
}
