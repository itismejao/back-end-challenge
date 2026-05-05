<?php

namespace Tests\Unit\Services;

use App\Domain\Integration\Contracts\MusicProviderInterface;
use App\Domain\Integration\Services\MusicProviderFactory;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MusicProviderFactoryTest extends TestCase
{
    #[Test]
    public function it_registers_and_resolves_a_provider(): void
    {
        $factory = new MusicProviderFactory();
        $provider = $this->createMock(MusicProviderInterface::class);

        $factory->register('spotify', $provider);

        $this->assertSame($provider, $factory->make('spotify'));
    }

    #[Test]
    public function it_throws_for_unregistered_provider(): void
    {
        $factory = new MusicProviderFactory();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Music provider [deezer] is not registered.');

        $factory->make('deezer');
    }

    #[Test]
    public function it_lists_available_providers(): void
    {
        $factory = new MusicProviderFactory();
        $factory->register('spotify', $this->createMock(MusicProviderInterface::class));
        $factory->register('deezer', $this->createMock(MusicProviderInterface::class));

        $this->assertSame(['spotify', 'deezer'], $factory->availableProviders());
    }

    #[Test]
    public function it_returns_empty_when_no_providers_registered(): void
    {
        $factory = new MusicProviderFactory();

        $this->assertSame([], $factory->availableProviders());
    }
}
