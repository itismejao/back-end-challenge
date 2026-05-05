<?php

namespace App\Domain\Music\Observers\Concerns;

use App\Domain\Music\Cache\TrackListingCache;

trait FlushesTrackListingCache
{
    protected function flush(): void
    {
        TrackListingCache::flush();
    }
}
