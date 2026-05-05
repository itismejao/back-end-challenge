<?php

namespace Integration\Jobs;

use Illuminate\Http\Client\RequestException;
use Integration\Contracts\MusicProviderFactoryInterface;
use Integration\Models\IntegrationLog;
use Integration\Services\TrackIngestionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchTrackByIsrcJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public array $backoff = [5, 15, 30, 60];

    /**
     * @param list<string> $markets
     */
    public function __construct(
        public readonly string $isrc,
        public readonly string $providerCode = 'spotify',
        public readonly array $markets = [],
    ) {
        $this->onQueue('integration');
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->providerCode))->releaseAfter(5),
        ];
    }

    public function handle(
        TrackIngestionService $ingestionService,
        MusicProviderFactoryInterface $providerFactory,
    ): void {
        $provider = $providerFactory->make($this->providerCode);
        $startedAt = now();

        $log = IntegrationLog::create([
            'provider_code' => $this->providerCode,
            'isrc' => $this->isrc,
            'status' => 'pending',
            'attempt' => $this->attempts(),
            'markets' => $this->markets,
            'started_at' => $startedAt,
        ]);

        try {
            Log::info("Fetching track for ISRC [{$this->isrc}] from [{$this->providerCode}]");

            $track = $ingestionService->ingest($this->isrc, $provider, $this->markets);

            $finishedAt = now();

            $log->update([
                'status' => $track ? 'success' : 'not_found',
                'track_id' => $track?->id,
                'duration_ms' => (int) $startedAt->diffInMilliseconds($finishedAt),
                'finished_at' => $finishedAt,
            ]);

            if ($track) {
                Log::info("Track [{$track->name}] ingested successfully for ISRC [{$this->isrc}]");
            }
        } catch (\Throwable $e) {
            $finishedAt = now();

            $log->update([
                'status' => 'failed',
                'duration_ms' => (int) $startedAt->diffInMilliseconds($finishedAt),
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
                'finished_at' => $finishedAt,
            ]);

            throw $e;
        }
    }

    public function retryAfter(RequestException $exception): ?int
    {
        return (int) $exception->response->header('Retry-After') ?: null;
    }
}
