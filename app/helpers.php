<?php

use Propaganistas\LaravelPhone\PhoneNumber;

if (!function_exists('makePhoneNormalized'))
{
    function makePhoneNormalized(?string $rawPhone): string
    {
        try {
            $phone = new PhoneNumber($rawPhone, 'RU');

            return str_replace('+', '', $phone->formatE164());
        } catch (Exception $e) {
            return '';
        }
    }
}

if (!function_exists('toDatabaseFormantPhone'))
{
    function toDatabaseFormantPhone(string $rawPhone): string
    {
        $phone = preg_replace('/\D/', '', $rawPhone);

        if (str_starts_with($phone, '8')) $phone = '7' . substr($phone, 1);

        return $phone;
    }
}

if (!function_exists('kopToRub'))
{
    function kopToRub(int|string $amount): float
    {
        return round(((int)str_replace(' ', '', $amount) / 100), 2);
    }
}

if (!function_exists('rubToKop'))
{
    function rubToKop(float|string $amount): int
    {
        return (int)((float)str_replace(' ', '', $amount) * 100);
    }
}

if (!function_exists('kopToRubView'))
{
    function kopToRubView(int|string $amount): string
    {
        return number_format(kopToRub($amount), 0, ',', ' ') . ' ₽';
    }
}
