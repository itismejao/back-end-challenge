<?php

namespace Music\Tests\Unit\Resources;

use Integration\Models\TrackExternalId;
use Music\Enums\AlbumType;
use Music\Enums\AvailabilityMode;
use Music\Http\Resources\TrackResource;
use Music\Models\Album;
use Music\Models\Artist;
use Music\Models\Track;
use Shared\Models\Country;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrackResourceTest extends TestCase
{
    #[Test]
    public function it_formats_duration_as_mm_ss(): void
    {
        $track = $this->makeTrack(durationMs: 215491);
        $request = Request::create('/api/tracks?market=BR');

        $resource = (new TrackResource($track))->toArray($request);

        $this->assertSame('03:35', $resource['duration']);
    }

    #[Test]
    public function it_formats_short_duration(): void
    {
        $track = $this->makeTrack(durationMs: 5000);
        $request = Request::create('/api/tracks?market=BR');

        $resource = (new TrackResource($track))->toArray($request);

        $this->assertSame('00:05', $resource['duration']);
    }

    #[Test]
    public function it_shows_available_true_for_matching_market(): void
    {
        $track = $this->makeTrack(markets: ['BR']);
        $request = Request::create('/api/tracks?market=BR');

        $resource = (new TrackResource($track))->toArray($request);

        $this->assertTrue($resource['available']);
        $this->assertSame('BR', $resource['market']);
    }

    #[Test]
    public function it_shows_available_false_for_non_matching_market(): void
    {
        $track = $this->makeTrack(markets: ['BR']);
        $request = Request::create('/api/tracks?market=JP');

        $resource = (new TrackResource($track))->toArray($request);

        $this->assertFalse($resource['available']);
        $this->assertSame('JP', $resource['market']);
    }

    #[Test]
    public function it_picks_smallest_image_as_thumb(): void
    {
        $images = [
            ['url' => 'http://img/large.jpg', 'width' => 640, 'height' => 640],
            ['url' => 'http://img/medium.jpg', 'width' => 300, 'height' => 300],
            ['url' => 'http://img/small.jpg', 'width' => 64, 'height' => 64],
        ];

        $track = $this->makeTrack(albumImages: $images);
        $request = Request::create('/api/tracks?market=BR');

        $resource = (new TrackResource($track))->toArray($request);

        $this->assertSame('http://img/small.jpg', $resource['album']['thumb']);
    }

    #[Test]
    public function it_returns_null_thumb_when_no_images(): void
    {
        $track = $this->makeTrack(albumImages: null);
        $request = Request::create('/api/tracks?market=BR');

        $resource = (new TrackResource($track))->toArray($request);

        $this->assertNull($resource['album']['thumb']);
    }

    #[Test]
    public function it_includes_spotify_external_data(): void
    {
        $track = $this->makeTrack();
        $request = Request::create('/api/tracks?market=BR');

        $resource = (new TrackResource($track))->toArray($request);

        $this->assertSame('spotify123', $resource['spotify']['external_id']);
        $this->assertSame('https://open.spotify.com/track/123', $resource['spotify']['url']);
    }

    private function makeTrack(
        int $durationMs = 215491,
        array $markets = [],
        ?array $albumImages = [['url' => 'http://img/1.jpg', 'width' => 640, 'height' => 640]],
    ): Track {
        $album = new Album();
        $album->name = 'Test Album';
        $album->album_type = AlbumType::Single;
        $album->release_date = '2025-01-01';
        $album->images = $albumImages;

        $track = new Track();
        $track->id = 1;
        $track->isrc = 'TEST12345678';
        $track->name = 'Test Track';
        $track->duration_ms = $durationMs;
        $track->explicit = false;
        $track->disc_number = 1;
        $track->track_number = 1;
        $track->availability_mode = AvailabilityMode::Markets;

        $track->setRelation('album', $album);

        $artist = new Artist();
        $artist->id = 1;
        $artist->name = 'Test Artist';
        $track->setRelation('artists', collect([$artist]));

        $marketModels = collect($markets)->map(function ($code) {
            $country = new Country();
            $country->code = $code;
            $country->name = $code;
            return $country;
        });
        $track->setRelation('availableMarkets', $marketModels);

        $externalId = new TrackExternalId();
        $externalId->provider_code = 'spotify';
        $externalId->external_id = 'spotify123';
        $externalId->external_url = 'https://open.spotify.com/track/123';
        $track->setRelation('externalIds', collect([$externalId]));

        return $track;
    }
}
