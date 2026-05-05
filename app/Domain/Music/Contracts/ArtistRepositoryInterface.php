<?php

namespace Music\Contracts;

use Music\Models\Artist;

interface ArtistRepositoryInterface
{
    public function upsertWithExternalId(
        string $name,
        string $providerCode,
        string $externalId,
        ?string $externalUrl = null,
    ): Artist;
}
