<?php

namespace App\Domain\Integration\Providers\Spotify;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SpotifyAuthService
{
    private const CACHE_KEY = 'spotify_access_token';

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $tokenUrl,
    ) {}

    public function getAccessToken(): string
    {
        return Cache::remember(self::CACHE_KEY, $this->getTtl(), function () {
            $response = Http::asForm()->post($this->tokenUrl, [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            $response->throw();

            return $response->json('access_token');
        });
    }

    public function authenticatedClient(): PendingRequest
    {
        return Http::withToken($this->getAccessToken());
    }

    public function forgetToken(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function getTtl(): int
    {
        return 3500; // slightly less than Spotify's 3600s to avoid edge cases
    }
}
