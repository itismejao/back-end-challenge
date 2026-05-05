<?php

namespace Tests\Feature\Http;

use App\Domain\Integration\Models\Provider;
use App\Domain\Integration\Models\TrackExternalId;
use App\Domain\Music\Cache\TrackListingCache;
use App\Domain\Music\Models\Album;
use App\Domain\Music\Models\Artist;
use App\Domain\Music\Models\Track;
use App\Domain\Shared\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrackEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\ProviderSeeder::class);
        $this->seed(\Database\Seeders\CountrySeeder::class);
    }

    #[Test]
    public function it_requires_market_parameter(): void
    {
        $this->getJson('/api/tracks')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['market']);
    }

    #[Test]
    public function it_validates_market_length(): void
    {
        $this->getJson('/api/tracks?market=BRA')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['market']);
    }

    #[Test]
    public function it_returns_empty_data_when_no_tracks(): void
    {
        $this->getJson('/api/tracks?market=BR')
            ->assertOk()
            ->assertJson(['data' => []]);
    }

    #[Test]
    public function it_returns_tracks_with_correct_structure(): void
    {
        $this->createTrack();

        $this->getJson('/api/tracks?market=BR')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'isrc', 'title', 'duration', 'duration_ms', 'explicit',
                    'disc_number', 'track_number', 'available', 'market',
                    'album' => ['name', 'type', 'release_date', 'thumb'],
                    'artists' => [['id', 'name']],
                    'spotify' => ['external_id', 'url'],
                ]],
                'meta' => ['path', 'per_page', 'next_cursor', 'prev_cursor'],
            ]);
    }

    #[Test]
    public function it_shows_available_true_for_matching_market(): void
    {
        $this->createTrack(markets: ['BR']);

        $this->getJson('/api/tracks?market=BR')
            ->assertOk()
            ->assertJsonPath('data.0.available', true);
    }

    #[Test]
    public function it_shows_available_false_for_non_matching_market(): void
    {
        $this->createTrack(markets: ['BR']);

        $this->getJson('/api/tracks?market=JP')
            ->assertOk()
            ->assertJsonPath('data.0.available', false);
    }

    #[Test]
    public function it_orders_by_title_ascending_by_default(): void
    {
        $this->createTrack(name: 'Zebra');
        $this->createTrack(name: 'Alpha', isrc: 'ZZZZ00000001', externalId: 'ext2');

        $response = $this->getJson('/api/tracks?market=BR')->assertOk();

        $titles = collect($response->json('data'))->pluck('title')->toArray();
        $this->assertSame(['Alpha', 'Zebra'], $titles);
    }

    #[Test]
    public function it_supports_order_by_duration(): void
    {
        $this->createTrack(name: 'Short', durationMs: 60000);
        $this->createTrack(name: 'Long', durationMs: 300000, isrc: 'ZZZZ00000001', externalId: 'ext2');

        $response = $this->getJson('/api/tracks?market=BR&order_by=duration&direction=desc')->assertOk();

        $titles = collect($response->json('data'))->pluck('title')->toArray();
        $this->assertSame(['Long', 'Short'], $titles);
    }

    #[Test]
    public function it_validates_order_by_values(): void
    {
        $this->getJson('/api/tracks?market=BR&order_by=invalid')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['order_by']);
    }

    #[Test]
    public function it_respects_per_page(): void
    {
        $this->createTrack(name: 'Track 1');
        $this->createTrack(name: 'Track 2', isrc: 'ZZZZ00000001', externalId: 'ext2');

        $this->getJson('/api/tracks?market=BR&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.per_page', 1);
    }

    #[Test]
    public function it_caches_response(): void
    {
        $this->createTrack();

        $this->getJson('/api/tracks?market=BR')->assertOk();

        $cacheKey = 'tracks:' . md5(url('/api/tracks') . '?market=BR');
        $cached = Cache::tags(TrackListingCache::TAG)->get($cacheKey);
        $this->assertNotNull($cached);
    }

    #[Test]
    public function fetch_dispatches_jobs(): void
    {
        Queue::fake();

        $this->postJson('/api/tracks/fetch', [
            'isrcs' => ['NO1R42509310', 'USRC17607839'],
            'markets' => ['BR'],
        ])
            ->assertStatus(202)
            ->assertJson(['message' => '2 job(s) dispatched.']);

        Queue::assertCount(2);
    }

    #[Test]
    public function fetch_validates_isrcs(): void
    {
        $this->postJson('/api/tracks/fetch', ['isrcs' => []])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['isrcs']);
    }

    #[Test]
    public function fetch_validates_isrc_length(): void
    {
        $this->postJson('/api/tracks/fetch', ['isrcs' => ['SHORT']])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['isrcs.0']);
    }

    private function createTrack(
        string $name = 'Test Track',
        string $isrc = 'TEST12345678',
        string $externalId = 'ext1',
        int $durationMs = 215491,
        array $markets = [],
    ): Track {
        $album = Album::firstOrCreate(
            ['name' => 'Test Album'],
            [
                'album_type' => 'single', 'release_date' => '2025-01-01',
                'release_date_precision' => 'day', 'total_tracks' => 1,
                'images' => [['url' => 'http://img/small.jpg', 'width' => 64, 'height' => 64]],
            ],
        );

        $artist = Artist::firstOrCreate(['name' => 'Test Artist']);

        $track = Track::create([
            'album_id' => $album->id, 'isrc' => $isrc, 'name' => $name,
            'duration_ms' => $durationMs, 'explicit' => false,
            'disc_number' => 1, 'track_number' => 1, 'availability_mode' => 'markets',
        ]);

        $track->artists()->sync([$artist->id => ['position' => 0]]);

        if (! empty($markets)) {
            $track->availableMarkets()->sync($markets);
        }

        $track->externalIds()->create([
            'provider_code' => 'spotify', 'external_id' => $externalId,
            'external_url' => 'https://open.spotify.com/track/' . $externalId,
            'synced_at' => now(),
        ]);

        return $track;
    }
}
