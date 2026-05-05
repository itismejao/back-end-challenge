<?php

namespace Music\Contracts;

use Music\Models\Album;
use Music\Models\Track;

interface TrackRepositoryInterface
{
    /**
     * @param array{
     *     isrc: string,
     *     name: string,
     *     duration_ms: int,
     *     explicit: bool,
     *     disc_number: int,
     *     track_number: int,
     *     availability_mode: string,
     * } $attributes
     * @param list<\App\Domain\Music\Models\Artist> $artists
     * @param list<string> $marketCodes
     */
    public function upsertWithExternalId(
        array $attributes,
        string $providerCode,
        string $externalId,
        ?string $externalUrl,
        Album $album,
        array $artists,
        array $marketCodes = [],
    ): Track;
}
