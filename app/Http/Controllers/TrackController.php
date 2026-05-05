<?php

namespace App\Http\Controllers;

use App\Domain\Integration\Jobs\FetchTrackByIsrcJob;
use App\Domain\Music\Contracts\TrackQueryInterface;
use App\Http\Resources\TrackResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function index(Request $request, TrackQueryInterface $trackQuery)
    {
        $validated = $request->validate([
            'market' => ['required', 'string', 'size:2'],
            'order_by' => ['sometimes', 'string', 'in:title,duration,release_date,artist,track_number,created_at'],
            'direction' => ['sometimes', 'string', 'in:asc,desc'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $tracks = $trackQuery->paginate($validated);

        TrackResource::withMarket(strtoupper($validated['market']));

        return TrackResource::collection($tracks);
    }

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
