<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Integration\Providers\IntegrationServiceProvider;
use Music\Providers\MusicServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(MusicServiceProvider::class);
        $this->app->register(IntegrationServiceProvider::class);
    }

    public function boot(): void
    {
        //
    }
}
