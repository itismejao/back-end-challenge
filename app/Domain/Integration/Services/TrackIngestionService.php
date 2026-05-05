<?php

namespace App\Domain\Integration\Services;

use App\Domain\Integration\Contracts\MusicProviderInterface;
use App\Domain\Integration\DTOs\ArtistDTO;
use App\Domain\Integration\DTOs\TrackDTO;
use App\Domain\Integration\Models\TrackExternalId;
use App\Domain\Music\Contracts\AlbumRepositoryInterface;
use App\Domain\Music\Contracts\ArtistRepositoryInterface;
use App\Domain\Music\Contracts\TrackRepositoryInterface;
use App\Domain\Music\Models\Album;
use App\Domain\Music\Models\Artist;
use App\Domain\Music\Models\Track;
use App\Domain\Music\Cache\TrackListingCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrackIngestionService
{
    public function __construct(
        private readonly ArtistRepositoryInterface $artistRepository,
        private readonly AlbumRepositoryInterface $albumRepository,
        private readonly TrackRepositoryInterface $trackRepository,
    ) {}

    /** @param list<string> $markets */
    public function ingest(string $isrc, MusicProviderInterface $provider, array $markets = []): ?Track
    {
        $trackDTO = $provider->searchByIsrc($isrc, $markets);

        if (! $trackDTO) {
            Log::warning("Track not found for ISRC [{$isrc}] on provider [{$provider->getProviderCode()}]");

            return null;
        }

        $track = $this->withoutObservers(function () use ($trackDTO, $provider) {
            return DB::transaction(function () use ($trackDTO, $provider) {
                $providerCode = $provider->getProviderCode();

                $albumArtists = $this->resolveArtists($trackDTO->album->artists, $providerCode);
                $trackArtists = $this->resolveArtists($trackDTO->artists, $providerCode);

                $album = $this->albumRepository->upsertWithExternalId(
                    attributes: [
                        'name' => $trackDTO->album->name,
                        'album_type' => $trackDTO->album->albumType,
                        'release_date' => $trackDTO->album->releaseDate,
                        'release_date_precision' => $trackDTO->album->releaseDatePrecision,
                        'total_tracks' => $trackDTO->album->totalTracks,
                        'images' => $trackDTO->album->images,
                    ],
                    providerCode: $providerCode,
                    externalId: $trackDTO->album->externalId,
                    externalUrl: $trackDTO->album->externalUrl,
                    artists: $albumArtists,
                );

                return $this->trackRepository->upsertWithExternalId(
                    attributes: [
                        'isrc' => $trackDTO->isrc,
                        'name' => $trackDTO->name,
                        'duration_ms' => $trackDTO->durationMs,
                        'explicit' => $trackDTO->explicit,
                        'disc_number' => $trackDTO->discNumber,
                        'track_number' => $trackDTO->trackNumber,
                        'availability_mode' => $this->resolveAvailabilityMode($trackDTO),
                    ],
                    providerCode: $providerCode,
                    externalId: $trackDTO->externalId,
                    externalUrl: $trackDTO->externalUrl,
                    album: $album,
                    artists: $trackArtists,
                    marketCodes: $trackDTO->availableMarkets,
                );
            });
        });

        TrackListingCache::flush();

        return $track;
    }

    private function withoutObservers(callable $callback): mixed
    {
        $models = [Track::class, Album::class, Artist::class, TrackExternalId::class];

        foreach ($models as $model) {
            $model::withoutEvents(fn () => null); // no-op to init dispatcher
        }

        return Track::withoutEvents(function () use ($callback, $models) {
            return Album::withoutEvents(function () use ($callback, $models) {
                return Artist::withoutEvents(function () use ($callback) {
                    return TrackExternalId::withoutEvents($callback);
                });
            });
        });
    }

    /**
     * @param list<ArtistDTO> $artistDTOs
     * @return list<Artist>
     */
    private function resolveArtists(array $artistDTOs, string $providerCode): array
    {
        return array_map(
            fn (ArtistDTO $dto) => $this->artistRepository->upsertWithExternalId(
                name: $dto->name,
                providerCode: $providerCode,
                externalId: $dto->externalId,
                externalUrl: $dto->externalUrl,
            ),
            $artistDTOs,
        );
    }

    private function resolveAvailabilityMode(TrackDTO $track): string
    {
        return ! empty($track->availableMarkets) ? 'markets' : 'unknown';
    }
}
