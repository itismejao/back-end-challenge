<?php

namespace Music\Observers;

use Music\Models\Track;
use Music\Observers\Concerns\FlushesTrackListingCache;

class TrackObserver
{
    use FlushesTrackListingCache;

    public function created(Track $track): void
    {
        $this->flush();
    }

    public function updated(Track $track): void
    {
        $this->flush();
    }

    public function deleted(Track $track): void
    {
        $this->flush();
    }
}
