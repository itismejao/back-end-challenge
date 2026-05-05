<?php

namespace Music\Http\Controllers;

use Music\Cache\TrackListingCache;
use Music\Contracts\TrackQueryInterface;
use Music\Http\Requests\TrackIndexRequest;
use Music\Http\Resources\TrackResource;
use Illuminate\Http\JsonResponse;

class TrackController
{
    public function index(TrackIndexRequest $request, TrackQueryInterface $trackQuery): JsonResponse
    {
        $cacheKey = 'tracks:' . md5($request->fullUrl());

        $data = TrackListingCache::remember($cacheKey, function () use ($request, $trackQuery) {
            $tracks = $trackQuery->paginate($request->validated());

            return TrackResource::collection($tracks)->response()->getData(true);
        });

        return response()->json($data);
    }
}
