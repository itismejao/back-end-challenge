<?php

namespace App\Domain\Music\Models;

use App\Domain\Integration\Models\ArtistExternalId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class Artist extends Model
{
    public function albums(): BelongsToMany
    {
        return $this->belongsToMany(Album::class, 'album_artists')
            ->using(AlbumArtist::class)
            ->withPivot('position')
            ->orderByPivot('position');
    }

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'track_artists')
            ->using(TrackArtist::class)
            ->withPivot('position')
            ->orderByPivot('position');
    }

    public function externalIds(): HasMany
    {
        return $this->hasMany(ArtistExternalId::class);
    }
}
