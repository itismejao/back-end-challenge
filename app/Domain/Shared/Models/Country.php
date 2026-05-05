<?php

namespace App\Domain\Shared\Models;

use App\Domain\Music\Models\Track;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['code', 'name'])]
class Country extends Model
{
    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'track_available_markets', 'country_code', 'track_id');
    }
}
