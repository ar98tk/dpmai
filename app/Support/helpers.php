<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

if (! function_exists('getSetting')) {
    function getSetting(string $key, mixed $default = null): mixed
    {
        try {
            if (! Schema::hasTable('settings')) {
                return $default;
            }

            return Cache::remember(
                'setting:'.$key,
                now()->addMinutes(10),
                fn () => Setting::query()->where('key', $key)->value('value') ?? $default
            );
        } catch (\Throwable $exception) {
            return $default;
        }
    }
}
