<?php

namespace Integration\Contracts;

use Integration\DTOs\TrackDTO;

interface MusicProviderInterface
{
    /** @param list<string> $markets */
    public function searchByIsrc(string $isrc, array $markets = []): ?TrackDTO;

    public function getProviderCode(): string;
}
