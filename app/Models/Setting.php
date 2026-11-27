<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

/**
 * Постоянные настройки приложения (ключ — значение).
 * Хранятся в отдельной таблице, а не в кэше: cache:clear / optimize:clear их не стирают.
 * Секреты (например, токен бота) пишутся зашифрованными ключом APP_KEY: $encrypted = true.
 */
class Setting extends Model
{
    protected $primaryKey = 'key';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, mixed $default = null, bool $encrypted = false): mixed
    {
        $value = static::query()->whereKey($key)->value('value');

        if ($value === null) {
            return $default;
        }

        if (!$encrypted) {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            // Например, сменился APP_KEY: значение не прочитать, его нужно ввести заново
            Log::warning("Не удалось расшифровать настройку «{$key}»: {$e->getMessage()}");

            return $default;
        }
    }

    public static function setValue(string $key, mixed $value, bool $encrypted = false): bool
    {
        if ($encrypted && $value !== null) {
            $value = Crypt::encryptString((string) $value);
        }

        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);

        return true;
    }
}
