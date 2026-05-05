<?php

namespace Music\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TrackArtist extends Pivot
{
    protected $table = 'track_artists';

    public $incrementing = false;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }
}
