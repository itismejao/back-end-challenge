<?php

namespace Database\Seeders;

use App\Domain\Integration\Models\Provider;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        Provider::upsert($this->providers(), 'code', ['name', 'is_active']);
    }

    /** @return list<array{code: string, name: string, is_active: bool}> */
    private function providers(): array
    {
        return [
            ['code' => 'spotify', 'name' => 'Spotify', 'is_active' => true],
            ['code' => 'apple_music', 'name' => 'Apple Music', 'is_active' => false],
            ['code' => 'deezer', 'name' => 'Deezer', 'is_active' => false],
            ['code' => 'tidal', 'name' => 'Tidal', 'is_active' => false],
        ];
    }
}
