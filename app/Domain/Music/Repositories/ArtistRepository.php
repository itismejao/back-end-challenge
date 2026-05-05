<?php

namespace App\Domain\Music\Repositories;

use App\Domain\Integration\Models\ArtistExternalId;
use App\Domain\Music\Contracts\ArtistRepositoryInterface;
use App\Domain\Music\Models\Artist;
use Illuminate\Database\UniqueConstraintViolationException;

class ArtistRepository implements ArtistRepositoryInterface
{
    public function upsertWithExternalId(
        string $name,
        string $providerCode,
        string $externalId,
        ?string $externalUrl = null,
    ): Artist {
        $existing = ArtistExternalId::where('provider_code', $providerCode)
            ->where('external_id', $externalId)
            ->first();

        if ($existing) {
            $existing->artist->update(['name' => $name]);
            $existing->update([
                'external_url' => $externalUrl,
                'synced_at' => now(),
            ]);

            return $existing->artist;
        }

        try {
            $artist = Artist::create(['name' => $name]);

            $artist->externalIds()->create([
                'provider_code' => $providerCode,
                'external_id' => $externalId,
                'external_url' => $externalUrl,
                'synced_at' => now(),
            ]);

            return $artist;
        } catch (UniqueConstraintViolationException) {
            return $this->upsertWithExternalId($name, $providerCode, $externalId, $externalUrl);
        }
    }
}
