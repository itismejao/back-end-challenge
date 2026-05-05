<?php

namespace App\Domain\Integration\Contracts;

use App\Domain\Integration\DTOs\TrackDTO;

interface MusicProviderInterface
{
    /** @param list<string> $markets */
    public function searchByIsrc(string $isrc, array $markets = []): ?TrackDTO;

    public function getProviderCode(): string;
}
