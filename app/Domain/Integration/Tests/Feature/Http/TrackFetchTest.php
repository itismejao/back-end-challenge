<?php

namespace Integration\Tests\Feature\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrackFetchTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_dispatches_jobs(): void
    {
        Queue::fake();

        $this->postJson('/api/tracks/fetch', [
            'isrcs' => ['NO1R42509310', 'USRC17607839'],
            'markets' => ['BR'],
        ])
            ->assertStatus(202)
            ->assertJson(['message' => '2 job(s) dispatched.']);

        Queue::assertCount(2);
    }

    #[Test]
    public function it_validates_isrcs_required(): void
    {
        $this->postJson('/api/tracks/fetch', ['isrcs' => []])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['isrcs']);
    }

    #[Test]
    public function it_validates_isrc_length(): void
    {
        $this->postJson('/api/tracks/fetch', ['isrcs' => ['SHORT']])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['isrcs.0']);
    }
}
