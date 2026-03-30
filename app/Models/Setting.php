<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Setting Model
 *
 * Key-value configuration storage for application settings.
 * Provides static helper methods for easy get/set operations.
 */
#[Fillable([
    'key',
    'value',
    'description',
])]
class Setting extends Model
{
    /**
     * Default speed limit in km/h.
     *
     * Used when no speed_limit setting exists in database.
     */
    private const DEFAULT_SPEED_LIMIT = 60;

    /**
     * Get a setting value by key.
     *
     * Returns the value if found, otherwise returns the provided default.
     *
     * @param  string  $key  The setting key to retrieve
     * @param  mixed  $default  Default value if setting not found
     * @return mixed Setting value or default
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key.
     *
     * Creates a new setting if it doesn't exist, updates if it does.
     *
     * @param  string  $key  The setting key
     * @param  mixed  $value  The value to store
     * @param  string|null  $description  Optional description
     * @return Setting The created or updated setting
     */
    public static function set(string $key, mixed $value, ?string $description = null): Setting
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
            ]
        );
    }

    /**
     * Get the speed limit setting.
     *
     * Returns the configured speed limit in km/h.
     * Falls back to default if not configured.
     *
     * @return float Speed limit in km/h
     */
    public static function getSpeedLimit(): float
    {
        return (float) static::get('speed_limit', self::DEFAULT_SPEED_LIMIT);
    }
}
