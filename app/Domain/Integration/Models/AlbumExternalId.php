<?php

namespace App\Domain\Integration\Models;

use App\Domain\Music\Models\Album;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'album_id',
    'provider_code',
    'external_id',
    'external_url',
    'provider_metadata',
    'synced_at',
])]
class AlbumExternalId extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'provider_metadata' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_code');
    }
}
