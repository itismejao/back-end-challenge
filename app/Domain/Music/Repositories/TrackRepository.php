<?php

namespace App\Domain\Music\Repositories;

use App\Domain\Integration\Models\TrackExternalId;
use App\Domain\Music\Contracts\TrackRepositoryInterface;
use App\Domain\Music\Models\Album;
use App\Domain\Music\Models\Track;
use Illuminate\Database\UniqueConstraintViolationException;

class TrackRepository implements TrackRepositoryInterface
{
    public function upsertWithExternalId(
        array $attributes,
        string $providerCode,
        string $externalId,
        ?string $externalUrl,
        Album $album,
        array $artists,
        array $marketCodes = [],
    ): Track {
        $existing = TrackExternalId::where('provider_code', $providerCode)
            ->where('external_id', $externalId)
            ->first();

        if ($existing) {
            $existing->track->update([
                'album_id' => $album->id,
                ...$attributes,
            ]);
            $existing->update([
                'external_url' => $externalUrl,
                'synced_at' => now(),
            ]);
            $this->syncArtists($existing->track, $artists);
            $this->syncMarkets($existing->track, $marketCodes);

            return $existing->track;
        }

        try {
            $track = Track::create([
                'album_id' => $album->id,
                ...$attributes,
            ]);

            $this->syncArtists($track, $artists);
            $this->syncMarkets($track, $marketCodes);

            $track->externalIds()->create([
                'provider_code' => $providerCode,
                'external_id' => $externalId,
                'external_url' => $externalUrl,
                'synced_at' => now(),
            ]);

            return $track;
        } catch (UniqueConstraintViolationException) {
            return $this->upsertWithExternalId($attributes, $providerCode, $externalId, $externalUrl, $album, $artists, $marketCodes);
        }
    }

    private function syncArtists(Track $track, array $artists): void
    {
        $pivotData = [];
        foreach ($artists as $position => $artist) {
            $pivotData[$artist->id] = ['position' => $position];
        }

        $track->artists()->sync($pivotData);
    }

    /** @param list<string> $marketCodes */
    private function syncMarkets(Track $track, array $marketCodes): void
    {
        $changes = $track->availableMarkets()->sync($marketCodes);

        if (array_filter($changes)) {
            $track->touch();
        }
    }
}
