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

if (!defined('NBSP'))
{
    /** Неразрывный пробел: сумма и знак рубля не разрываются переносом строки */
    define('NBSP', "\u{00A0}");
}

if (!function_exists('kopToRubView'))
{
    /**
     * «12 345 ₽» — разряды и знак рубля разделены неразрывными пробелами.
     */
    function kopToRubView(int|string $amount): string
    {
        return number_format(kopToRub($amount), 0, ',', NBSP) . NBSP . '₽';
    }
}

if (!function_exists('kopToRubShort'))
{
    /**
     * Короткая запись суммы для осей графиков: «1,2 млн ₽», «350 тыс ₽», «900 ₽» (с неразрывными пробелами).
     */
    function kopToRubShort(int|float|string $amount): string
    {
        $rub = (float) $amount / 100;
        $abs = abs($rub);

        if ($abs >= 1_000_000) return rtrim(rtrim(number_format($rub / 1_000_000, 1, ',', NBSP), '0'), ',') . NBSP . 'млн' . NBSP . '₽';
        if ($abs >= 1_000) return number_format($rub / 1_000, 0, ',', NBSP) . NBSP . 'тыс' . NBSP . '₽';

        return number_format($rub, 0, ',', NBSP) . NBSP . '₽';
    }
}
