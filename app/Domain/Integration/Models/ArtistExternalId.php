<?php

namespace Integration\Models;

use Music\Models\Artist;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'artist_id',
    'provider_code',
    'external_id',
    'external_url',
    'provider_metadata',
    'synced_at',
])]
class ArtistExternalId extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'provider_metadata' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_code');
    }
}
