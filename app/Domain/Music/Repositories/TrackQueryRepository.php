<?php

namespace Music\Repositories;

use Music\Contracts\TrackQueryInterface;
use Music\Enums\TrackOrderBy;
use Music\Models\Track;
use Illuminate\Contracts\Pagination\CursorPaginator;

class TrackQueryRepository implements TrackQueryInterface
{
    private const DEFAULT_PER_PAGE = 15;

    public function paginate(array $filters = []): CursorPaginator
    {
        $orderBy = TrackOrderBy::tryFrom($filters['order_by'] ?? '') ?? TrackOrderBy::Title;
        $direction = $filters['direction'] ?? 'asc';
        $perPage = $filters['per_page'] ?? self::DEFAULT_PER_PAGE;

        $query = Track::query()
            ->with(['album', 'artists', 'availableMarkets', 'externalIds'])
            ->join('albums', 'tracks.album_id', '=', 'albums.id');

        if ($orderBy === TrackOrderBy::Artist) {
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
            ->orderBy($orderBy->column(), $direction)
            ->orderBy('tracks.id')
            ->cursorPaginate($perPage);
    }
}
