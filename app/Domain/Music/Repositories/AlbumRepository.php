<?php

namespace Music\Repositories;

use Integration\Models\AlbumExternalId;
use Music\Contracts\AlbumRepositoryInterface;
use Music\Models\Album;
use Illuminate\Database\UniqueConstraintViolationException;

class AlbumRepository implements AlbumRepositoryInterface
{
    public function upsertWithExternalId(
        array $attributes,
        string $providerCode,
        string $externalId,
        ?string $externalUrl,
        array $artists,
    ): Album {
        $existing = AlbumExternalId::where('provider_code', $providerCode)
            ->where('external_id', $externalId)
            ->first();

        if ($existing) {
            $existing->album->update($attributes);
            $existing->update([
                'external_url' => $externalUrl,
                'synced_at' => now(),
            ]);
            $this->syncArtists($existing->album, $artists);

            return $existing->album;
        }

        try {
            $album = Album::create($attributes);

            $this->syncArtists($album, $artists);

            $album->externalIds()->create([
                'provider_code' => $providerCode,
                'external_id' => $externalId,
                'external_url' => $externalUrl,
                'synced_at' => now(),
            ]);

            return $album;
        } catch (UniqueConstraintViolationException) {
            return $this->upsertWithExternalId($attributes, $providerCode, $externalId, $externalUrl, $artists);
        }
    }

    private function syncArtists(Album $album, array $artists): void
    {
        $pivotData = [];
        foreach ($artists as $position => $artist) {
            $pivotData[$artist->id] = ['position' => $position];
        }

        $album->artists()->sync($pivotData);
    }
}
