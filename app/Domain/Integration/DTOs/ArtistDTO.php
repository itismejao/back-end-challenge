<?php

namespace App\Domain\Integration\DTOs;

final readonly class ArtistDTO
{
    public function __construct(
        public string $name,
        public string $externalId,
        public ?string $externalUrl = null,
    ) {}
}
