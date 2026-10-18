<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * In-memory cache for fast lookups during request lifecycle
     */
    protected static array $cache = [];

    /**
     * Get a setting value with optional default
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, static::$cache)) {
            return static::$cache[$key] ?? $default;
        }

        try {
            $record = static::where('key', $key)->first();
            if ($record && $record->value !== null) {
                static::$cache[$key] = $record->value;
                return $record->value;
            }
        } catch (\Throwable) {
            // In case table does not exist yet (e.g. during fresh migrations)
            return $default;
        }

        return $default;
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value !== null ? (string) $value : null, 'group' => $group]
        );

        static::$cache[$key] = $value !== null ? (string) $value : null;
    }

    /**
     * Clear setting in-memory cache
     */
    public static function clearCache(): void
    {
        static::$cache = [];
    }
}
