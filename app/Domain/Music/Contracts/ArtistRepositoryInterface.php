<?php

namespace App\Domain\Music\Contracts;

use App\Domain\Music\Models\Artist;

interface ArtistRepositoryInterface
{
    public function upsertWithExternalId(
        string $name,
        string $providerCode,
        string $externalId,
        ?string $externalUrl = null,
    ): Artist;
}
