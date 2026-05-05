<?php

namespace Music\Enums;

enum TrackOrderBy: string
{
    case Title = 'title';
    case Duration = 'duration';
    case ReleaseDate = 'release_date';
    case Artist = 'artist';
    case TrackNumber = 'track_number';
    case CreatedAt = 'created_at';

    public function column(): string
    {
        return match ($this) {
            self::Title => 'tracks.name',
            self::Duration => 'tracks.duration_ms',
            self::ReleaseDate => 'albums.release_date',
            self::Artist => 'artist_name',
            self::TrackNumber => 'tracks.track_number',
            self::CreatedAt => 'tracks.created_at',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
