<?php

namespace App\Providers;

use App\Domain\Integration\Contracts\MusicProviderFactoryInterface;
use App\Domain\Integration\Providers\Spotify\SpotifyAuthService;
use App\Domain\Integration\Providers\Spotify\SpotifyMusicProvider;
use App\Domain\Integration\Services\MusicProviderFactory;
use App\Domain\Music\Contracts\AlbumRepositoryInterface;
use App\Domain\Music\Contracts\ArtistRepositoryInterface;
use App\Domain\Music\Contracts\TrackQueryInterface;
use App\Domain\Music\Contracts\TrackRepositoryInterface;
use App\Domain\Music\Repositories\AlbumRepository;
use App\Domain\Music\Repositories\ArtistRepository;
use App\Domain\Music\Repositories\TrackQueryRepository;
use App\Domain\Music\Repositories\TrackRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ArtistRepositoryInterface::class, ArtistRepository::class);
        $this->app->bind(AlbumRepositoryInterface::class, AlbumRepository::class);
        $this->app->bind(TrackRepositoryInterface::class, TrackRepository::class);
        $this->app->bind(TrackQueryInterface::class, TrackQueryRepository::class);

        $this->app->singleton(SpotifyAuthService::class, function () {
            $config = config('services.spotify');

            return new SpotifyAuthService(
                clientId: $config['client_id'],
                clientSecret: $config['client_secret'],
                tokenUrl: $config['token_url'],
            );
        });

        $this->app->singleton(MusicProviderFactoryInterface::class, function ($app) {
            $factory = new MusicProviderFactory();
            $factory->register('spotify', $app->make(SpotifyMusicProvider::class));

            return $factory;
        });

    }

    public function boot(): void
    {
        //
    }
}
