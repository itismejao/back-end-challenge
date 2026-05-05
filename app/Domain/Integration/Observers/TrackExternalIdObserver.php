<?php

namespace App\Domain\Integration\Observers;

use App\Domain\Integration\Models\TrackExternalId;
use App\Domain\Music\Observers\Concerns\FlushesTrackListingCache;

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
