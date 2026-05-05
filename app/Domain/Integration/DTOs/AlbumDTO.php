<?php

namespace App\Domain\Integration\DTOs;

final readonly class AlbumDTO
{
    /**
     * @param list<ArtistDTO> $artists
     * @param list<array{url: string, width: int, height: int}>|null $images
     */
    public function __construct(
        public string $name,
        public string $albumType,
        public ?string $releaseDate,
        public string $releaseDatePrecision,
        public int $totalTracks,
        public ?array $images,
        public string $externalId,
        public ?string $externalUrl,
        public array $artists = [],
    ) {}
}
