<?php

namespace App\Constants;

class TgStatus
{
    public const ENABLE = 'ENABLE';
    public const DISABLE = 'DISABLE';

    public static function values(): array
    {
        return [
            self::ENABLE,
            self::DISABLE,
        ];
    }

    public static function labels(): array
    {
        return [
            self::ENABLE => 'Включен',
            self::DISABLE => 'Выключен',
        ];
    }
}
