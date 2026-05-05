<?php

namespace Tests\Feature\Repositories;

use App\Domain\Music\Models\Album;
use App\Domain\Music\Models\Artist;
use App\Domain\Music\Models\Track;
use App\Domain\Music\Repositories\TrackRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrackRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TrackRepository $repository;
    private Album $album;
    private Artist $artist;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TrackRepository();
        $this->seed(\Database\Seeders\ProviderSeeder::class);
        $this->seed(\Database\Seeders\CountrySeeder::class);

        $this->album = Album::create([
            'name' => 'Test Album', 'album_type' => 'single',
            'release_date_precision' => 'day', 'total_tracks' => 1,
        ]);
        $this->artist = Artist::create(['name' => 'Test Artist']);
    }

    #[Test]
    public function it_creates_track_with_external_id_and_artists(): void
    {
        $track = $this->repository->upsertWithExternalId(
            attributes: [
                'isrc' => 'TEST12345678', 'name' => 'Test Track',
                'duration_ms' => 180000, 'explicit' => false,
                'disc_number' => 1, 'track_number' => 1,
                'availability_mode' => 'unknown',
            ],
            providerCode: 'spotify',
            externalId: 'track123',
            externalUrl: 'http://spotify.com/track/123',
            album: $this->album,
            artists: [$this->artist],
        );

        $this->assertDatabaseHas('tracks', ['isrc' => 'TEST12345678']);
        $this->assertDatabaseHas('track_external_ids', ['external_id' => 'track123']);
        $this->assertDatabaseHas('track_artists', [
            'track_id' => $track->id,
            'artist_id' => $this->artist->id,
            'position' => 0,
        ]);
    }

    #[Test]
    public function it_updates_existing_track(): void
    {
        $track = $this->repository->upsertWithExternalId(
            attributes: [
                'isrc' => 'TEST12345678', 'name' => 'Original',
                'duration_ms' => 180000, 'explicit' => false,
                'disc_number' => 1, 'track_number' => 1,
                'availability_mode' => 'unknown',
            ],
            providerCode: 'spotify', externalId: 'track123',
            externalUrl: null, album: $this->album, artists: [$this->artist],
        );

        $updated = $this->repository->upsertWithExternalId(
            attributes: [
                'isrc' => 'TEST12345678', 'name' => 'Updated',
                'duration_ms' => 200000, 'explicit' => true,
                'disc_number' => 1, 'track_number' => 1,
                'availability_mode' => 'markets',
            ],
            providerCode: 'spotify', externalId: 'track123',
            externalUrl: 'http://new-url.com', album: $this->album, artists: [$this->artist],
        );

        $this->assertSame($track->id, $updated->id);
        $this->assertSame('Updated', $updated->fresh()->name);
        $this->assertSame(200000, $updated->fresh()->duration_ms);
        $this->assertSame(1, Track::count());
    }

    #[Test]
    public function it_syncs_available_markets(): void
    {
        $track = $this->repository->upsertWithExternalId(
            attributes: [
                'isrc' => 'TEST12345678', 'name' => 'Track',
                'duration_ms' => 180000, 'explicit' => false,
                'disc_number' => 1, 'track_number' => 1,
                'availability_mode' => 'markets',
            ],
            providerCode: 'spotify', externalId: 'track123',
            externalUrl: null, album: $this->album,
            artists: [$this->artist], marketCodes: ['BR', 'US'],
        );

        $this->assertCount(2, $track->availableMarkets);
        $this->assertTrue($track->availableMarkets->contains('code', 'BR'));
        $this->assertTrue($track->availableMarkets->contains('code', 'US'));
    }

    #[Test]
    public function it_replaces_markets_on_update(): void
    {
        $this->repository->upsertWithExternalId(
            attributes: [
                'isrc' => 'TEST12345678', 'name' => 'Track',
                'duration_ms' => 180000, 'explicit' => false,
                'disc_number' => 1, 'track_number' => 1,
                'availability_mode' => 'markets',
            ],
            providerCode: 'spotify', externalId: 'track123',
            externalUrl: null, album: $this->album,
            artists: [$this->artist], marketCodes: ['BR', 'US'],
        );

        $updated = $this->repository->upsertWithExternalId(
            attributes: [
                'isrc' => 'TEST12345678', 'name' => 'Track',
                'duration_ms' => 180000, 'explicit' => false,
                'disc_number' => 1, 'track_number' => 1,
                'availability_mode' => 'markets',
            ],
            providerCode: 'spotify', externalId: 'track123',
            externalUrl: null, album: $this->album,
            artists: [$this->artist], marketCodes: ['GB'],
        );

        $updated->load('availableMarkets');
        $this->assertCount(1, $updated->availableMarkets);
        $this->assertTrue($updated->availableMarkets->contains('code', 'GB'));
    }
}
