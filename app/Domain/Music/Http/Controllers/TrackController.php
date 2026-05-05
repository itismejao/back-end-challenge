<?php

namespace Music\Http\Controllers;

use Music\Cache\TrackListingCache;
use Music\Contracts\TrackQueryInterface;
use Music\Http\Resources\TrackResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackController
{
    public function index(Request $request, TrackQueryInterface $trackQuery): JsonResponse
    {
        $validated = $request->validate([
            'market' => ['required', 'string', 'size:2'],
            'order_by' => ['sometimes', 'string', 'in:title,duration,release_date,artist,track_number,created_at'],
            'direction' => ['sometimes', 'string', 'in:asc,desc'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $cacheKey = 'tracks:' . md5($request->fullUrl());

        $data = TrackListingCache::remember($cacheKey, function () use ($validated, $trackQuery) {
            $tracks = $trackQuery->paginate($validated);

            return TrackResource::collection($tracks)->response()->getData(true);
        });

        return response()->json($data);
    }
}
