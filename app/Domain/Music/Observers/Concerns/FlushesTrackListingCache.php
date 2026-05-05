<?php

namespace Music\Observers\Concerns;

use Music\Cache\TrackListingCache;

trait FlushesTrackListingCache
{
    protected function flush(): void
    {
        TrackListingCache::flush();
    }
}
