<?php

namespace Music\Observers;

use Music\Models\Artist;
use Music\Observers\Concerns\FlushesTrackListingCache;

class ArtistObserver
{
    use FlushesTrackListingCache;

    public function updated(Artist $artist): void
    {
        $this->flush();
    }

    public function deleted(Artist $artist): void
    {
        $this->flush();
    }
}
