<?php

namespace Music\Models;

use Integration\Models\AlbumExternalId;
use Music\Enums\AlbumType;
use Music\Enums\ReleaseDatePrecision;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'album_type',
    'release_date',
    'release_date_precision',
    'total_tracks',
    'images',
    'upc',
])]
class Album extends Model
{
    protected function casts(): array
    {
        return [
            'album_type' => AlbumType::class,
            'release_date' => 'date',
            'release_date_precision' => ReleaseDatePrecision::class,
            'total_tracks' => 'integer',
            'images' => 'array',
        ];
    }

    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'album_artists')
            ->using(AlbumArtist::class)
            ->withPivot('position')
            ->orderByPivot('position');
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class);
    }

    public function externalIds(): HasMany
    {
        return $this->hasMany(AlbumExternalId::class);
    }
}
