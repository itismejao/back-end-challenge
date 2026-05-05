<?php

namespace Tests\Feature\Observers;

use App\Domain\Integration\Models\TrackExternalId;
use App\Domain\Music\Cache\TrackListingCache;
use App\Domain\Music\Models\Album;
use App\Domain\Music\Models\Artist;
use App\Domain\Music\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CacheInvalidationTest extends TestCase
{
    use RefreshDatabase;

    private Album $album;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\ProviderSeeder::class);

        $this->album = Album::create([
            'name' => 'Test Album', 'album_type' => 'single',
            'release_date_precision' => 'day', 'total_tracks' => 1,
        ]);
    }

    #[Test]
    public function track_created_flushes_cache(): void
    {
        $this->populateCache();

        Track::create([
            'album_id' => $this->album->id, 'isrc' => 'TEST12345678',
            'name' => 'Track', 'duration_ms' => 180000, 'explicit' => false,
            'disc_number' => 1, 'track_number' => 1,
        ]);

        $this->assertCacheFlushed();
    }

    #[Test]
    public function track_updated_flushes_cache(): void
    {
        $track = Track::create([
            'album_id' => $this->album->id, 'isrc' => 'TEST12345678',
            'name' => 'Track', 'duration_ms' => 180000, 'explicit' => false,
            'disc_number' => 1, 'track_number' => 1,
        ]);

        $this->populateCache();

        $track->update(['name' => 'Updated']);

        $this->assertCacheFlushed();
    }

    #[Test]
    public function track_deleted_flushes_cache(): void
    {
        $track = Track::create([
            'album_id' => $this->album->id, 'isrc' => 'TEST12345678',
            'name' => 'Track', 'duration_ms' => 180000, 'explicit' => false,
            'disc_number' => 1, 'track_number' => 1,
        ]);

        $this->populateCache();

        $track->delete();

        $this->assertCacheFlushed();
    }

    #[Test]
    public function album_updated_flushes_cache(): void
    {
        $this->populateCache();

        $this->album->update(['name' => 'Updated Album']);

        $this->assertCacheFlushed();
    }

    #[Test]
    public function artist_updated_flushes_cache(): void
    {
        $artist = Artist::create(['name' => 'Artist']);

        $this->populateCache();

        $artist->update(['name' => 'Updated Artist']);

        $this->assertCacheFlushed();
    }

    #[Test]
    public function track_external_id_created_flushes_cache(): void
    {
        $track = Track::create([
            'album_id' => $this->album->id, 'isrc' => 'TEST12345678',
            'name' => 'Track', 'duration_ms' => 180000, 'explicit' => false,
            'disc_number' => 1, 'track_number' => 1,
        ]);

        $this->populateCache();

        TrackExternalId::create([
            'track_id' => $track->id, 'provider_code' => 'spotify',
            'external_id' => 'ext123', 'synced_at' => now(),
        ]);

        $this->assertCacheFlushed();
    }

    #[Test]
    public function track_external_id_updated_flushes_cache(): void
    {
        $track = Track::create([
            'album_id' => $this->album->id, 'isrc' => 'TEST12345678',
            'name' => 'Track', 'duration_ms' => 180000, 'explicit' => false,
            'disc_number' => 1, 'track_number' => 1,
        ]);

        $externalId = TrackExternalId::create([
            'track_id' => $track->id, 'provider_code' => 'spotify',
            'external_id' => 'ext123', 'synced_at' => now(),
        ]);

        $this->populateCache();

        $externalId->update(['external_url' => 'http://new-url.com']);

        $this->assertCacheFlushed();
    }

    private function populateCache(): void
    {
        Cache::tags(TrackListingCache::TAG)->put('test-cache-key', 'cached-value', 600);
        $this->assertSame('cached-value', Cache::tags(TrackListingCache::TAG)->get('test-cache-key'));
    }

    private function assertCacheFlushed(): void
    {
        $this->assertNull(Cache::tags(TrackListingCache::TAG)->get('test-cache-key'));
    }
}
