<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            try {
                $setting = Setting::where('key', $key)->first();
                return $setting ? $setting->value : $default;
            } catch (\Throwable $e) {
                return $default;
            }
        });
    }

    /**
     * Set/update a setting value by key.
     *
     * @param string $key
     * @param string|null $value
     * @param string $group
     * @return Setting
     */
    public static function set(string $key, ?string $value, string $group = 'general'): Setting
    {
        $setting = Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        Cache::forget("setting.{$key}");

        return $setting;
    }

    /**
     * Clear all settings cache.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        Setting::all()->each(function ($setting) {
            Cache::forget("setting.{$setting->key}");
        });
    }
}
