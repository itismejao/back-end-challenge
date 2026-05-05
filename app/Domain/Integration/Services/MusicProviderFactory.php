<?php

namespace App\Domain\Integration\Services;

use App\Domain\Integration\Contracts\MusicProviderFactoryInterface;
use App\Domain\Integration\Contracts\MusicProviderInterface;
use InvalidArgumentException;

class MusicProviderFactory implements MusicProviderFactoryInterface
{
    /** @var array<string, MusicProviderInterface> */
    private array $providers = [];

    public function register(string $providerCode, MusicProviderInterface $provider): void
    {
        $this->providers[$providerCode] = $provider;
    }

    public function make(string $providerCode): MusicProviderInterface
    {
        if (! isset($this->providers[$providerCode])) {
            throw new InvalidArgumentException("Music provider [{$providerCode}] is not registered.");
        }

        return $this->providers[$providerCode];
    }

    public function availableProviders(): array
    {
        return array_keys($this->providers);
    }
}
