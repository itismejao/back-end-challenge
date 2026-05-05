<?php

namespace Integration\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Integration\Console\FetchTracksByIsrcCommand;
use Integration\Contracts\MusicProviderFactoryInterface;
use Integration\Models\TrackExternalId;
use Integration\Observers\TrackExternalIdObserver;
use Integration\Providers\Spotify\SpotifyAuthService;
use Integration\Providers\Spotify\SpotifyMusicProvider;
use Integration\Services\MusicProviderFactory;

class IntegrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
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
        $this->registerRoutes();
        $this->registerCommands();
        $this->registerObservers();
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([FetchTracksByIsrcCommand::class]);
        }
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
        TrackExternalId::observe(TrackExternalIdObserver::class);
    }
}
