<?php

namespace Integration\Jobs;

use Illuminate\Http\Client\RequestException;
use Integration\Contracts\MusicProviderFactoryInterface;
use Integration\Enums\IntegrationStatus;
use Integration\Models\IntegrationLog;
use Integration\Services\TrackIngestionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class FetchTrackByIsrcJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const DEFAULT_RETRY_AFTER_SECONDS = 60;
    private const RETRY_WINDOW_MINUTES = 60;

    public int $maxExceptions = 3;

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

    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(self::RETRY_WINDOW_MINUTES);
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
            'status' => IntegrationStatus::Pending,
            'attempt' => $this->attempts(),
            'markets' => $this->markets,
            'started_at' => $startedAt,
        ]);

        try {
            Log::info("Fetching track for ISRC [{$this->isrc}] from [{$this->providerCode}]");

            $track = $ingestionService->ingest($this->isrc, $provider, $this->markets);

            $log->markFinished(
                status: $track ? IntegrationStatus::Success : IntegrationStatus::NotFound,
                startedAt: $startedAt,
                trackId: $track?->id,
            );

            if ($track) {
                Log::info("Track [{$track->name}] ingested successfully for ISRC [{$this->isrc}]");
            }
        } catch (RequestException $e) {
            $log->markFailed($startedAt, $e);

            if ($this->isRateLimited($e)) {
                $this->handleRateLimit($e);

                return;
            }

            throw $e;
        } catch (\Throwable $e) {
            $log->markFailed($startedAt, $e);

            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        IntegrationLog::create([
            'provider_code' => $this->providerCode,
            'isrc' => $this->isrc,
            'status' => IntegrationStatus::Failed,
            'attempt' => $this->attempts(),
            'markets' => $this->markets,
            'error_message' => $e->getMessage(),
            'error_class' => get_class($e),
            'started_at' => now(),
            'finished_at' => now(),
        ]);
    }

    private function isRateLimited(RequestException $e): bool
    {
        return $e->response->status() === Response::HTTP_TOO_MANY_REQUESTS;
    }

    private function handleRateLimit(RequestException $e): void
    {
        $retryAfter = (int) ($e->response->header('Retry-After') ?: self::DEFAULT_RETRY_AFTER_SECONDS);

        Log::warning("Rate limited for ISRC [{$this->isrc}], retrying in {$retryAfter}s");

        $this->release($retryAfter);
    }
}
