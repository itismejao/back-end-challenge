<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $market = strtoupper($request->query('market', ''));

        $spotifyExternalId = $this->externalIds
            ->where('provider_code', 'spotify')
            ->first();

        return [
            'id' => $this->id,
            'isrc' => $this->isrc,
            'title' => $this->name,
            'duration' => $this->formatDuration($this->duration_ms),
            'duration_ms' => $this->duration_ms,
            'explicit' => $this->explicit,
            'disc_number' => $this->disc_number,
            'track_number' => $this->track_number,
            'available' => $market !== '' && $this->availableMarkets->contains('code', $market),
            'market' => $market,
            'album' => [
                'name' => $this->album->name,
                'type' => $this->album->album_type->value,
                'release_date' => $this->album->release_date?->format('Y-m-d'),
                'thumb' => $this->getAlbumThumb(),
            ],
            'artists' => $this->artists->map(fn ($artist) => [
                'id' => $artist->id,
                'name' => $artist->name,
            ])->values(),
            'spotify' => [
                'external_id' => $spotifyExternalId?->external_id,
                'url' => $spotifyExternalId?->external_url,
            ],
        ];
    }

    private function formatDuration(int $ms): string
    {
        $totalSeconds = intdiv($ms, 1000);
        $minutes = intdiv($totalSeconds, 60);
        $seconds = $totalSeconds % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    private function getAlbumThumb(): ?string
    {
        $images = $this->album->images;

        if (empty($images)) {
            return null;
        }

        return collect($images)->sortBy('width')->first()['url'] ?? null;
    }
}
