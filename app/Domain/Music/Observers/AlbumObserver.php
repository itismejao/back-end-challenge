<?php

namespace App\Domain\Music\Observers;

use App\Domain\Music\Models\Album;
use App\Domain\Music\Observers\Concerns\FlushesTrackListingCache;

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
