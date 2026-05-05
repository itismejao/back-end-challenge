<?php

namespace App\Domain\Integration\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'is_active'])]
class Provider extends Model
{
    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function artistExternalIds(): HasMany
    {
        return $this->hasMany(ArtistExternalId::class, 'provider_code');
    }

    public function albumExternalIds(): HasMany
    {
        return $this->hasMany(AlbumExternalId::class, 'provider_code');
    }

    public function trackExternalIds(): HasMany
    {
        return $this->hasMany(TrackExternalId::class, 'provider_code');
    }
}
