<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'category',
        'type',
        'description',
        'created_by',
        'updated_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($setting) {
            $setting->created_by = Auth::id();
            $setting->updated_by = Auth::id();
        });

        static::updating(function ($setting) {
            $setting->updated_by = Auth::id();
        });

        static::saved(function ($setting) {
            cache()->forget("setting.value.{$setting->key}");
            cache()->forget("setting.bool.{$setting->key}");
            cache()->forget('settings.all.kv');
        });

        static::deleted(function ($setting) {
            cache()->forget("setting.value.{$setting->key}");
            cache()->forget("setting.bool.{$setting->key}");
        });
    }


    /* =========================
     * UNIVERSAL SETTING GETTERS
     * ========================= */

    public static function get(string $key, mixed $default = null): mixed
    {
        return cache()->remember(
            "setting.value.$key",
            now()->addMinutes(10),
            function () use ($key, $default) {
                $setting = static::where('key', $key)->first();
                if (!$setting) return $default;

                return match ($setting->type) {
                    'integer' => (int) $setting->value,
                    'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOL),
                    'json'    => json_decode($setting->value, true),
                    default   => $setting->value,
                };
            }
        );
    }

    public static function bool(string $key, bool $default = false): bool
    {
        return cache()->remember(
            "setting.bool.$key",
            now()->addMinutes(10),
            fn () => (bool) static::get($key, $default)
        );
    }

    public static function int(string $key, int $default = 0): int
    {
        return (int) static::get($key, $default);
    }

    public static function str(string $key, string $default = ''): string
    {
        return (string) static::get($key, $default);
    }

    /* =========================
     * RELATIONSHIPS
     * ========================= */

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function oauthEnabled(string $provider): bool
    {
        return filled(config("services.$provider.client_id"))
            && filled(config("services.$provider.client_secret"))
            && filled(config("services.$provider.redirect"));
    }
}
