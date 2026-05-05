<?php

namespace Tests\Unit\Services;

use App\Domain\Integration\Contracts\MusicProviderInterface;
use App\Domain\Integration\DTOs\AlbumDTO;
use App\Domain\Integration\DTOs\ArtistDTO;
use App\Domain\Integration\DTOs\TrackDTO;
use App\Domain\Integration\Services\TrackIngestionService;
use App\Domain\Music\Cache\TrackListingCache;
use App\Domain\Music\Contracts\AlbumRepositoryInterface;
use App\Domain\Music\Contracts\ArtistRepositoryInterface;
use App\Domain\Music\Contracts\TrackRepositoryInterface;
use App\Domain\Music\Models\Album;
use App\Domain\Music\Models\Artist;
use App\Domain\Music\Models\Track;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrackIngestionServiceTest extends TestCase
{
    private ArtistRepositoryInterface $artistRepo;
    private AlbumRepositoryInterface $albumRepo;
    private TrackRepositoryInterface $trackRepo;
    private MusicProviderInterface $provider;
    private TrackIngestionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artistRepo = $this->createMock(ArtistRepositoryInterface::class);
        $this->albumRepo = $this->createMock(AlbumRepositoryInterface::class);
        $this->trackRepo = $this->createMock(TrackRepositoryInterface::class);
        $this->provider = $this->createMock(MusicProviderInterface::class);

        $this->service = new TrackIngestionService(
            $this->artistRepo,
            $this->albumRepo,
            $this->trackRepo,
        );
    }

    #[Test]
    public function it_returns_null_when_provider_finds_nothing(): void
    {
        $this->provider->method('searchByIsrc')->willReturn(null);
        $this->provider->method('getProviderCode')->willReturn('spotify');

        $result = $this->service->ingest('XXXX00000000', $this->provider);

        $this->assertNull($result);
    }

    #[Test]
    public function it_orchestrates_full_ingestion(): void
    {
        $trackDTO = $this->makeTrackDTO();

        $this->provider->method('searchByIsrc')->willReturn($trackDTO);
        $this->provider->method('getProviderCode')->willReturn('spotify');

        $artist = new Artist(['name' => 'Carefree']);
        $artist->id = 1;

        $album = new Album(['name' => 'IRON MAIDEN']);
        $album->id = 1;

        $track = new Track(['name' => 'IRON MAIDEN']);
        $track->id = 1;

        $this->artistRepo->method('upsertWithExternalId')->willReturn($artist);
        $this->albumRepo->method('upsertWithExternalId')->willReturn($album);
        $this->trackRepo->method('upsertWithExternalId')->willReturn($track);

        $result = $this->service->ingest('NO1R42509310', $this->provider, ['BR']);

        $this->assertNotNull($result);
        $this->assertSame('IRON MAIDEN', $result->name);
    }

    #[Test]
    public function it_resolves_availability_mode_to_markets_when_playable(): void
    {
        $trackDTO = $this->makeTrackDTO(availableMarkets: ['BR']);

        $this->provider->method('searchByIsrc')->willReturn($trackDTO);
        $this->provider->method('getProviderCode')->willReturn('spotify');

        $artist = new Artist(['name' => 'A']);
        $artist->id = 1;
        $album = new Album(['name' => 'A']);
        $album->id = 1;
        $track = new Track(['name' => 'T']);
        $track->id = 1;

        $this->artistRepo->method('upsertWithExternalId')->willReturn($artist);
        $this->albumRepo->method('upsertWithExternalId')->willReturn($album);

        $this->trackRepo->expects($this->once())
            ->method('upsertWithExternalId')
            ->with(
                $this->callback(fn ($attrs) => $attrs['availability_mode'] === 'markets'),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->equalTo(['BR']),
            )
            ->willReturn($track);

        $this->service->ingest('NO1R42509310', $this->provider, ['BR']);
    }

    #[Test]
    public function it_resolves_availability_mode_to_unknown_when_not_playable(): void
    {
        $trackDTO = $this->makeTrackDTO(availableMarkets: []);

        $this->provider->method('searchByIsrc')->willReturn($trackDTO);
        $this->provider->method('getProviderCode')->willReturn('spotify');

        $artist = new Artist(['name' => 'A']);
        $artist->id = 1;
        $album = new Album(['name' => 'A']);
        $album->id = 1;
        $track = new Track(['name' => 'T']);
        $track->id = 1;

        $this->artistRepo->method('upsertWithExternalId')->willReturn($artist);
        $this->albumRepo->method('upsertWithExternalId')->willReturn($album);

        $this->trackRepo->expects($this->once())
            ->method('upsertWithExternalId')
            ->with(
                $this->callback(fn ($attrs) => $attrs['availability_mode'] === 'unknown'),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->equalTo([]),
            )
            ->willReturn($track);

        $this->service->ingest('NO1R42509310', $this->provider);
    }

    #[Test]
    public function it_flushes_cache_after_ingestion(): void
    {
        Cache::tags(TrackListingCache::TAG)->put('test-key', 'value', 600);

        $trackDTO = $this->makeTrackDTO();
        $this->provider->method('searchByIsrc')->willReturn($trackDTO);
        $this->provider->method('getProviderCode')->willReturn('spotify');

        $artist = new Artist(['name' => 'A']);
        $artist->id = 1;
        $album = new Album(['name' => 'A']);
        $album->id = 1;
        $track = new Track(['name' => 'T']);
        $track->id = 1;

        $this->artistRepo->method('upsertWithExternalId')->willReturn($artist);
        $this->albumRepo->method('upsertWithExternalId')->willReturn($album);
        $this->trackRepo->method('upsertWithExternalId')->willReturn($track);

        $this->service->ingest('NO1R42509310', $this->provider);

        $this->assertNull(Cache::tags(TrackListingCache::TAG)->get('test-key'));
    }

    private function makeTrackDTO(array $availableMarkets = []): TrackDTO
    {
        return new TrackDTO(
            name: 'IRON MAIDEN',
            isrc: 'NO1R42509310',
            durationMs: 215491,
            explicit: true,
            discNumber: 1,
            trackNumber: 1,
            externalId: '7qaLSS4e5luH7meEYGu7NM',
            externalUrl: 'https://open.spotify.com/track/7qaLSS4e5luH7meEYGu7NM',
            album: new AlbumDTO(
                name: 'IRON MAIDEN', albumType: 'single', releaseDate: '2025-11-28',
                releaseDatePrecision: 'day', totalTracks: 1, images: null,
                externalId: '5ZOUvcjuQWsNyohlotwRRZ', externalUrl: null,
                artists: [new ArtistDTO(name: 'Carefree', externalId: 'art1')],
            ),
            artists: [new ArtistDTO(name: 'Carefree', externalId: 'art1')],
            availableMarkets: $availableMarkets,
        );
    }
}
