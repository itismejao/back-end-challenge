<?php

namespace Music\Observers;

use Music\Models\Album;
use Music\Observers\Concerns\FlushesTrackListingCache;

class AlbumObserver
{
    use FlushesTrackListingCache;

    public function updated(Album $album): void
    {
        $this->flush();
    }

    public function deleted(Album $album): void
    {
        $this->flush();
    }
}
