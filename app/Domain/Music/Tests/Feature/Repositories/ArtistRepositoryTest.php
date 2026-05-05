<?php

namespace Music\Tests\Feature\Repositories;

use Integration\Models\ArtistExternalId;
use Music\Models\Artist;
use Music\Repositories\ArtistRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ArtistRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ArtistRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ArtistRepository();
        $this->seed(\Database\Seeders\ProviderSeeder::class);
    }

    #[Test]
    public function it_creates_artist_with_external_id(): void
    {
        $artist = $this->repository->upsertWithExternalId('Carefree', 'spotify', 'ext123', 'http://example.com');
        $this->assertDatabaseHas('artists', ['name' => 'Carefree']);
        $this->assertDatabaseHas('artist_external_ids', ['artist_id' => $artist->id, 'provider_code' => 'spotify', 'external_id' => 'ext123']);
    }

    #[Test]
    public function it_updates_existing_artist(): void
    {
        $artist = $this->repository->upsertWithExternalId('Carefree', 'spotify', 'ext123');
        $updated = $this->repository->upsertWithExternalId('Carefree Updated', 'spotify', 'ext123');
        $this->assertSame($artist->id, $updated->id);
        $this->assertSame('Carefree Updated', $updated->fresh()->name);
    }

    #[Test]
    public function it_returns_existing_artist_by_external_id(): void
    {
        $first = $this->repository->upsertWithExternalId('Carefree', 'spotify', 'ext123');
        $second = $this->repository->upsertWithExternalId('Carefree', 'spotify', 'ext123');
        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Artist::count());
    }

    #[Test]
    public function it_creates_different_artists_for_different_external_ids(): void
    {
        $this->repository->upsertWithExternalId('Artist A', 'spotify', 'ext1');
        $this->repository->upsertWithExternalId('Artist B', 'spotify', 'ext2');
        $this->assertSame(2, Artist::count());
        $this->assertSame(2, ArtistExternalId::count());
    }
}
