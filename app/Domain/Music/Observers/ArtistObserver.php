<?php

namespace App\Domain\Music\Observers;

use App\Domain\Music\Models\Artist;
use App\Domain\Music\Observers\Concerns\FlushesTrackListingCache;

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
