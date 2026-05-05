<?php

namespace Integration\Tests\Unit\Services;

use Integration\Contracts\MusicProviderInterface;
use Integration\Services\MusicProviderFactory;
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
        $this->expectException(InvalidArgumentException::class);
        (new MusicProviderFactory())->make('deezer');
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
        $this->assertSame([], (new MusicProviderFactory())->availableProviders());
    }
}
