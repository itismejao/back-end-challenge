<?php

namespace Integration\Http\Controllers;

use Integration\Http\Requests\TrackFetchRequest;
use Integration\Jobs\FetchTrackByIsrcJob;
use Illuminate\Http\JsonResponse;

class TrackFetchController
{
    public function __invoke(TrackFetchRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $provider = $validated['provider'] ?? 'spotify';
        $markets = array_map('strtoupper', $validated['markets'] ?? []);
        $dispatched = [];

        foreach ($validated['isrcs'] as $isrc) {
            FetchTrackByIsrcJob::dispatch($isrc, $provider, $markets);
            $dispatched[] = $isrc;
        }

        return response()->json([
            'message' => count($dispatched).' job(s) dispatched.',
            'isrcs' => $dispatched,
            'provider' => $provider,
            'markets' => $markets,
        ], 202);
    }
}
