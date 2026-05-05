<?php

namespace Music\Repositories;

use Music\Contracts\TrackQueryInterface;
use Music\Models\Track;
use Illuminate\Contracts\Pagination\CursorPaginator;

class TrackQueryRepository implements TrackQueryInterface
{
    private const ALLOWED_ORDER_BY = [
        'title' => 'tracks.name',
        'duration' => 'tracks.duration_ms',
        'release_date' => 'albums.release_date',
        'artist' => 'artist_name',
        'track_number' => 'tracks.track_number',
        'created_at' => 'tracks.created_at',
    ];

    private const DEFAULT_ORDER_BY = 'title';
    private const DEFAULT_DIRECTION = 'asc';
    private const DEFAULT_PER_PAGE = 15;

    public function paginate(array $filters = []): CursorPaginator
    {
        $orderBy = $filters['order_by'] ?? self::DEFAULT_ORDER_BY;
        $direction = $filters['direction'] ?? self::DEFAULT_DIRECTION;
        $perPage = $filters['per_page'] ?? self::DEFAULT_PER_PAGE;

        $column = self::ALLOWED_ORDER_BY[$orderBy] ?? self::ALLOWED_ORDER_BY[self::DEFAULT_ORDER_BY];

        $query = Track::query()
            ->with(['album', 'artists', 'availableMarkets', 'externalIds'])
            ->join('albums', 'tracks.album_id', '=', 'albums.id');

        if ($orderBy === 'artist') {
            $query->leftJoin('track_artists', function ($join) {
                $join->on('tracks.id', '=', 'track_artists.track_id')
                    ->where('track_artists.position', '=', 0);
            })
            ->leftJoin('artists', 'track_artists.artist_id', '=', 'artists.id')
            ->selectRaw('tracks.*, artists.name as artist_name');
        } else {
            $query->select('tracks.*');
        }

        return $query
            ->orderBy($column, $direction)
            ->orderBy('tracks.id')
            ->cursorPaginate($perPage);
    }
}
