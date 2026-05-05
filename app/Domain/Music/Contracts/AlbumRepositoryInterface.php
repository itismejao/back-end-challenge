<?php

namespace App\Domain\Music\Contracts;

use App\Domain\Music\Models\Album;

interface AlbumRepositoryInterface
{
    /**
     * @param array{
     *     name: string,
     *     album_type: string,
     *     release_date: ?string,
     *     release_date_precision: string,
     *     total_tracks: int,
     *     images: ?array,
     * } $attributes
     * @param list<\App\Domain\Music\Models\Artist> $artists
     */
    public function upsertWithExternalId(
        array $attributes,
        string $providerCode,
        string $externalId,
        ?string $externalUrl,
        array $artists,
    ): Album;
}
