<?php

namespace App\Http\Controllers;

use App\Domain\Integration\Jobs\FetchTrackByIsrcJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function fetch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'isrcs' => ['required', 'array', 'min:1', 'max:100'],
            'isrcs.*' => ['required', 'string', 'size:12'],
            'provider' => ['sometimes', 'string', 'in:spotify'],
            'markets' => ['sometimes', 'array'],
            'markets.*' => ['required', 'string', 'size:2'],
        ]);

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
