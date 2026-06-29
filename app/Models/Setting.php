<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    /**
     * Get one setting value, with optional fallback.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting:{$key}", 3600, function () use ($key, $default) {
            $row = static::where('key', $key)->value('value');
            return $row !== null ? $row : $default;
        });
    }

    /**
     * Upsert one setting and clear its cache entry.
     */
    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
        Cache::forget("setting:{$key}");
    }

    /**
     * Get all settings in a group, keyed by key.
     */
    public static function group(string $group): array
    {
        return Cache::remember("settings_group:{$group}", 3600, function () use ($group) {
            return static::where('group', $group)->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Clear cached group (call after bulk-saving a group).
     */
    public static function clearGroupCache(string $group): void
    {
        Cache::forget("settings_group:{$group}");
    }
}
