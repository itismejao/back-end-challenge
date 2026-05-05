<?php

namespace Music\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Music\Contracts\AlbumRepositoryInterface;
use Music\Contracts\ArtistRepositoryInterface;
use Music\Contracts\TrackQueryInterface;
use Music\Contracts\TrackRepositoryInterface;
use Music\Models\Album;
use Music\Models\Artist;
use Music\Models\Track;
use Music\Observers\AlbumObserver;
use Music\Observers\ArtistObserver;
use Music\Observers\TrackObserver;
use Music\Repositories\AlbumRepository;
use Music\Repositories\ArtistRepository;
use Music\Repositories\TrackQueryRepository;
use Music\Repositories\TrackRepository;

class MusicServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ArtistRepositoryInterface::class, ArtistRepository::class);
        $this->app->bind(AlbumRepositoryInterface::class, AlbumRepository::class);
        $this->app->bind(TrackRepositoryInterface::class, TrackRepository::class);
        $this->app->bind(TrackQueryInterface::class, TrackQueryRepository::class);
    }

    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerObservers();
    }

    protected function registerRoutes(): void
    {
        if (! $this->app->routesAreCached()) {
            Route::middleware('api')
                ->prefix('api')
                ->group(__DIR__.'/../Http/Routes/api.php');
        }
    }

    protected function registerObservers(): void
    {
        Track::observe(TrackObserver::class);
        Album::observe(AlbumObserver::class);
        Artist::observe(ArtistObserver::class);
    }
}
