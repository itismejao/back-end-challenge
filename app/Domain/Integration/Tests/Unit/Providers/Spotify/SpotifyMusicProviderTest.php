<?php

namespace Integration\Tests\Unit\Providers\Spotify;

use Integration\Providers\Spotify\SpotifyAuthService;
use Integration\Providers\Spotify\SpotifyMusicProvider;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SpotifyMusicProviderTest extends TestCase
{
    private SpotifyMusicProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $auth = new SpotifyAuthService('fake-id', 'fake-secret', 'https://accounts.spotify.com/api/token');
        Http::fake(['accounts.spotify.com/*' => Http::response(['access_token' => 'fake-token', 'token_type' => 'Bearer', 'expires_in' => 3600])]);
        $this->provider = new SpotifyMusicProvider($auth);
    }

    #[Test]
    public function it_returns_provider_code(): void
    {
        $this->assertSame('spotify', $this->provider->getProviderCode());
    }

    #[Test]
    public function it_returns_null_when_no_results_found(): void
    {
        Http::fake([
            'accounts.spotify.com/*' => Http::response(['access_token' => 'fake', 'token_type' => 'Bearer', 'expires_in' => 3600]),
            'api.spotify.com/v1/search*' => Http::response(['tracks' => ['items' => []]]),
        ]);
        $this->assertNull($this->provider->searchByIsrc('XXXX00000000'));
    }

    #[Test]
    public function it_maps_track_data_correctly(): void
    {
        Http::fake([
            'accounts.spotify.com/*' => Http::response(['access_token' => 'fake', 'token_type' => 'Bearer', 'expires_in' => 3600]),
            'api.spotify.com/v1/search*' => Http::response($this->spotifySearchResponse()),
        ]);
        $result = $this->provider->searchByIsrc('NO1R42509310', ['BR']);
        $this->assertSame('IRON MAIDEN', $result->name);
        $this->assertSame(215491, $result->durationMs);
        $this->assertTrue($result->explicit);
        $this->assertSame('7qaLSS4e5luH7meEYGu7NM', $result->externalId);
    }

    #[Test]
    public function it_maps_album_data_correctly(): void
    {
        Http::fake([
            'accounts.spotify.com/*' => Http::response(['access_token' => 'fake', 'token_type' => 'Bearer', 'expires_in' => 3600]),
            'api.spotify.com/v1/search*' => Http::response($this->spotifySearchResponse()),
        ]);
        $result = $this->provider->searchByIsrc('NO1R42509310', ['BR']);
        $this->assertSame('IRON MAIDEN', $result->album->name);
        $this->assertSame('single', $result->album->albumType);
        $this->assertCount(3, $result->album->images);
    }

    #[Test]
    public function it_maps_artists_correctly(): void
    {
        Http::fake([
            'accounts.spotify.com/*' => Http::response(['access_token' => 'fake', 'token_type' => 'Bearer', 'expires_in' => 3600]),
            'api.spotify.com/v1/search*' => Http::response($this->spotifySearchResponse()),
        ]);
        $result = $this->provider->searchByIsrc('NO1R42509310', ['BR']);
        $this->assertCount(2, $result->artists);
        $this->assertSame('Carefree', $result->artists[0]->name);
    }

    #[Test]
    public function it_collects_available_markets(): void
    {
        Http::fake([
            'accounts.spotify.com/*' => Http::response(['access_token' => 'fake', 'token_type' => 'Bearer', 'expires_in' => 3600]),
            'api.spotify.com/v1/search*' => Http::response($this->spotifySearchResponse(isPlayable: true)),
        ]);
        $this->assertSame(['BR', 'US'], $this->provider->searchByIsrc('NO1R42509310', ['BR', 'US'])->availableMarkets);
    }

    #[Test]
    public function it_excludes_non_playable_markets(): void
    {
        Http::fake([
            'accounts.spotify.com/*' => Http::response(['access_token' => 'fake', 'token_type' => 'Bearer', 'expires_in' => 3600]),
            'api.spotify.com/v1/search*' => Http::response($this->spotifySearchResponse(isPlayable: false)),
        ]);
        $this->assertSame([], $this->provider->searchByIsrc('NO1R42509310', ['BR'])->availableMarkets);
    }

    #[Test]
    public function it_searches_without_market_when_none_provided(): void
    {
        Http::fake([
            'accounts.spotify.com/*' => Http::response(['access_token' => 'fake', 'token_type' => 'Bearer', 'expires_in' => 3600]),
            'api.spotify.com/v1/search*' => Http::response($this->spotifySearchResponse()),
        ]);
        $result = $this->provider->searchByIsrc('NO1R42509310');
        $this->assertNotNull($result);
        $this->assertSame([], $result->availableMarkets);
    }

    #[Test]
    public function it_retries_on_401_with_fresh_token(): void
    {
        $callCount = 0;
        Http::fake([
            'accounts.spotify.com/*' => Http::response(['access_token' => 'fresh', 'token_type' => 'Bearer', 'expires_in' => 3600]),
            'api.spotify.com/v1/search*' => function () use (&$callCount) {
                $callCount++;
                return $callCount === 1
                    ? Http::response(['error' => ['status' => 401, 'message' => 'Expired']], 401)
                    : Http::response($this->spotifySearchResponse());
            },
        ]);
        $this->assertNotNull($this->provider->searchByIsrc('NO1R42509310'));
        $this->assertSame(2, $callCount);
    }

    #[Test]
    public function it_normalizes_unknown_album_type_to_album(): void
    {
        $response = $this->spotifySearchResponse();
        $response['tracks']['items'][0]['album']['album_type'] = 'unknown_type';
        Http::fake([
            'accounts.spotify.com/*' => Http::response(['access_token' => 'fake', 'token_type' => 'Bearer', 'expires_in' => 3600]),
            'api.spotify.com/v1/search*' => Http::response($response),
        ]);
        $this->assertSame('album', $this->provider->searchByIsrc('NO1R42509310')->album->albumType);
    }

    private function spotifySearchResponse(bool $isPlayable = true): array
    {
        return ['tracks' => ['items' => [[
            'id' => '7qaLSS4e5luH7meEYGu7NM', 'name' => 'IRON MAIDEN', 'duration_ms' => 215491,
            'explicit' => true, 'disc_number' => 1, 'track_number' => 1, 'is_playable' => $isPlayable,
            'external_urls' => ['spotify' => 'https://open.spotify.com/track/7qaLSS4e5luH7meEYGu7NM'],
            'album' => [
                'id' => '5ZOUvcjuQWsNyohlotwRRZ', 'name' => 'IRON MAIDEN', 'album_type' => 'single',
                'release_date' => '2025-11-28', 'release_date_precision' => 'day', 'total_tracks' => 1,
                'external_urls' => ['spotify' => 'https://open.spotify.com/album/5ZOUvcjuQWsNyohlotwRRZ'],
                'images' => [
                    ['url' => 'https://i.scdn.co/image/large.jpg', 'width' => 640, 'height' => 640],
                    ['url' => 'https://i.scdn.co/image/medium.jpg', 'width' => 300, 'height' => 300],
                    ['url' => 'https://i.scdn.co/image/small.jpg', 'width' => 64, 'height' => 64],
                ],
                'artists' => [
                    ['id' => '3gXdxjEAOYqbKYDYRGuxUS', 'name' => 'Carefree', 'external_urls' => ['spotify' => 'https://open.spotify.com/artist/3gXdxjEAOYqbKYDYRGuxUS']],
                    ['id' => '1lTEXwwTttlwUk5LnFw4Di', 'name' => 'Redmenn', 'external_urls' => ['spotify' => 'https://open.spotify.com/artist/1lTEXwwTttlwUk5LnFw4Di']],
                ],
            ],
            'artists' => [
                ['id' => '3gXdxjEAOYqbKYDYRGuxUS', 'name' => 'Carefree', 'external_urls' => ['spotify' => 'https://open.spotify.com/artist/3gXdxjEAOYqbKYDYRGuxUS']],
                ['id' => '1lTEXwwTttlwUk5LnFw4Di', 'name' => 'Redmenn', 'external_urls' => ['spotify' => 'https://open.spotify.com/artist/1lTEXwwTttlwUk5LnFw4Di']],
            ],
        ]]]];
    }
}
