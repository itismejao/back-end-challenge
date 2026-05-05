<?php

namespace App\Domain\Music\Models;

use App\Domain\Shared\Models\Country;
use App\Domain\Integration\Models\TrackExternalId;
use App\Domain\Music\Enums\AvailabilityMode;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'album_id',
    'isrc',
    'name',
    'duration_ms',
    'explicit',
    'disc_number',
    'track_number',
    'availability_mode',
])]
class Track extends Model
{
    protected function casts(): array
    {
        return [
            'duration_ms' => 'integer',
            'explicit' => 'boolean',
            'disc_number' => 'integer',
            'track_number' => 'integer',
            'availability_mode' => AvailabilityMode::class,
        ];
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'track_artists')
            ->using(TrackArtist::class)
            ->withPivot('position')
            ->orderByPivot('position');
    }

    public function availableMarkets(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'track_available_markets', 'track_id', 'country_code');
    }

    public function externalIds(): HasMany
    {
        return $this->hasMany(TrackExternalId::class);
    }
}
