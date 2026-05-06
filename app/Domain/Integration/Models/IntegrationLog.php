<?php

namespace Integration\Models;

use Integration\Enums\IntegrationStatus;
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
            'status' => IntegrationStatus::class,
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

    public function markFinished(IntegrationStatus $status, \DateTimeInterface $startedAt, ?int $trackId = null): void
    {
        $finishedAt = now();

        $this->update([
            'status' => $status,
            'track_id' => $trackId,
            'duration_ms' => (int) $startedAt->diffInMilliseconds($finishedAt),
            'finished_at' => $finishedAt,
        ]);
    }

    public function markFailed(\DateTimeInterface $startedAt, \Throwable $e): void
    {
        $finishedAt = now();

        $this->update([
            'status' => IntegrationStatus::Failed,
            'duration_ms' => (int) $startedAt->diffInMilliseconds($finishedAt),
            'error_message' => $e->getMessage(),
            'error_class' => get_class($e),
            'finished_at' => $finishedAt,
        ]);
    }
}
