<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Preserves inbound links from the previous website by mapping retired paths
 * onto their replacements with a permanent redirect.
 */
class Redirect extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    private const CACHE_KEY = 'redirects.map';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * @return array<string, string>
     */
    public static function map(): array
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn (): array => static::query()->pluck('to_path', 'from_path')->all(),
        );
    }
}
