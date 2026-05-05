<?php

namespace App\Domain\Integration\Contracts;

interface MusicProviderFactoryInterface
{
    public function make(string $providerCode): MusicProviderInterface;

    /** @return list<string> */
    public function availableProviders(): array;
}
