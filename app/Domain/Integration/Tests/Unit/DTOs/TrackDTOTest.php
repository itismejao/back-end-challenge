<?php

namespace Integration\Tests\Unit\DTOs;

use Integration\DTOs\AlbumDTO;
use Integration\DTOs\ArtistDTO;
use Integration\DTOs\TrackDTO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TrackDTOTest extends TestCase
{
    #[Test]
    public function it_creates_a_track_dto_with_all_fields(): void
    {
        $album = new AlbumDTO(
            name: 'Test Album', albumType: 'single', releaseDate: '2025-01-01',
            releaseDatePrecision: 'day', totalTracks: 1,
            images: [['url' => 'http://img.test/1.jpg', 'width' => 640, 'height' => 640]],
            externalId: 'album123', externalUrl: 'http://spotify.com/album/123',
            artists: [new ArtistDTO(name: 'Artist 1', externalId: 'art1')],
        );

        $dto = new TrackDTO(
            name: 'Test Track', isrc: 'USRC17607839', durationMs: 180000,
            explicit: true, discNumber: 1, trackNumber: 3,
            externalId: 'track123', externalUrl: 'http://spotify.com/track/123',
            album: $album,
            artists: [new ArtistDTO(name: 'Artist 1', externalId: 'art1')],
            availableMarkets: ['BR', 'US'],
        );

        $this->assertSame('Test Track', $dto->name);
        $this->assertSame('USRC17607839', $dto->isrc);
        $this->assertSame(180000, $dto->durationMs);
        $this->assertTrue($dto->explicit);
        $this->assertSame(['BR', 'US'], $dto->availableMarkets);
    }

    #[Test]
    public function it_defaults_optional_fields(): void
    {
        $album = new AlbumDTO(
            name: 'A', albumType: 'album', releaseDate: null,
            releaseDatePrecision: 'day', totalTracks: 0, images: null,
            externalId: 'a1', externalUrl: null,
        );

        $dto = new TrackDTO(
            name: 'T', isrc: 'AABB00112233', durationMs: 1000,
            explicit: false, discNumber: 1, trackNumber: 1,
            externalId: 't1', externalUrl: null, album: $album,
        );

        $this->assertSame([], $dto->artists);
        $this->assertSame([], $dto->availableMarkets);
    }
}
