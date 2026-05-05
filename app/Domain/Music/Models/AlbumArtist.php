<?php

namespace Music\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AlbumArtist extends Pivot
{
    protected $table = 'album_artists';

    public $incrementing = false;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }
}
