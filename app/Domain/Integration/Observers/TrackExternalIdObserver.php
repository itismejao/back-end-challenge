<?php

namespace Integration\Observers;

use Integration\Models\TrackExternalId;
use Music\Observers\Concerns\FlushesTrackListingCache;

class TrackExternalIdObserver
{
    use FlushesTrackListingCache;

    public function created(TrackExternalId $externalId): void
    {
        $this->flush();
    }

    public function updated(TrackExternalId $externalId): void
    {
        $this->flush();
    }

    public function deleted(TrackExternalId $externalId): void
    {
        $this->flush();
    }
}
