<?php

namespace Integration\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IntegrationLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider_code' => $this->provider_code,
            'isrc' => $this->isrc,
            'status' => $this->status,
            'duration_ms' => $this->duration_ms,
            'attempt' => $this->attempt,
            'markets' => $this->markets,
            'error_message' => $this->error_message,
            'error_class' => $this->error_class,
            'track' => $this->whenLoaded('track', fn () => [
                'id' => $this->track->id,
                'name' => $this->track->name,
                'isrc' => $this->track->isrc,
            ]),
            'started_at' => $this->started_at?->toISOString(),
            'finished_at' => $this->finished_at?->toISOString(),
        ];
    }
}
