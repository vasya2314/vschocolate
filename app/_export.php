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
    /** Целые рубли → целые копейки */
    function rubToKop(int $amount): int
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

if (!function_exists('kopToRubShort'))
{
    /**
     * Короткая запись суммы для осей графиков: «1,2 млн ₽», «350 тыс ₽», «900 ₽».
     */
    function kopToRubShort(int|float|string $amount): string
    {
        $rub = (float) $amount / 100;
        $abs = abs($rub);

        if ($abs >= 1_000_000) return rtrim(rtrim(number_format($rub / 1_000_000, 1, ',', ' '), '0'), ',') . ' млн ₽';
        if ($abs >= 1_000) return number_format($rub / 1_000, 0, ',', ' ') . ' тыс ₽';

        return number_format($rub, 0, ',', ' ') . ' ₽';
    }
}
