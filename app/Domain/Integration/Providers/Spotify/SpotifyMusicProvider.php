<?php

namespace App\Domain\Integration\Providers\Spotify;

use App\Domain\Integration\Contracts\MusicProviderInterface;
use App\Domain\Integration\DTOs\AlbumDTO;
use App\Domain\Integration\DTOs\ArtistDTO;
use App\Domain\Integration\DTOs\TrackDTO;
use Illuminate\Http\Client\RequestException;

class SpotifyMusicProvider implements MusicProviderInterface
{
    private const PROVIDER_CODE = 'spotify';
    private const API_BASE_URL = 'https://api.spotify.com/v1';

    public function __construct(
        private readonly SpotifyAuthService $auth,
    ) {}

    public function searchByIsrc(string $isrc, array $markets = []): ?TrackDTO
    {
        try {
            return $this->doSearch($isrc, $markets);
        } catch (RequestException $e) {
            if ($e->response->status() !== 401) {
                throw $e;
            }

            $this->auth->forgetToken();

            return $this->doSearch($isrc, $markets);
        }
    }

    public function getProviderCode(): string
    {
        return self::PROVIDER_CODE;
    }

    /** @param list<string> $markets */
    private function doSearch(string $isrc, array $markets): ?TrackDTO
    {
        if (empty($markets)) {
            return $this->searchWithoutMarket($isrc);
        }

        $trackData = null;
        $availableMarkets = [];

        foreach ($markets as $market) {
            $response = $this->auth->authenticatedClient()
                ->get(self::API_BASE_URL.'/search', [
                    'q' => "isrc:{$isrc}",
                    'type' => 'track',
                    'market' => $market,
                    'limit' => 1,
                ]);

            $response->throw();

            $items = $response->json('tracks.items', []);

            if (empty($items)) {
                continue;
            }

            $trackData ??= $items[0];

            if ($items[0]['is_playable'] ?? false) {
                $availableMarkets[] = $market;
            }
        }

        if (! $trackData) {
            return null;
        }

        return $this->mapTrack($trackData, $isrc, $availableMarkets);
    }

    private function searchWithoutMarket(string $isrc): ?TrackDTO
    {
        $response = $this->auth->authenticatedClient()
            ->get(self::API_BASE_URL.'/search', [
                'q' => "isrc:{$isrc}",
                'type' => 'track',
                'limit' => 1,
            ]);

        $response->throw();

        $items = $response->json('tracks.items', []);

        if (empty($items)) {
            return null;
        }

        return $this->mapTrack($items[0], $isrc, []);
    }

    /** @param list<string> $availableMarkets */
    private function mapTrack(array $data, string $isrc, array $availableMarkets): TrackDTO
    {
        return new TrackDTO(
            name: $data['name'],
            isrc: $isrc,
            durationMs: $data['duration_ms'],
            explicit: $data['explicit'] ?? false,
            discNumber: $data['disc_number'] ?? 1,
            trackNumber: $data['track_number'] ?? 1,
            externalId: $data['id'],
            externalUrl: $data['external_urls']['spotify'] ?? null,
            album: $this->mapAlbum($data['album']),
            artists: $this->mapArtists($data['artists'] ?? []),
            availableMarkets: $availableMarkets,
        );
    }

    private function mapAlbum(array $data): AlbumDTO
    {
        return new AlbumDTO(
            name: $data['name'],
            albumType: $this->normalizeAlbumType($data['album_type'] ?? 'album'),
            releaseDate: $data['release_date'] ?? null,
            releaseDatePrecision: $data['release_date_precision'] ?? 'day',
            totalTracks: $data['total_tracks'] ?? 0,
            images: $this->mapImages($data['images'] ?? []),
            externalId: $data['id'],
            externalUrl: $data['external_urls']['spotify'] ?? null,
            artists: $this->mapArtists($data['artists'] ?? []),
        );
    }

    /** @return list<ArtistDTO> */
    private function mapArtists(array $artists): array
    {
        return array_map(
            fn (array $artist) => new ArtistDTO(
                name: $artist['name'],
                externalId: $artist['id'],
                externalUrl: $artist['external_urls']['spotify'] ?? null,
            ),
            $artists,
        );
    }

    /** @return list<array{url: string, width: int, height: int}>|null */
    private function mapImages(array $images): ?array
    {
        if (empty($images)) {
            return null;
        }

        return array_map(
            fn (array $image) => [
                'url' => $image['url'],
                'width' => $image['width'],
                'height' => $image['height'],
            ],
            $images,
        );
    }

    private function normalizeAlbumType(string $type): string
    {
        return match ($type) {
            'album', 'single', 'compilation', 'ep' => $type,
            default => 'album',
        };
    }
}
