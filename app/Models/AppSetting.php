<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("app_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, mixed $value): void
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget("app_setting_{$key}");
    }

    /**
     * Cast value based on type.
     */
    protected static function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Get all settings grouped by group.
     */
    public static function getAllGrouped(): array
    {
        return self::all()->groupBy('group')->map(function ($settings) {
            return $settings->mapWithKeys(function ($setting) {
                return [$setting->key => self::castValue($setting->value, $setting->type)];
            });
        })->toArray();
    }
}
