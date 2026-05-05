<?php

namespace Music\Cache;

use Illuminate\Support\Facades\Cache;

class TrackListingCache
{
    public const TAG = 'tracks-listing';
    public const TTL = 600;

    public static function remember(string $key, callable $callback): mixed
    {
        return Cache::tags(self::TAG)->remember($key, self::TTL, $callback);
    }

    public static function flush(): void
    {
        Cache::tags(self::TAG)->flush();
    }
}
