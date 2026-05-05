<?php

namespace App\Domain\Integration\DTOs;

final readonly class TrackDTO
{
    /**
     * @param list<ArtistDTO> $artists
     * @param list<string> $availableMarkets
     */
    public function __construct(
        public string $name,
        public string $isrc,
        public int $durationMs,
        public bool $explicit,
        public int $discNumber,
        public int $trackNumber,
        public string $externalId,
        public ?string $externalUrl,
        public AlbumDTO $album,
        public array $artists = [],
        public array $availableMarkets = [],
    ) {}
}
