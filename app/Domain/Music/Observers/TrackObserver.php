<?php

namespace App\Domain\Music\Observers;

use App\Domain\Music\Models\Track;
use App\Domain\Music\Observers\Concerns\FlushesTrackListingCache;

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
