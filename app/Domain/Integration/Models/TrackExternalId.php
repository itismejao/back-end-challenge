<?php

namespace App\Domain\Integration\Models;

use App\Domain\Music\Models\Track;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'track_id',
    'provider_code',
    'external_id',
    'external_url',
    'provider_metadata',
    'synced_at',
])]
class TrackExternalId extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'provider_metadata' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_code');
    }
}
