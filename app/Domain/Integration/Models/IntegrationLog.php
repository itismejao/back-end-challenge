<?php

namespace Integration\Models;

use Music\Models\Track;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'provider_code',
    'isrc',
    'status',
    'duration_ms',
    'track_id',
    'error_message',
    'error_class',
    'attempt',
    'markets',
    'started_at',
    'finished_at',
])]
class IntegrationLog extends Model
{
    protected function casts(): array
    {
        return [
            'duration_ms' => 'integer',
            'attempt' => 'integer',
            'markets' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }
}
