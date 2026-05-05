<?php

namespace Integration\Tests\Unit\DTOs;

use Integration\DTOs\ArtistDTO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ArtistDTOTest extends TestCase
{
    #[Test]
    public function it_creates_with_required_fields(): void
    {
        $dto = new ArtistDTO(name: 'Artist', externalId: 'ext123');

        $this->assertSame('Artist', $dto->name);
        $this->assertSame('ext123', $dto->externalId);
        $this->assertNull($dto->externalUrl);
    }

    #[Test]
    public function it_creates_with_external_url(): void
    {
        $dto = new ArtistDTO(name: 'Artist', externalId: 'ext123', externalUrl: 'http://example.com');

        $this->assertSame('http://example.com', $dto->externalUrl);
    }
}
